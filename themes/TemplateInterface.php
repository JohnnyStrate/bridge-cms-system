<?php
declare(strict_types=1);

/**
 * Kontrakten, alle skabeloner skal følge.
 *
 * En skabelon er en færdig side: en liste af blokke med det indhold, de
 * skal starte med. Når brugeren vælger den i "Opret side", KOPIERES
 * blokkene ned på siden. Siden gemmer bagefter ingen levende reference
 * til skabelonen, så en ændring i skabelonen rører aldrig sider, der
 * allerede er oprettet.
 *
 * Skabeloner ligger som kode og ikke i databasen. Det betyder, at en ny
 * skabelon følger med et 'git pull' som alt andet kode, og at indholdet
 * kan skrives læseligt med kommentarer i stedet for som JSON i en
 * SQL-streng.
 */
interface TemplateInterface
{
    /**
     * Skabelonens navn i adressen og i databasen, fx 'klubforside'.
     * Kun små bogstaver, tal og bindestreg.
     */
    public static function slug(): string;

    /** Navnet, brugeren ser på kortet i "Opret side". */
    public static function name(): string;

    /** En linje om, hvad skabelonen indeholder. */
    public static function description(): string;

    /**
     * Sti til et miniaturebillede, relativt til projektroden, eller en
     * tom streng. Uden billede viser kortet "Ingen forhåndsvisning" —
     * øje-knappen viser den rigtige skabelon uanset hvad.
     */
    public static function thumbnail(): string;

    /** Lavest først i "Opret side". */
    public static function sortOrder(): int;

    /**
     * Blokkene, siden oprettes med, i den rækkefølge de skal stå.
     *
     * Hver blok er ['type' => 'hero', 'settings' => [...], 'styles' => [...]].
     * Felter, der ikke nævnes, får blokkens egen standardværdi, og
     * ukendte felter kasseres — PageBuilder validerer alt mod blokkens
     * skema, præcis som når en redaktør gemmer.
     *
     * @return array<int, array{type: string, settings?: array<string, mixed>, styles?: array<string, mixed>}>
     */
    public static function blocks(): array;
}
