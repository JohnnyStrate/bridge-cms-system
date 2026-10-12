<?php
declare(strict_types=1);

/**
 * Lægger den færdige eksport (mappen export/) op på webserveren via FTP.
 *
 * Bruger PHP's indbyggede FTP-funktioner, så der ikke skal installeres
 * noget ekstra. Krypteret forbindelse (FTPS) bruger ftp_ssl_connect.
 * SFTP er ikke med: det kræver en udvidelse, der ikke følger med XAMPP.
 *
 * HVAD DER SKER VED EN UDGIVELSE
 *  1. Hver fil i eksporten får et sha1-fingeraftryk.
 *  2. Det sammenlignes med huskelisten (tabellen publish_files), som
 *     rummer det, der blev uploadet sidste gang.
 *  3. Kun nye og ændrede filer uploades. CSS og billeder først, HTML
 *     til sidst — så peger en side aldrig på en fil, der ikke er kommet
 *     over endnu.
 *  4. Først når ALT er uploadet, slettes filer, der ikke findes i
 *     eksporten mere (fx en side, der er sat tilbage til kladde).
 *
 * SIKKERHEDSREGLER
 *  - Der slettes KUN filer, som huskelisten siger, vi selv har uploadet.
 *    Mappen på serveren listes aldrig for at rydde op. Står "mappe på
 *    serveren" forkert, kan det derfor ikke koste andre filer livet.
 *  - Mappen på serveren skal findes i forvejen. Den oprettes ikke, så en
 *    tastefejl ikke ender som en mærkelig ny mappe.
 *  - Huskelisten opdateres fil for fil, EFTER at hver fil er kommet
 *    over. Går forbindelsen ned undervejs, fortsætter næste kørsel dér,
 *    hvor den slap.
 *
 * LIMITATION
 * Huskelisten fortæller, hvad CMS'et sidst uploadede — ikke hvad der
 * ligger på serveren lige nu. Rettes eller slettes filer derude i hånden,
 * ved CMS'et det ikke. Dér hjælper "Upload alt forfra".
 */
final class FtpPublisher
{
    /** Nøglen i site_settings, der husker, hvilken server listen gælder. */
    private const TARGET_KEY = 'ftp_target';

    /** Prøvefil til "Test forbindelse". Slettes igen med det samme. */
    private const TEST_FILE = '.bridge-cms-test.tmp';

    private ?\FTP\Connection $conn = null;

    /** Absolut sti til mappen på serveren (fra ftp_pwd). */
    private string $base = '/';

    /** @var array<string, true> mapper, vi ved findes på serveren */
    private array $knownDirs = [];

    /**
     * @param array{host: string, port: int, user: string, password: string, dir: string, secure: bool} $settings
     */
    public function __construct(
        private readonly array $settings,
        private readonly PublishFilesRepository $files,
        private readonly SiteSettingsRepository $store
    ) {
    }

    /**
     * Mangler PHP det, der skal bruges? Null betyder, at alt er klar.
     * Kan kaldes uden at oprette en FtpPublisher, fx til en advarsel i admin.
     */
    public static function requirementsError(bool $secure = true): ?string
    {
        if (!function_exists('ftp_connect')) {
            return 'PHP\'s FTP-udvidelse er slået fra. Åbn php.ini (i XAMPP: Config-knappen ved Apache '
                . '→ "PHP (php.ini)"), fjern semikolonet foran "extension=ftp", gem filen og genstart Apache.';
        }

        if ($secure && !function_exists('ftp_ssl_connect')) {
            return 'Krypteret FTP (FTPS) kræver OpenSSL i PHP. Fjern semikolonet foran "extension=openssl" '
                . 'i php.ini og genstart Apache — eller fjern fluebenet ved kryptering.';
        }

        return null;
    }

    /**
     * "Test forbindelse": logger ind, åbner mappen og prøver at lægge en
     * lille fil derind og slette den igen. Rører hverken huskelisten eller
     * rigtige filer.
     *
     * @return string en besked til brugeren, hvis alt virker
     * @throws RuntimeException med en besked til brugeren, hvis noget ikke gør
     */
    public function test(): string
    {
        $this->connect();

        try {
            $stream = fopen('php://temp', 'r+');
            fwrite($stream, 'Bridge CMS forbindelsestest');
            rewind($stream);

            $remote = $this->remote(self::TEST_FILE);
            $ok     = @ftp_fput($this->conn, $remote, $stream, FTP_BINARY);
            fclose($stream);

            if (!$ok) {
                throw new RuntimeException(sprintf(
                    'Login og mappe virker, men serveren afviste at oprette en fil i "%s". '
                    . 'Tjek, at brugeren har skriveadgang, og at det er den rigtige mappe.',
                    $this->settings['dir']
                ));
            }

            @ftp_delete($this->conn, $remote);

            return sprintf(
                'Forbindelsen virker. Logget ind som %s (%s), og mappen "%s" kan skrives i.',
                $this->settings['user'],
                $this->settings['secure'] ? 'krypteret med FTPS' : 'UKRYPTERET — kodeordet sendes i klartekst',
                $this->settings['dir']
            );
        } finally {
            $this->close();
        }
    }

    /**
     * Uploader eksporten.
     *
     * @param bool $full true = upload ALLE filer, uanset huskelisten
     *                   ("Upload alt forfra"). Filer, der ikke findes i
     *                   eksporten mere, slettes stadig.
     *
     * @return array{uploaded: int, unchanged: int, deleted: int, warnings: array<int, string>, host: string}
     * @throws RuntimeException med en besked til brugeren
     */
    public function publish(string $exportDir, bool $full = false): array
    {
        // En første upload med mange billeder tager længere end XAMPP's
        // standard på 30 sekunder, og en lukket fane må ikke afbryde den
        // midt i en fil.
        @set_time_limit(0);
        ignore_user_abort(true);

        if (!is_file($exportDir . '/index.html')) {
            throw new RuntimeException('Eksportmappen er tom. Byg websitet først.');
        }

        $local = $this->scan($exportDir);

        // Skifter host, bruger eller mappe, ligger filerne et andet sted
        // end sidst — så gælder huskelisten ikke længere.
        $target = PublishSettings::targetFingerprint($this->settings);

        if ($this->store->get(self::TARGET_KEY) !== $target) {
            $this->files->clear();
            $this->store->set(self::TARGET_KEY, $target);
        }

        $known  = $this->files->all();
        $upload = [];

        foreach ($local as $path => $hash) {
            if ($full || ($known[$path] ?? null) !== $hash) {
                $upload[] = $path;
            }
        }

        $unchanged = count($local) - count($upload);
        $stale     = array_values(array_diff(array_keys($known), array_keys($local)));

        // CSS og billeder før HTML. Er ikke alt kommet over, når noget
        // går galt, står de gamle sider stadig og virker.
        usort($upload, static fn (string $a, string $b): int =>
            [str_ends_with($a, '.html') ? 1 : 0, $a] <=> [str_ends_with($b, '.html') ? 1 : 0, $b]
        );

        // Forbindelsen oprettes altid, også når intet er ændret. Ellers
        // ville forkerte oplysninger se ud som en vellykket udgivelse.
        $this->connect();

        $uploaded = 0;
        $deleted  = 0;
        $warnings = [];

        try {
            foreach ($upload as $path) {
                $remote = $this->remote($path);

                $this->ensureDir(dirname($remote));

                if (!@ftp_put($this->conn, $remote, $exportDir . '/' . $path, FTP_BINARY)) {
                    throw new RuntimeException(sprintf(
                        'Upload stoppede ved "%s" (%d af %d filer var nået over). '
                        . 'Prøv igen — den fortsætter, hvor den slap.',
                        $path,
                        $uploaded,
                        count($upload)
                    ));
                }

                // Noteres med det samme, så en afbrudt kørsel ikke skal
                // starte forfra.
                $this->files->save($path, $local[$path]);
                $uploaded++;
            }

            // Sletning kommer først nu, hvor alt nyt er på plads.
            foreach ($stale as $path) {
                if (!self::isSafePath($path)) {
                    $warnings[] = "Sprang over: ugyldig sti i huskelisten ({$path}).";
                    continue;
                }

                $remote = $this->remote($path);

                // Er filen allerede væk (slettet i hånden), er målet nået.
                if (@ftp_delete($this->conn, $remote) || @ftp_size($this->conn, $remote) === -1) {
                    $this->files->delete($path);
                    $deleted++;
                    $this->pruneEmptyDirs(dirname($path));
                } else {
                    $warnings[] = "Kunne ikke slette {$path} på serveren.";
                }
            }
        } finally {
            $this->close();
        }

        return [
            'uploaded'  => $uploaded,
            'unchanged' => $unchanged,
            'deleted'   => $deleted,
            'warnings'  => $warnings,
            'host'      => $this->settings['host'],
        ];
    }

    // -----------------------------------------------------------------
    //  Forbindelsen
    // -----------------------------------------------------------------

    private function connect(): void
    {
        $secure  = $this->settings['secure'];
        $problem = self::requirementsError($secure);

        if ($problem !== null) {
            throw new RuntimeException($problem);
        }

        $host = $this->settings['host'];
        $port = $this->settings['port'];

        $conn = $secure
            ? @ftp_ssl_connect($host, $port, 15)
            : @ftp_connect($host, $port, 15);

        if ($conn === false) {
            throw new RuntimeException(sprintf(
                'Kunne ikke forbinde til %s på port %d. Tjek adressen og porten.%s',
                $host,
                $port,
                $secure
                    ? ' Understøtter serveren ikke FTPS, kan kryptering slås fra — men så sendes kodeordet i klartekst.'
                    : ''
            ));
        }

        $this->conn      = $conn;
        $this->knownDirs = [];

        if (!@ftp_login($conn, $this->settings['user'], $this->settings['password'])) {
            $this->close();
            throw new RuntimeException('Login mislykkedes. Tjek brugernavn og kodeord.');
        }

        @ftp_set_option($conn, FTP_TIMEOUT_SEC, 60);

        // Mange webhoteller sender en intern adresse tilbage i passiv
        // tilstand. Så bruges i stedet serverens egen adresse.
        @ftp_set_option($conn, FTP_USEPASVADDRESS, false);

        if (!@ftp_pasv($conn, true)) {
            $this->close();
            throw new RuntimeException('Kunne ikke skifte til passiv overførsel. Prøv en anden server eller kontakt udbyderen.');
        }

        if (!@ftp_chdir($conn, $this->settings['dir'])) {
            $this->close();
            throw new RuntimeException(sprintf(
                'Mappen "%s" findes ikke på serveren, eller brugeren må ikke åbne den. '
                . 'Tjek feltet "Mappe på serveren". Ofte hedder den public_html, www eller htdocs.',
                $this->settings['dir']
            ));
        }

        $pwd        = @ftp_pwd($conn);
        $this->base = (is_string($pwd) && $pwd !== '') ? $pwd : '/';
    }

    private function close(): void
    {
        if ($this->conn !== null) {
            @ftp_close($this->conn);
            $this->conn = null;
        }
    }

    // -----------------------------------------------------------------
    //  Stier og mapper
    // -----------------------------------------------------------------

    /** "kontakt/index.html" → "/public_html/kontakt/index.html" */
    private function remote(string $relative): string
    {
        return rtrim($this->base, '/') . '/' . $relative;
    }

    /** Sørger for, at en mappe findes på serveren — også de mapper over den. */
    private function ensureDir(string $remoteDir): void
    {
        if ($remoteDir === $this->base || isset($this->knownDirs[$remoteDir])) {
            return;
        }

        if (@ftp_chdir($this->conn, $remoteDir)) {
            $this->knownDirs[$remoteDir] = true;

            return;
        }

        $parent = dirname($remoteDir);

        if ($parent !== $remoteDir) {
            $this->ensureDir($parent);
        }

        if (@ftp_mkdir($this->conn, $remoteDir) === false && !@ftp_chdir($this->conn, $remoteDir)) {
            throw new RuntimeException("Kunne ikke oprette mappen {$remoteDir} på serveren.");
        }

        $this->knownDirs[$remoteDir] = true;
    }

    /**
     * Fjerner tomme mapper efter en slettet fil, opad til (men aldrig
     * inklusive) mappen på serveren. ftp_rmdir afviser mapper med noget i,
     * så en mappe med andres filer bliver stående.
     */
    private function pruneEmptyDirs(string $relativeDir): void
    {
        while ($relativeDir !== '.' && $relativeDir !== '' && $relativeDir !== '/') {
            $remote = $this->remote($relativeDir);

            if (!@ftp_rmdir($this->conn, $remote)) {
                return;
            }

            unset($this->knownDirs[$remote]);
            $relativeDir = dirname($relativeDir);
        }
    }

    /**
     * Gennemgår eksportmappen og giver hver fil dens fingeraftryk.
     *
     * @return array<string, string> relativ sti => sha1
     */
    private function scan(string $exportDir): array
    {
        $root   = rtrim(str_replace('\\', '/', $exportDir), '/');
        $result = [];

        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($items as $item) {
            /** @var SplFileInfo $item */
            if (!$item->isFile()) {
                continue;
            }

            $relative = substr(str_replace('\\', '/', $item->getPathname()), strlen($root) + 1);

            if (!self::isSafePath($relative)) {
                throw new RuntimeException("Filnavnet har ugyldige tegn og kan ikke uploades: {$relative}");
            }

            $hash = sha1_file($item->getPathname());

            if ($hash === false) {
                throw new RuntimeException("Kunne ikke læse {$relative}.");
            }

            $result[$relative] = $hash;
        }

        ksort($result);

        return $result;
    }

    /**
     * En sti, vi tør sende til serveren: relativ, uden "..", uden
     * kontroltegn. Kontroltegn ville kunne bryde en FTP-kommando i to.
     */
    private static function isSafePath(string $path): bool
    {
        return $path !== ''
            && strlen($path) <= 400
            && $path[0] !== '/'
            && preg_match('/[\x00-\x1F\x7F]/', $path) !== 1
            && !in_array('..', explode('/', $path), true);
    }
}
