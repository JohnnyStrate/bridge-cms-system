<?php
declare(strict_types=1);

/**
 * Fælles grundlag for skabeloner.
 *
 * En skabelon behøver kun at skrive slug(), name() og blocks(). Resten
 * har fornuftige standarder, så en ny skabelon kan være kort.
 */
abstract class AbstractTemplate implements TemplateInterface
{
    public static function description(): string
    {
        return '';
    }

    public static function thumbnail(): string
    {
        return '';
    }

    public static function sortOrder(): int
    {
        return 100;
    }

    /**
     * Hjælper, der gør blocks() læselig:
     *
     *     return [
     *         static::block('hero', ['title' => 'Din klub']),
     *         static::block('welcome'),
     *     ];
     *
     * @param array<string, mixed> $settings
     * @param array<string, mixed> $styles
     * @return array{type: string, settings: array<string, mixed>, styles: array<string, mixed>}
     */
    protected static function block(string $type, array $settings = [], array $styles = []): array
    {
        return [
            'type'     => $type,
            'settings' => $settings,
            'styles'   => $styles,
        ];
    }
}
