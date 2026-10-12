<?php
declare(strict_types=1);

/**
 * Oplysningerne til webserveren: host, port, bruger, kodeord, mappe og
 * om forbindelsen skal være krypteret (FTPS).
 *
 * De skrives under "Server (FTP)" i admin og gemmes i site_settings under
 * nøglerne ftp_host, ftp_port, ftp_user, ftp_password, ftp_dir og
 * ftp_secure — samme tabel som klubbens oplysninger, så der ikke skal
 * ændres i databasen for at få dem med.
 *
 * KODEORDET
 * Det gemmes i klartekst i databasen og aldrig i git (databasen er ikke i
 * git, og include/database.php er i .gitignore). Det er i orden, fordi
 * CMS'et kun kører lokalt. Skal admin nogensinde ligge på en rigtig
 * server, skal der først være login — se kommentaren i store-page.php.
 * Kodeordet sendes aldrig tilbage til formularen: et tomt felt betyder
 * "behold det, der er gemt".
 *
 * ALT VALIDERES HER
 * FTP er en tekstprotokol, hvor hver kommando er én linje. Et linjeskift
 * i et brugernavn, kodeord eller en mappe kunne derfor snige en ekstra
 * kommando ind. Linjeskift og andre kontroltegn afvises i alle felter.
 */
final class PublishSettings
{
    /** @var array<string, string> felt => nøgle i site_settings */
    private const KEYS = [
        'host'     => 'ftp_host',
        'port'     => 'ftp_port',
        'user'     => 'ftp_user',
        'password' => 'ftp_password',
        'dir'      => 'ftp_dir',
        'secure'   => 'ftp_secure',
    ];

    private function __construct()
    {
    }

    /**
     * @return array{host: string, port: int, user: string, password: string, dir: string, secure: bool}
     */
    public static function load(PDO $pdo): array
    {
        $repository = new SiteSettingsRepository($pdo);

        $port = (int) $repository->get(self::KEYS['port'], '21');

        return [
            'host'     => $repository->get(self::KEYS['host']),
            'port'     => ($port >= 1 && $port <= 65535) ? $port : 21,
            'user'     => $repository->get(self::KEYS['user']),
            'password' => $repository->get(self::KEYS['password']),
            'dir'      => $repository->get(self::KEYS['dir'], '/') ?: '/',
            // Krypteret er standard, også før noget er gemt.
            'secure'   => $repository->get(self::KEYS['secure'], '1') === '1',
        ];
    }

    /**
     * Er nok udfyldt til, at en upload kan prøves?
     *
     * @param array{host: string, user: string, password: string} $settings
     */
    public static function isConfigured(array $settings): bool
    {
        return $settings['host'] !== ''
            && $settings['user'] !== ''
            && $settings['password'] !== '';
    }

    /**
     * Renser og validerer det, formularen sendte.
     *
     * @param array<string, mixed> $input
     * @param string               $currentPassword det gemte kodeord, bruges hvis feltet er tomt
     * @return array{host: string, port: int, user: string, password: string, dir: string, secure: bool}
     *
     * @throws InvalidArgumentException med en besked, der er skrevet til brugeren
     */
    public static function clean(array $input, string $currentPassword = ''): array
    {
        // --- Host --------------------------------------------------
        $host = self::line($input['host'] ?? '', 'Serveradressen', 253);

        if (preg_match('~^sftp://~i', $host) === 1) {
            throw new InvalidArgumentException(
                'SFTP understøttes ikke endnu. Brug almindelig FTP med kryptering (FTPS).'
            );
        }

        // Folk kopierer ofte "ftp://ftp.minklub.dk/" fra en vejledning.
        $host = preg_replace('~^ftps?://~i', '', $host) ?? $host;
        $host = explode('/', $host)[0];

        if ($host === '') {
            throw new InvalidArgumentException('Skriv serverens adresse, fx ftp.dinklub.dk.');
        }

        if (preg_match('~^[A-Za-z0-9]([A-Za-z0-9.-]*[A-Za-z0-9])?$~', $host) !== 1) {
            throw new InvalidArgumentException(
                'Serveradressen ser ikke rigtig ud. Skriv kun navnet, fx ftp.dinklub.dk '
                . '— portnummeret skrives i feltet "Port".'
            );
        }

        // --- Port --------------------------------------------------
        $portText = self::line($input['port'] ?? '', 'Porten', 5);

        if ($portText === '') {
            $port = 21;
        } elseif (ctype_digit($portText) && (int) $portText >= 1 && (int) $portText <= 65535) {
            $port = (int) $portText;
        } else {
            throw new InvalidArgumentException('Porten skal være et tal mellem 1 og 65535. Normalt er den 21.');
        }

        // --- Bruger ------------------------------------------------
        $user = self::line($input['user'] ?? '', 'Brugernavnet', 100);

        if ($user === '') {
            throw new InvalidArgumentException('Skriv brugernavnet til FTP-adgangen.');
        }

        // --- Kodeord -----------------------------------------------
        // Må ikke trimmes: mellemrum kan være en del af et kodeord.
        $password = is_string($input['password'] ?? null) ? $input['password'] : '';

        if ($password === '') {
            $password = $currentPassword;
        }

        if (preg_match('/[\x00-\x1F\x7F]/', $password) === 1) {
            throw new InvalidArgumentException('Kodeordet indeholder ugyldige tegn (fx et linjeskift).');
        }

        if (strlen($password) > 200) {
            throw new InvalidArgumentException('Kodeordet er for langt.');
        }

        // --- Mappe -------------------------------------------------
        $dir = str_replace('\\', '/', self::line($input['dir'] ?? '', 'Mappen', 300));
        $dir = preg_replace('~/{2,}~', '/', $dir) ?? $dir;

        if (in_array('..', explode('/', $dir), true)) {
            throw new InvalidArgumentException('Mappen må ikke indeholde "..".');
        }

        $dir = $dir === '' ? '/' : $dir;
        $dir = $dir === '/' ? '/' : (rtrim($dir, '/') ?: '/');

        return [
            'host'     => $host,
            'port'     => $port,
            'user'     => $user,
            'password' => $password,
            'dir'      => $dir,
            'secure'   => !empty($input['secure']),
        ];
    }

    /**
     * @param array{host: string, port: int, user: string, password: string, dir: string, secure: bool} $clean
     */
    public static function save(PDO $pdo, array $clean): void
    {
        $repository = new SiteSettingsRepository($pdo);

        foreach (self::KEYS as $field => $key) {
            $value = $clean[$field];

            $repository->set($key, is_bool($value) ? ($value ? '1' : '0') : (string) $value);
        }
    }

    /**
     * Et fingeraftryk af, HVOR der udgives hen. Skifter host, port, bruger
     * eller mappe, gælder huskelisten ikke længere (filerne ligger et andet
     * sted), og FtpPublisher glemmer den.
     *
     * @param array{host: string, port: int, user: string, dir: string} $settings
     */
    public static function targetFingerprint(array $settings): string
    {
        return sha1(implode("\n", [
            strtolower($settings['host']),
            (string) $settings['port'],
            $settings['user'],
            $settings['dir'],
        ]));
    }

    /** Trimmer og afviser kontroltegn. Returnerer den rensede tekst. */
    private static function line(mixed $value, string $label, int $max): string
    {
        $value = trim(is_string($value) ? $value : '');

        if (preg_match('/[\x00-\x1F\x7F]/', $value) === 1) {
            throw new InvalidArgumentException("{$label} indeholder ugyldige tegn.");
        }

        if (mb_strlen($value, 'UTF-8') > $max) {
            throw new InvalidArgumentException("{$label} er for lang.");
        }

        return $value;
    }
}
