<?php
declare(strict_types=1);

/**
 * Klubbens faste oplysninger: navn, logo, adresse, telefon og e-mail.
 *
 * De skrives ét sted — "Indstillinger" i admin — og alle temaers navbar og
 * footer henter dem herfra. Skifter man tema, følger de med, så kunden
 * aldrig skal skrive sin adresse ind igen.
 *
 * Værdierne ligger i site_settings under nøglerne i KEYS. Mangler en
 * værdi, bruges pladsholderen i DEFAULTS, så et nyt site aldrig ser tomt
 * ud. Der skal derfor ikke køres nogen migration.
 *
 * Blokkene kalder SiteInfo::get('club_name') osv. Oplysningerne hentes fra
 * databasen første gang og huskes resten af forespørgslen.
 */
final class SiteInfo
{
    /** @var array<string, string> felt => pladsholder */
    public const DEFAULTS = [
        'club_name' => 'Din Bridgeklub',
        'address'   => "Vejnavn 1\n1234 By",
        'phone'     => '+45 00 00 00 00',
        'email'     => 'info@dinklub.dk',
        'logo'      => '',
    ];

    /** @var array<string, string> felt => nøgle i site_settings */
    private const KEYS = [
        'club_name' => 'club_name',
        'address'   => 'club_address',
        'phone'     => 'club_phone',
        'email'     => 'club_email',
        'logo'      => 'club_logo',
    ];

    /** @var array<string, string>|null */
    private static ?array $values = null;

    private function __construct()
    {
    }

    public static function get(string $field): string
    {
        return self::all()[$field] ?? '';
    }

    /** @return array<string, string> */
    public static function all(): array
    {
        if (self::$values !== null) {
            return self::$values;
        }

        $values = self::DEFAULTS;

        try {
            $repository = new SiteSettingsRepository(Database::getConnection());

            foreach (array_keys(self::DEFAULTS) as $field) {
                $values[$field] = $repository->get(self::KEYS[$field], self::DEFAULTS[$field]);
            }
        } catch (Throwable $e) {
            // Uden database (fx en fejlkonfiguration) vises pladsholderne
            // frem for at vælte hele siden.
            error_log('Klubinfo kunne ikke hentes: ' . $e->getMessage());
        }

        return self::$values = $values;
    }

    /**
     * Udfylder {år} og {klub} i en tekst, fx footerens bundlinje:
     * "© {år} {klub}" → "© 2026 Din Bridgeklub".
     */
    public static function expand(string $text): string
    {
        return strtr($text, [
            '{år}'  => date('Y'),
            '{klub}' => self::get('club_name'),
        ]);
    }

    /**
     * Gemmer det, "Indstillinger" sendte. Alt valideres her.
     *
     * @param array<string, mixed> $input
     */
    public static function save(PDO $pdo, array $input): void
    {
        $clean = [
            'club_name' => self::text($input['club_name'] ?? '', 120),
            'address'   => self::text($input['address'] ?? '', 300, true),
            'phone'     => self::text($input['phone'] ?? '', 40),
            'email'     => self::text($input['email'] ?? '', 254),
            'logo'      => (string) ($input['logo'] ?? ''),
        ];

        if ($clean['email'] !== '' && filter_var($clean['email'], FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('E-mailadressen ser ikke rigtig ud.');
        }

        // Logoet er en sti, som vi selv har fået fra ImageUploader, eller
        // den gamle værdi. Samme regler som et billedfelt i en blok.
        $clean['logo'] = FieldValidator::validateAll(
            ['logo' => ['type' => 'image', 'default' => '']],
            ['logo' => $clean['logo']]
        )['logo'];

        $repository = new SiteSettingsRepository($pdo);

        foreach ($clean as $field => $value) {
            $repository->set(self::KEYS[$field], (string) $value);
        }

        self::$values = null;
    }

    /** Fjerner kontroltegn og begrænser længden. */
    private static function text(mixed $value, int $max, bool $multiline = false): string
    {
        $value = trim(is_string($value) ? $value : '');
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        $value = $multiline
            ? (preg_replace('/[^\P{C}\n]/u', '', $value) ?? '')
            : (preg_replace('/\p{C}/u', '', $value) ?? '');

        return mb_substr($value, 0, $max, 'UTF-8');
    }
}
