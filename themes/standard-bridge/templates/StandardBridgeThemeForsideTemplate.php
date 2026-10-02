<?php
declare(strict_types=1);

final class StandardBridgeForsideTemplate extends AbstractTemplate
{
    public static function slug(): string
    {
        return 'standardbridge-forside';
    }

    public static function name(): string
    {
        return 'Forside';
    }

    // Blokkene på siden. Navbaren kommer automatisk med, fordi den står i
    // temaets globals() — den skal derfor IKKE stå her.
    public static function blocks(): array
    {
        return [
            static::block('textarea', [
                'title' => 'Velkommen',
                'body'  => 'Her kommer sidens indhold.',
            ]),
        ];
    }
}