<?php
declare(strict_types=1);

/**
 * Gemmer ét galleri. JSON ind, JSON ud.
 *
 * Billederne valideres gennem præcis samme skema, som editoren tegnede
 * dem ud fra — en sti, der ikke ligner en filsti, bliver til tom tekst
 * frem for at blive skrevet i databasen.
 */

require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

/** @param array<string, mixed> $data */
function respond(int $status, array $data): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['ok' => false, 'error' => 'Kun POST er tilladt.']);
}

$payload = json_decode(file_get_contents('php://input') ?: '', true);

if (!is_array($payload)) {
    respond(400, ['ok' => false, 'error' => 'Ugyldigt dataformat.']);
}

$repository = new GalleryRepository(Database::getConnection());

$id      = (int) ($payload['id'] ?? 0);
$gallery = $repository->find($id);

if ($gallery === null) {
    respond(404, ['ok' => false, 'error' => 'Galleriet findes ikke.']);
}

$name = trim((string) ($payload['name'] ?? ''));

if ($name === '') {
    respond(422, ['ok' => false, 'error' => 'Galleriet skal have et navn.']);
}

$images = FieldValidator::validateField(
    GalleryRepository::imagesField(),
    is_array($payload['images'] ?? null) ? $payload['images'] : []
);

try {
    $repository->save($id, mb_substr($name, 0, 150), $images);

    respond(200, ['ok' => true, 'name' => $name, 'images' => count($images)]);

} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        respond(422, ['ok' => false, 'error' => 'Der findes allerede et galleri med det navn.']);
    }

    error_log('Gemning af galleri ' . $id . ' fejlede: ' . $e->getMessage());
    respond(500, ['ok' => false, 'error' => 'Der opstod en teknisk fejl.']);
}