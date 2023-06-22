<?php

namespace DavidGlitch04\ChestKits;

use pocketmine\item\{
    Item
};
use pocketmine\plugin\{
    Plugin,
    PluginBase
};
use DavidGlitch04\ChestKits\{
    Command\CKitsCommand,
    Task\CheckUpdateTask
};
use pocketmine\{
    lang\Language,
    player\Player,
    utils\Config,
    utils\TextFormat
};
use pocketmine\block\VanillaBlocks;
use function strval;
/**
 * Class ChestKits
 * @package DavidGlitch04\ChestKits
 */
class ChestKits extends PluginBase{
    /** @var Config $kits */
    public Config $kits;
    /** @var Plugin|null $piggyEnchants */
    public ?Plugin $piggyEnchants;
    /** @var Language $language */
    public static Language $language;
    /** @var array|string[] $languages */
    private array $languages = [
        "vie",
        "eng"
    ];
    public static function getLanguage(): Language{
        return self::$language;
    }

    /**
     * @return void
     */
    protected function onEnable(): void
    {
        $server = $this->getServer();
        $server->getPluginManager()->registerEvents(new EventListener($this), $this);
        $server->getCommandMap()->register("chestkits", new CKitsCommand($this));
        $this->saveResource("kits.yml");
        $this->kits = new Config($this->getDataFolder()."kits.yml", Config::YAML);
        $this->piggyEnchants = $this->getServer()->getPluginManager()->getPlugin("PiggyCustomEnchants");
        $this->initLanguage(strval($this->getConfig()->get("language", "eng")), $this->languages);
        $this->checkUpdate();
    }

    private function checkUpdate(): void{
        $this->getServer()->getAsyncPool()->submitTask(new CheckUpdateTask($this->getDescription()->getName(), $this->getDescription()->getVersion()));
    }

    /**
     * @param string $lang
     * @param array $languageFiles
     */
    public function initLanguage(string $lang, array $languageFiles): void {
        $path = $this->getDataFolder() . "languages/";
        if (!is_dir($path)) {
            @mkdir($path);
        }
        foreach ($languageFiles as $file) {
            if (!is_file($path . $file . ".ini")) {
                $this->saveResource("languages/" . $file . ".ini");
            }
        }
        self::$language = new Language($lang, $path);
    }

    public function getPrefix(): string{
        return strval($this->getConfig()->get("prefix", "&c[&aChestkits&c] "));
    }

    /**
     * @param Player $player
     * @param string $name
     * @param string $lore
     */
    public function sendKit(Player $player, string $name, string $lore): void{
        $kit = VanillaBlocks::CHEST()->asItem();
        $kit->getNamedTag()
            ->setString("chestkits", $name);
        $kit->setCustomName($name);
        $kit->setLore(array($lore));
        $player->getInventory()->addItem($kit);
    }

    /**
     * @param Item $item
     * @return bool
     */
    public function isChestKit(Item $item): bool{
        return $item->getNamedTag()->getTag("chestkits") !== null;
    }

    public function getMessage(string $msg, array $replace = null): string
    {
        $prefix = $this->getPrefix();
        if($replace == null){
            $msg = ChestKits::getLanguage()->translateString($msg);
        } else{
            $msg = ChestKits::getLanguage()->translateString($msg, $replace);
        }
        return TextFormat::colorize($prefix . $msg);
    }
}
