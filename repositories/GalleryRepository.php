<?php
declare(strict_types=1);

/**
 * Al SQL der rører tabellen `galleries`.
 *
 * Et galleri er en navngiven samling billeder, som flere sider kan pege
 * på. Billederne ligger som JSON i én kolonne, fordi de altid læses
 * samlet og altid i listens rækkefølge — samme mønster som
 * page_blocks.settings.
 */
final class GalleryRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Skemaet for et galleris billeder.
     *
     * Står ét sted, fordi to parter skal være enige om det: editoren, der
     * tegner felterne, og gemningen, der validerer dem. Formatet er et
     * almindeligt repeater-felt, så FieldRenderer og FieldValidator kan
     * bruges uændret.
     *
     * @return array<string, mixed>
     */
    public static function imagesField(): array
    {
        return [
            'type'     => 'repeater',
            'label'    => 'Billeder',
            'max_rows' => 200,
            'fields'   => [
                'src' => [
                    'type'    => 'image',
                    'label'   => 'Billedfil',
                    'default' => '',
                ],
                'alt' => [
                    'type'        => 'text',
                    'label'       => 'Beskrivelse',
                    'placeholder' => 'Hvad viser billedet?',
                    'default'     => '',
                ],
                'caption' => [
                    'type'        => 'text',
                    'label'       => 'Billedtekst',
                    'placeholder' => 'Vises under billedet',
                    'default'     => '',
                ],
            ],
            'default' => [],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, name, images, updated_at FROM galleries ORDER BY name ASC'
        );

        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, images, updated_at FROM galleries WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();

        return $row ? $this->hydrate($row) : null;
    }

    /** @throws PDOException hvis navnet allerede findes (unik nøgle). */
    public function create(string $name): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO galleries (name, images) VALUES (:name, :images)'
        );
        $stmt->execute(['name' => $name, 'images' => '[]']);

        return (int) $this->pdo->lastInsertId();
    }

    /** @param array<int, array<string, string>> $images */
    public function save(int $id, string $name, array $images): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE galleries SET name = :name, images = :images WHERE id = :id'
        );

        $stmt->execute([
            'id'     => $id,
            'name'   => $name,
            'images' => json_encode(
                $images,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
            ),
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM galleries WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /**
     * Hvor mange blokke peger på galleriet?
     *
     * Der er ingen fremmednøgle fra JSON, så sammenhængen tælles her.
     * Tallet vises, før brugeren sletter — ellers ville billeder forsvinde
     * fra sider, hun ikke havde åbnet.
     */
    public function usageCount(int $id): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT
               (SELECT COUNT(*) FROM page_blocks
                 WHERE block_type = 'gallery'
                   AND CAST(JSON_EXTRACT(settings, '$.gallery_id') AS UNSIGNED) = :a)
             + (SELECT COUNT(*) FROM global_blocks
                 WHERE block_type = 'gallery'
                   AND CAST(JSON_EXTRACT(settings, '$.gallery_id') AS UNSIGNED) = :b)
             AS total"
        );

        // To navne til samme værdi: PDO uden emulerede prepares tillader
        // ikke, at den samme parameter bruges to gange.
        $stmt->execute(['a' => $id, 'b' => $id]);

        return (int) $stmt->fetchColumn();
    }

    private function hydrate(array $row): array
    {
        $images = json_decode((string) ($row['images'] ?? '[]'), true);

        $row['id']     = (int) $row['id'];
        $row['images'] = is_array($images) ? $images : [];

        return $row;
    }
}