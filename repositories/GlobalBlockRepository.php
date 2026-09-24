<?php
declare(strict_types=1);

/**
 * Al SQL der rører tabellen `global_blocks`.
 *
 * Tabellen har en unik nøgle på (theme, slot), så hvert tema højst har én
 * navbar og én footer. Det er databasen, der garanterer det — ikke koden.
 * Derfor er gemning en upsert: to samtidige gemninger kan ikke lave to
 * rækker.
 */
final class GlobalBlockRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Et temas gemte globale blokke.
     *
     * @return array<int, array<string, mixed>>
     */
    public function forTheme(string $theme): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, theme, slot, block_type, settings, styles, is_visible
               FROM global_blocks
              WHERE theme = :theme
              ORDER BY slot ASC'
        );
        $stmt->execute(['theme' => $theme]);

        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    /**
     * @param array<string, mixed> $settings
     * @param array<string, mixed> $styles
     */
    public function save(
        string $theme,
        string $slot,
        string $blockType,
        array $settings,
        array $styles,
        bool $isVisible = true
    ): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO global_blocks (theme, slot, block_type, settings, styles, is_visible)
                  VALUES (:theme, :slot, :block_type, :settings, :styles, :is_visible)
             ON DUPLICATE KEY UPDATE
                  block_type = VALUES(block_type),
                  settings   = VALUES(settings),
                  styles     = VALUES(styles),
                  is_visible = VALUES(is_visible)'
        );

        $stmt->execute([
            'theme'      => $theme,
            'slot'       => $slot,
            'block_type' => $blockType,
            'settings'   => $this->encode($settings),
            'styles'     => $this->encode($styles),
            'is_visible' => $isVisible ? 1 : 0,
        ]);
    }

    /**
     * Skjuler en global blok uden at slette dens indhold.
     *
     * Findes rækken ikke, oprettes den som skjult med de værdier, der
     * sendes med — ellers ville temaets dummy-navbar dukke op igen.
     *
     * @param array<string, mixed> $settings
     * @param array<string, mixed> $styles
     */
    public function hide(
        string $theme,
        string $slot,
        string $blockType,
        array $settings,
        array $styles
    ): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO global_blocks (theme, slot, block_type, settings, styles, is_visible)
                  VALUES (:theme, :slot, :block_type, :settings, :styles, 0)
             ON DUPLICATE KEY UPDATE is_visible = 0'
        );

        $stmt->execute([
            'theme'      => $theme,
            'slot'       => $slot,
            'block_type' => $blockType,
            'settings'   => $this->encode($settings),
            'styles'     => $this->encode($styles),
        ]);
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
