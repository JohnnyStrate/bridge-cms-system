<?php
declare(strict_types=1);

/**
 * Skifter sitets aktive tema. Kaldes kun fra formularen i themes.php.
 *
 * Der oprettes ingen rækker her. Har det nye tema aldrig været brugt, viser
 * GlobalBlocks dets dummy-navbar og -footer, indtil brugeren gemmer i
 * editoren. Det gamle temas rækker røres ikke, så et skift tilbage giver
 * præcis det, man havde.
 */

require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: themes.php');
    exit;
}

// Værdien slås op blandt de temaer, der findes i /themes/.
$slug = trim((string) ($_POST['theme'] ?? ''));

try {
    $pdo = Database::getConnection();

    // Et skjult (ufærdigt) tema kan ikke vælges ved at sende dets navn.
    if (!array_key_exists($slug, ThemeRegistry::selectable(ThemeRegistry::active($pdo)))) {
        throw new InvalidArgumentException('Temaet kan ikke vælges.');
    }

    ThemeRegistry::setActive($pdo, $slug);

    header('Location: themes.php?skiftet=1');
    exit;

} catch (InvalidArgumentException $e) {
    header('Location: themes.php?fejl=' . urlencode($e->getMessage()));
    exit;

} catch (Throwable $e) {
    error_log('Skift af tema fejlede: ' . $e->getMessage());

    header('Location: themes.php?fejl=' . urlencode('Temaet kunne ikke skiftes. Prøv igen.'));
    exit;
}
