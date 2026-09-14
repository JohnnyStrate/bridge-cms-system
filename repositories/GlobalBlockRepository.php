<?php
declare(strict_types=1);

/**
 * Al SQL der rører tabellen `global_blocks`.
 *
 * Tabellen har en unik nøgle på `slot`, så der pr. definition kun kan
 * findes én navbar. Det er databasen, der garanterer det — ikke koden.
 * Derfor er gemning en upsert: vi behøver ikke først slå op, om blokken
 * findes, og to samtidige gemninger kan ikke lave to rækker.
 */
final class GlobalBlockRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, slot, block_type, settings, styles, is_visible
               FROM global_blocks
              ORDER BY slot ASC'
        );

        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    public function find(string $slot): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, slot, block_type, settings, styles, is_visible
               FROM global_blocks
              WHERE slot = :slot'
        );
        $stmt->execute(['slot' => $slot]);

        $row = $stmt->fetch();

        return $row ? $this->hydrate($row) : null;
    }

    /**
     * @param array<string, mixed> $settings
     * @param array<string, mixed> $styles
     */
    public function save(
        string $slot,
        string $blockType,
        array $settings,
        array $styles,
        bool $isVisible = true
    ): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO global_blocks (slot, block_type, settings, styles, is_visible)
                  VALUES (:slot, :block_type, :settings, :styles, :is_visible)
             ON DUPLICATE KEY UPDATE
                  block_type = VALUES(block_type),
                  settings   = VALUES(settings),
                  styles     = VALUES(styles),
                  is_visible = VALUES(is_visible)'
        );

        $stmt->execute([
            'slot'       => $slot,
            'block_type' => $blockType,
            'settings'   => $this->encode($settings),
            'styles'     => $this->encode($styles),
            'is_visible' => $isVisible ? 1 : 0,
        ]);
    }

    public function delete(string $slot): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM global_blocks WHERE slot = :slot');
        $stmt->execute(['slot' => $slot]);
    }

    private function hydrate(array $row): array
    {
        $row['id']         = (int) $row['id'];
        $row['is_visible'] = (bool) $row['is_visible'];
        $row['settings']   = $this->decode((string) ($row['settings'] ?? '{}'));
        $row['styles']     = $this->decode((string) ($row['styles'] ?? '{}'));

        return $row;
    }

    private function decode(string $json): array
    {
        $data = json_decode($json, true);

        return is_array($data) ? $data : [];
    }

    private function encode(array $data): string
    {
        return json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );
    }
}