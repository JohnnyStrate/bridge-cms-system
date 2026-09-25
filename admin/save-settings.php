<?php
declare(strict_types=1);

/**
 * Gemmer "Indstillinger". Kaldes kun fra formularen i settings.php.
 *
 * Et nyt logo lægges i uploads/ af ImageUploader — samme vej som alle
 * andre billeder, så filtype og størrelse tjekkes ét sted. Alt andet
 * valideres i SiteInfo::save().
 */

require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: settings.php');
    exit;
}

$pdo = Database::getConnection();

try {
    // Udgangspunktet er det nuværende logo.
    $logo = SiteInfo::get('logo');

    if (!empty($_POST['remove_logo'])) {
        $logo = '';
    }

    // Et nyt logo vinder over både det gamle og "fjern".
    $upload = $_FILES['logo'] ?? null;

    if (is_array($upload) && ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $logo = (new ImageUploader(APP_ROOT . '/uploads'))->store($upload);
    }

    SiteInfo::save($pdo, [
        'club_name' => $_POST['club_name'] ?? '',
        'address'   => $_POST['address'] ?? '',
        'phone'     => $_POST['phone'] ?? '',
        'email'     => $_POST['email'] ?? '',
        'logo'      => $logo,
    ]);

    header('Location: settings.php?gemt=1');
    exit;

} catch (PDOException $e) {
    // En databasefejl er også en RuntimeException, men dens tekst skal
    // ikke vises til brugeren. Derfor fanges den først.
    error_log('Gemning af indstillinger fejlede: ' . $e->getMessage());
    header('Location: settings.php?fejl=' . urlencode('Indstillingerne kunne ikke gemmes. Prøv igen.'));
    exit;

} catch (InvalidArgumentException | RuntimeException $e) {
    // Beskeder fra SiteInfo og ImageUploader er skrevet til brugeren.
    header('Location: settings.php?fejl=' . urlencode($e->getMessage()));
    exit;

} catch (Throwable $e) {
    error_log('Gemning af indstillinger fejlede: ' . $e->getMessage());
    header('Location: settings.php?fejl=' . urlencode('Indstillingerne kunne ikke gemmes. Prøv igen.'));
    exit;
}
