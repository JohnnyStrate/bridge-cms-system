<?= 
final class StandardBridgeHero extends AbstractBlock {
    public static function type():string {
        return 'standardbridge-hero';
    }
    final public static function label():string {
        return 'hero — standardbridge';
    }
    public function getSchema():array {
        return [
            'title' =>m []
        ]
    }
}


?>
