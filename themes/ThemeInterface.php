<?php
declare(strict_types=1);

/**
 * Kontrakten, alle temaer skal følge.
 *
 * Et tema er ét samlet design: sine egne blokke, sin egen navbar og footer
 * og sine egne skabeloner. Alt ligger i temaets mappe:
 *
 *     themes/tema1/
 *         Tema1Theme.php        ← denne klasse
 *         blocks/navbar/        ← temaets blokke
 *         blocks/footer/
 *         templates/            ← sider med dummy-indhold
 *
 * Sitet har ÉT aktivt tema ad gangen (site_settings.active_theme). Det
 * bestemmer, hvilken navbar og footer der vises, og hvilke blokke og
 * skabeloner editoren tilbyder.
 *
 * Temaets slug er mappenavnet. Det står ikke i klassen, så de to ikke kan
 * komme til at sige noget forskelligt.
 */
interface ThemeInterface
{
    /** Mappenavnet, fx 'tema1'. Gemmes i databasen. */
    public static function slug(): string;

    /** Navnet, brugeren ser under "Tema". */
    public static function name(): string;

    /** En linje om temaets udtryk. */
    public static function description(): string;

    /** Sti til et miniaturebillede relativt til projektroden, eller ''. */
    public static function thumbnail(): string;

    /** Lavest først. Det første tema er standard på en ny installation. */
    public static function sortOrder(): int;

    /**
     * Er temaet færdigt nok til at kunne vælges?
     *
     * false skjuler det under "Tema", så et halvfærdigt tema ikke ser
     * færdigt ud. Koden bliver liggende og virker stadig — er temaet
     * allerede aktivt, bliver det ved med at være det og vises på listen.
     */
    public static function isReady(): bool;

    /**
     * Temaets blokke: bloktype => klasse.
     *
     * Bloktypen er en databasenøgle (page_blocks.block_type) og må ikke
     * ændres, når der først er gemt sider med den.
     *
     * @return array<string, class-string<BlockInterface>>
     */
    public static function blocks(): array;

    /**
     * Temaets globale blokke: slot => bloktype.
     *
     *     return ['header' => 'tema1-navbar', 'footer' => 'tema1-footer'];
     *
     * Typen skal være en af temaets egne blokke. Første gang temaet vælges,
     * oprettes de med blokkens standardværdier (dummy-indholdet).
     *
     * @return array<string, string>
     */
    public static function globals(): array;
}
