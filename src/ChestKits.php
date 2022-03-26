<?php

namespace DavidGlitch04\ChestKits;

use DavidGlitch04\ChestKits\Command\CKitsCommand;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\StringTag;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class ChestKits extends PluginBase{

    public Config $kits;

    public $piggyEnchants;

    protected function onEnable(): void
    {
        $server = $this->getServer();
        $server->getPluginManager()->registerEvents(new EventListener($this), $this);
        $server->getCommandMap()->register("chestkits", new CKitsCommand($this));
        $this->saveResource("kits.yml");
        $this->kits = new Config($this->getDataFolder()."kits.yml", Config::YAML);
        $this->piggyEnchants = $this->getServer()->getPluginManager()->getPlugin("PiggyCustomEnchants");
    }

    public function sendKit(Player $player, string $name, string $lore): void{
        $kit = ItemFactory::getInstance()->get(54, 0, 1);
        $kit->getNamedTag()
            ->setString("chestkits", $name);
        $kit->setCustomName($name);
        $kit->setLore(array($lore));
        $player->getInventory()->addItem($kit);
    }

    public function isChestKit(Item $item): bool{
        if($item->getNamedTag()->getTag("chestkits") !== null){
            return true;
        } else{
            return false;
        }
    }
}