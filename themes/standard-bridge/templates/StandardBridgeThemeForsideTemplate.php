<?php
declare(strict_types=1);

final class StandardBridgeThemeForsideTemplate extends AbstractTemplate
{
    public static function slug(): string
    {
        return 'standardbridge-forside';
    }

    public static function name(): string
    {
        return 'Forside';
    }

    // Blokkene på siden. Navbar og footer kommer automatisk med, fordi de
    // står i temaets globals() — de skal derfor IKKE stå her.
    public static function blocks(): array
    {
        return [
            static::block('standardbridge-hero'),
            static::block('textarea', [
                'title' => 'Velkommen',
                'body'  => 'Her kommer sidens indhold.',
            ]),
        ];
    }
}