<?php
declare(strict_types=1);

/**
 * Modtager og gemmer uploadede billeder.
 *
 * Klassen er systemets mest sikkerhedsfølsomme kode. Et upload-endpoint,
 * der ikke kontrollerer hvad det modtager, er den klassiske vej til at få
 * kørbar kode ind på en server.
 *
 * Fire regler bærer det:
 *
 * 1. TYPEN AFGØRES AF INDHOLDET, IKKE AF NAVNET.
 *    Filendelsen er brugerinput og siger ingenting. getimagesize() åbner
 *    filen og afgør, hvad den faktisk er. En .jpg der i virkeligheden er
 *    PHP-kode, falder på det trin.
 *
 * 2. FILNAVNET GENERERES.
 *    Det oprindelige navn bruges aldrig. Uploader nogen '../../index.php',
 *    når det navn aldrig filsystemet. Vi laver et nyt ud fra tilfældige
 *    bytes plus den endelse, vi selv har udledt af indholdet.
 *
 * 3. SVG AFVISES.
 *    En SVG er en XML-fil, der kan indeholde JavaScript. Den er reelt et
 *    HTML-dokument forklædt som billede, og den hører ikke hjemme i et
 *    upload-felt, som en redaktør bruger.
 *
 * 4. STØRRELSEN HAR ET LOFT.
 *    Uden det kan et par uploads fylde disken.
 *
 * BILLEDERNE GØRES LETTERE
 * Et foto fra en telefon er ofte 4000–6000 pixel bredt og flere MB. Det
 * er langt mere, end en skærm kan vise, og gør siden langsom. Derfor
 * (se optimise()):
 *   - billedet skaleres ned, så den længste side højst er MAX_SIDE pixel,
 *   - fotos gemmes som JPG — også en PNG uden gennemsigtighed,
 *   - en PNG MED gennemsigtighed (fx et logo) forbliver PNG,
 *   - GIF røres ikke (den kan være animeret),
 *   - billedet drejes rigtigt, hvis telefonen har gemt det "på siden".
 * Mangler serveren GD-udvidelsen, gemmes originalen uændret.
 */
final class ImageUploader
{
    /** 8 MB. Rigeligt til et fotografi, lavt nok til at begrænse skade. */
    private const MAX_BYTES = 8 * 1024 * 1024;

    /** Længste side i pixel efter upload. Rigeligt til en fuld skærm. */
    private const MAX_SIDE = 2000;

    /** JPG-kvalitet (0–100). 82 kan ikke skelnes fra originalen på en skærm. */
    private const JPEG_QUALITY = 82;

    /**
     * Billedtyper vi accepterer, og den endelse hver af dem får.
     *
     * Nøglerne er konstanter fra getimagesize(), altså udledt af filens
     * indhold. SVG optræder bevidst ikke: getimagesize() genkender den
     * ikke som billede, og den skal heller ikke igennem.
     *
     * @var array<int, string>
     */
    private const ALLOWED = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG  => 'png',
        IMAGETYPE_GIF  => 'gif',
        IMAGETYPE_WEBP => 'webp',
    ];

    public function __construct(private readonly string $uploadDir)
    {
    }

    /**
     * Gemmer en uploadet fil og returnerer dens sti relativt til
     * projektroden, fx 'uploads/2026/09/a1b2c3d4e5f6.jpg'.
     *
     * @param array<string, mixed> $file En post fra $_FILES.
     *
     * @throws RuntimeException med en besked, brugeren kan forstå.
     */
    public function store(array $file): string
    {
        $this->assertUploadSucceeded($file);

        $temporaryPath = (string) ($file['tmp_name'] ?? '');

        // Bekræfter at filen rent faktisk kom fra en HTTP-upload og ikke
        // er en sti, nogen har fået serveren til at pege på.
        if (!is_uploaded_file($temporaryPath)) {
            throw new RuntimeException('Ugyldig upload.');
        }

        if ((int) ($file['size'] ?? 0) > self::MAX_BYTES) {
            throw new RuntimeException('Billedet er for stort. Maksimum er 8 MB.');
        }

        $extension = $this->detectExtension($temporaryPath);

        // Månedsmapper holder antallet af filer pr. mappe nede, så
        // filhåndteringen stadig er til at arbejde med efter et par år.
        $subDirectory = date('Y/m');
        $directory    = $this->uploadDir . '/' . $subDirectory;

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Kunne ikke oprette mappen til billeder.');
        }

        // Tilfældigt navn. Ingen del af brugerens filnavn overlever.
        $filename = bin2hex(random_bytes(8)) . '.' . $extension;
        $target   = $directory . '/' . $filename;

        if (!move_uploaded_file($temporaryPath, $target)) {
            throw new RuntimeException('Billedet kunne ikke gemmes.');
        }

        // Gør billedet lettere. Endelsen kan skifte (PNG-foto → JPG).
        $target   = $this->optimise($target, $extension);
        $filename = basename($target);

        // Ikke kørbar. Betyder intet på Windows, men filerne skal kunne
        // flyttes til en Linux-server uden at blive et problem.
        chmod($target, 0644);

        return 'uploads/' . $subDirectory . '/' . $filename;
    }

    /**
     * Skalerer billedet ned og gemmer det i et let format.
     *
     * Returnerer stien til den fil, der skal bruges. Går noget galt
     * undervejs, bruges originalen — et upload må aldrig fejle, fordi
     * optimeringen ikke kunne lade sig gøre.
     */
    private function optimise(string $path, string $extension): string
    {
        if ($extension === 'gif' || !function_exists('imagecreatetruecolor')) {
            return $path;
        }

        $info = @getimagesize($path);

        if ($info === false) {
            return $path;
        }

        [$width, $height] = $info;

        // Et billede fylder bredde × højde × ca. 5 byte i hukommelsen,
        // mens det behandles. Er der ikke plads, bruges originalen.
        if (!$this->hasMemoryFor($width * $height * 5 + 16 * 1024 * 1024)) {
            return $path;
        }

        $source = match ($extension) {
            'jpg'  => @imagecreatefromjpeg($path),
            'png'  => @imagecreatefrompng($path),
            'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };

        if ($source === false) {
            return $path;
        }

        // Telefoner gemmer ofte billedet liggende og skriver blot i
        // filen, at det skal vises drejet. Den oplysning forsvinder, når
        // billedet gemmes igen — så drejningen udføres her.
        $orientation = $extension === 'jpg' ? $this->jpegOrientation($path) : 1;

        if ($orientation !== 1) {
            $source = $this->applyOrientation($source, $orientation);
            $width  = imagesx($source);
            $height = imagesy($source);
        }

        $keepAlpha = $extension !== 'jpg' && $this->hasTransparency($source, $width, $height);
        $scale     = min(1, self::MAX_SIDE / max($width, $height));
        $newWidth  = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));

        $image = imagecreatetruecolor($newWidth, $newHeight);

        if ($keepAlpha) {
            imagealphablending($image, false);
            imagesavealpha($image, true);
            imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
        } else {
            // JPG kan ikke være gennemsigtig — hvid bund i stedet for sort.
            imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));
        }

        imagecopyresampled($image, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($source);

        $newExtension = $keepAlpha ? $extension : 'jpg';
        $target       = preg_replace('/\.[a-z]+$/', '.' . $newExtension, $path) ?? $path;
        $temporary    = $target . '.tmp';

        if ($newExtension === 'jpg') {
            // "Progressiv" JPG: vises groft med det samme og skarpes op.
            imageinterlace($image, true);
        }

        $saved = match ($newExtension) {
            'png'   => imagepng($image, $temporary, 9),
            'webp'  => function_exists('imagewebp') && imagewebp($image, $temporary, self::JPEG_QUALITY),
            default => imagejpeg($image, $temporary, self::JPEG_QUALITY),
        };

        imagedestroy($image);

        // Blev resultatet ikke mindre (og er det ikke skaleret eller
        // drejet), beholdes originalen.
        if (!$saved || !is_file($temporary)
            || ($scale === 1 && $orientation === 1 && $newExtension === $extension
                && filesize($temporary) >= filesize($path))) {
            @unlink($temporary);
            return $path;
        }

        rename($temporary, $target);

        if ($target !== $path) {
            @unlink($path);
        }

        return $target;
    }

    /** Er der mindst én pixel, der ikke er helt dækkende? (stikprøve) */
    private function hasTransparency(GdImage $image, int $width, int $height): bool
    {
        $step = max(1, (int) floor(min($width, $height) / 60));

        for ($y = 0; $y < $height; $y += $step) {
            for ($x = 0; $x < $width; $x += $step) {
                if (((imagecolorat($image, $x, $y) >> 24) & 0x7F) > 0) {
                    return true;
                }
            }
        }

        return false;
    }

    private function hasMemoryFor(int $bytes): bool
    {
        $limit = trim((string) ini_get('memory_limit'));

        if ($limit === '-1') {
            return true;
        }

        $value = (int) $limit;
        $value *= match (strtolower(substr($limit, -1))) {
            'g' => 1024 * 1024 * 1024,
            'm' => 1024 * 1024,
            'k' => 1024,
            default => 1,
        };

        if ($value - memory_get_usage() >= $bytes) {
            return true;
        }

        // Prøv at få lidt mere til netop dette billede.
        return @ini_set('memory_limit', (string) (memory_get_usage() + $bytes + 32 * 1024 * 1024)) !== false;
    }

    /**
     * Læser EXIF-feltet "Orientation" (1–8) direkte fra JPG-filen.
     * Kræver ikke exif-udvidelsen, som ofte er slået fra i XAMPP.
     */
    private function jpegOrientation(string $path): int
    {
        $data = (string) @file_get_contents($path, false, null, 0, 128 * 1024);

        if (!str_starts_with($data, "\xFF\xD8")) {
            return 1;
        }

        $offset = 2;

        while ($offset + 4 <= strlen($data) && $data[$offset] === "\xFF") {
            $marker = ord($data[$offset + 1]);
            $length = unpack('n', substr($data, $offset + 2, 2))[1];

            // APP1 med "Exif\0\0"
            if ($marker === 0xE1 && substr($data, $offset + 4, 6) === "Exif\0\0") {
                $tiff   = $offset + 10;
                $little = substr($data, $tiff, 2) === 'II';
                $u16    = static fn (int $at): int => unpack($little ? 'v' : 'n', substr($data, $at, 2))[1];
                $u32    = static fn (int $at): int => unpack($little ? 'V' : 'N', substr($data, $at, 4))[1];

                $ifd     = $tiff + $u32($tiff + 4);
                $entries = $u16($ifd);

                for ($i = 0; $i < $entries; $i++) {
                    $entry = $ifd + 2 + $i * 12;

                    if ($entry + 12 > strlen($data)) {
                        break;
                    }

                    if ($u16($entry) === 0x0112) {
                        $value = $u16($entry + 8);
                        return $value >= 1 && $value <= 8 ? $value : 1;
                    }
                }

                return 1;
            }

            // Selve billeddata begynder — ingen EXIF fundet.
            if ($marker === 0xDA) {
                break;
            }

            $offset += 2 + $length;
        }

        return 1;
    }

    private function applyOrientation(GdImage $image, int $orientation): GdImage
    {
        $rotated = match ($orientation) {
            3, 4 => imagerotate($image, 180, 0),
            5, 6 => imagerotate($image, -90, 0),
            7, 8 => imagerotate($image, 90, 0),
            default => $image,
        };

        if ($rotated === false) {
            return $image;
        }

        if (in_array($orientation, [2, 4, 5, 7], true)) {
            imageflip($rotated, IMG_FLIP_HORIZONTAL);
        }

        return $rotated;
    }

    /**
     * @param array<string, mixed> $file
     */
    private function assertUploadSucceeded(array $file): void
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($error === UPLOAD_ERR_OK) {
            return;
        }

        // PHP's egne fejlkoder oversættes til noget, en redaktør kan
        // handle på. 'UPLOAD_ERR_INI_SIZE' siger ingen bruger noget.
        throw new RuntimeException(match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE
                => 'Billedet er for stort.',
            UPLOAD_ERR_PARTIAL
                => 'Overførslen blev afbrudt. Prøv igen.',
            UPLOAD_ERR_NO_FILE
                => 'Der blev ikke valgt nogen fil.',
            UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE
                => 'Serveren kunne ikke gemme filen.',
            default
                => 'Upload mislykkedes.',
        });
    }

    /**
     * Afgør filtypen ud fra indholdet og returnerer den endelse, filen
     * skal have.
     */
    private function detectExtension(string $path): string
    {
        // Returnerer false for alt, der ikke er et billede, den kender —
        // herunder PHP-filer, tekstfiler og SVG.
        $info = @getimagesize($path);

        if ($info === false || !isset(self::ALLOWED[$info[2]])) {
            throw new RuntimeException(
                'Filen er ikke et gyldigt billede. Brug JPG, PNG, GIF eller WebP.'
            );
        }

        return self::ALLOWED[$info[2]];
    }
}