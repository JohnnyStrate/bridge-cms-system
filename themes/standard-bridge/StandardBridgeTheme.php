<?php
 declare(strict_types=1);
 
final class StandardBridgeTheme extends AbstractTheme {
public static function name(): string {
    return 'Tema';
}
public static function description(): string {
    
    return 'beskrivelse af Khalids grimme description';
};
public static function sortOrder(): int {
return 20;

} ;
public static isReady(): bool {
    return false;
};
public static function block(): array {
    return [
        'standardbridge-navbar' => StandardBridgeNavbarblock::class,
    ];
};
publvi static function globals(): array{
    return [
        'header' => 'standardbridge-navbar',
    ];
};

 };