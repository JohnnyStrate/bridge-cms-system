<?php
declare(strict_types=1);

/**
 * Skifter en sides status (kladde/udgivet) direkte fra sidelisten.
 *
 * Formularen sender den status, siden SKAL have — ikke "skift". Så giver
 * et dobbeltklik eller en genindsendt formular samme resultat som ét klik.
 *
 * Editoren har stadig sit eget status-felt. Den gemmer via save-page.php
 * og PageSaver som før; den her fil bruges kun af index.php.
 *
 * Adgangskontrol er bevidst udeladt; se begrundelsen i store-page.php.
 */

require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$pageId = filter_input(INPUT_POST, 'page_id', FILTER_VALIDATE_INT) ?: 0;
$status = (string) ($_POST['status'] ?? '');

try {
    $pages = new PageRepository(Database::getConnection());

    if ($pages->find($pageId) === null) {
        throw new InvalidArgumentException('Siden findes ikke.');
    }

    $pages->setStatus($pageId, $status);

    header('Location: index.php');
    exit;

} catch (InvalidArgumentException $e) {
    header('Location: index.php?fejl=' . urlencode($e->getMessage()));
    exit;

} catch (Throwable $e) {
    error_log('Statusskift for side ' . $pageId . ' fejlede: ' . $e->getMessage());

    header('Location: index.php?fejl=' . urlencode('Status kunne ikke ændres. Prøv igen.'));
    exit;
}
