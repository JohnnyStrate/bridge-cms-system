<?php
declare(strict_types=1);

/**
 * Sletter et galleri.
 *
 * Blokke, der pegede på det, mister deres billeder, men bliver stående og
 * viser deres tomme tilstand. Det er med vilje: at slette brugerens
 * blokke som bivirkning af en helt anden handling ville være værre end et
 * tomt galleri, hun selv kan fylde igen.
 */

require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: galleries.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

try {
    (new GalleryRepository(Database::getConnection()))->delete($id);
    header('Location: galleries.php');

} catch (Throwable $e) {
    error_log('Sletning af galleri ' . $id . ' fejlede: ' . $e->getMessage());
    header('Location: galleries.php?fejl=' . rawurlencode('Galleriet kunne ikke slettes.'));
}

exit;