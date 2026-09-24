<?php
declare(strict_types=1);

/**
 * Al SQL der rører tabellen `site_settings`.
 *
 * Indstillinger for hele sitet som nøgle/værdi. Lige nu kun
 * 'active_theme', men klubnavn, logo og kontaktoplysninger kan komme hertil
 * senere uden en ny tabel.
 */
final class SiteSettingsRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function get(string $key, string $default = ''): string
    {
        $stmt = $this->pdo->prepare(
            'SELECT `value` FROM site_settings WHERE `key` = :key'
        );
        $stmt->execute(['key' => $key]);

        $value = $stmt->fetchColumn();

        return $value === false ? $default : (string) $value;
    }

    public function set(string $key, string $value): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO site_settings (`key`, `value`) VALUES (:key, :value)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)'
        );
        $stmt->execute(['key' => $key, 'value' => $value]);
    }
}
