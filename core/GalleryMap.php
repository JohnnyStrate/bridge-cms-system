<?php
declare(strict_types=1);

/**
 * Slår gallerier op ved id under rendering.
 *
 * Præcis samme rolle som SiteMap har for sider: blokken gemmer et id, og
 * kortet oversætter det til indhold. Uden det ville GalleryBlock skulle
 * hente fra databasen midt i sin render() — og en blok må hverken kende
 * PDO eller SQL.
 */
final class GalleryMap
{
    /** @param array<int, array<string, mixed>> $galleries id => række */
    private function __construct(private readonly array $galleries)
    {
    }

    /** @param array<int, array<string, mixed>> $rows Fra GalleryRepository::all() */
    public static function fromGalleries(array $rows): self
    {
        $indexed = [];

        foreach ($rows as $row) {
            $indexed[(int) $row['id']] = $row;
        }

        return new self($indexed);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function has(int $id): bool
    {
        return isset($this->galleries[$id]);
    }

    public function name(int $id): string
    {
        return (string) ($this->galleries[$id]['name'] ?? '');
    }

    /** @return array<int, array<string, string>> */
    public function images(int $id): array
    {
        $images = $this->galleries[$id]['images'] ?? [];

        return is_array($images) ? $images : [];
    }

    /**
     * Til dropdownen i editoren.
     *
     * @return array<int, string> id => navn
     */
    public function choices(): array
    {
        $choices = [];

        foreach ($this->galleries as $id => $row) {
            $choices[$id] = (string) $row['name'];
        }

        return $choices;
    }
}