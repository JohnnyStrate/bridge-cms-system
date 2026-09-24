<?php
declare(strict_types=1);

/**
 * Fælles opstart for hele applikationen.
 *
 * Enhver indgangsfil starter med præcis én linje:
 *
 *     require_once __DIR__ . '/../bootstrap.php';
 */

define('APP_ROOT', __DIR__);

error_reporting(E_ALL);
ini_set('display_errors', '1');

date_default_timezone_set('Europe/Copenhagen');

// Hjælpefunktioner. Kan ikke autoloades, da autoloading kun gælder klasser.
require_once APP_ROOT . '/core/helpers.php';


/**
 * Autoloader.
 *
 * Klasser indlæses først, når de faktisk bruges.
 */
spl_autoload_register(static function (string $class): void {

    // Klassenavne kommer fra vores egen kode, aldrig fra brugerinput.
    // Guarden er alligevel med, fordi et klassenavn her bliver til en
    // filsti, og den slags skal aldrig kunne pege uden for projektet.
    if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $class) !== 1) {
        return;
    }

    // Mapper der gennemsøges.
    //
    // Hvert tema har sin egen mappe med sine blokke og skabeloner:
    //
    //     themes/tema1/Tema1Theme.php
    //     themes/tema1/blocks/navbar/Tema1Navbar.php
    //     themes/tema1/templates/Tema1ForsideTemplate.php
    //
    // De fælles værktøjsblokke ligger i blocks/faelles/. Et nyt tema er
    // kun en ny mappe — der skal ikke rettes noget her.
    static $directories = null;

    if ($directories === null) {
        $directories = [
            APP_ROOT . '/core/',
            APP_ROOT . '/repositories/',
            APP_ROOT . '/blocks/',
            APP_ROOT . '/themes/',
        ];

        $patterns = [
            '/blocks/*/*',              // blocks/faelles/image/
            '/themes/*',                // themes/tema1/
            '/themes/*/blocks/*',       // themes/tema1/blocks/navbar/
            '/themes/*/templates',      // themes/tema1/templates/
            '/themes/*/templates/*',    // en skabelon med sin egen mappe
        ];

        foreach ($patterns as $pattern) {
            foreach (glob(APP_ROOT . $pattern, GLOB_ONLYDIR) ?: [] as $dir) {
                $directories[] = $dir . '/';
            }
        }
    }

    // Blok-klasserne hedder HeroBlock, mens filen hedder Hero.php.
    // Begge navne prøves, så I kan navngive frit.
    $candidates = [$class];

    if (str_ends_with($class, 'Block') && $class !== 'Block') {
        $candidates[] = substr($class, 0, -5);
    }

    foreach ($directories as $directory) {
        foreach ($candidates as $filename) {
            $path = $directory . $filename . '.php';
            if (is_file($path)) {
                require_once $path;
                return;
            }
        }
    }
});