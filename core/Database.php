<?php
declare(strict_types=1);

/**
 * Databaseforbindelsen, delt af hele projektet.
 *
 * Filen indeholder INGEN adgangsoplysninger og må gerne ligge i git.
 * Oplysningerne læses fra include/database.php, som hver udvikler selv
 * opretter ud fra include/database.example.php, og som er i .gitignore.
 */
final class Database
{
    private static ?PDO $connection = null;

    /**
     * Rene statiske klasser skal ikke kunne instantieres.
     */
    private function __construct()
    {
    }

    public static function getConnection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $configPath = APP_ROOT . '/include/database.php';

        if (!is_file($configPath)) {
            throw new RuntimeException(
                'Konfigurationsfilen include/database.php mangler. '
                . 'Kopiér include/database.example.php og udfyld den.'
            );
        }

        $config = require $configPath;

        foreach (['host', 'database', 'username', 'password'] as $key) {
            if (!is_array($config) || !array_key_exists($key, $config)) {
                throw new RuntimeException(
                    "Nøglen '{$key}' mangler i include/database.php."
                );
            }
        }

        /*
         * Porten er valgfri. Uden den bruger PDO MySQL/MariaDBs
         * standardport, 3306. Den er kun nødvendig, hvis XAMPP er sat til
         * en anden port, fx fordi 3306 var optaget.
         */
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            $config['host'],
            $config['database']
        );

        if (!empty($config['port'])) {
            $dsn .= ';port=' . (int) $config['port'];
        }

        try {
            self::$connection = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                [
                    // Fejl i SQL queries kaster exceptions.
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

                    // Hent database-resultater som associative arrays.
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

                    // Brug ægte prepared statements.
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            // Beskeden fra PDO kan indeholde brugernavn og vært, men
            // aldrig adgangskoden. Admin kører kun lokalt, så den må vises.
            throw new RuntimeException(
                'DB-fejl: ' . $e->getMessage(),
                0,
                $e
            );
        }

        return self::$connection;
    }
}
