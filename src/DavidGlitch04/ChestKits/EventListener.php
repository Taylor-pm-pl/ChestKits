<?php

namespace DavidGlitch04\ChestKits;

use pocketmine\event\{
    Listener,
    block\BlockPlaceEvent
};
use pocketmine\item\{Item,
    enchantment\EnchantmentInstance,
    enchantment\StringToEnchantmentParser,
    StringToItemParser,
    VanillaItems};
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchantManager;

/**
 * Class EventListener
 * @package DavidGlitch04\ChestKits
 */
class EventListener implements Listener{
    /** @var ChestKits $chestkits */
    private ChestKits $chestkits;

    /**
     * EventListener constructor.
     * @param ChestKits $chestkits
     */
    public function __construct(ChestKits $chestkits)
    {
        $this->chestkits = $chestkits;
    }

    /**
     * @param BlockPlaceEvent $event
     */
    public function onTap(BlockPlaceEvent $event): void{
        $item = $event->getItem();
        $player = $event->getPlayer();
        $world = $player->getWorld();
        $position = $event->getBlockAgainst()->getPosition();
        if($this->chestkits->isChestKit($item)){
            $kitname = $item->getNamedTag()->getString("chestkits");
            foreach ($this->chestkits->kits->getAll() as $kits){
                if($kits["name"] == $kitname){
                    foreach($kits["items"] as $itemString){
                        $world->dropItem($position, $this->loadItem(...explode(":", $itemString)));
                    }
                    isset($kits["helmet"]) and $world->dropItem($position, $this->loadItem(...explode(":", $kits["helmet"])));
                    isset($kits["chestplate"]) and $world->dropItem($position, $this->loadItem(...explode(":", $kits["chestplate"])));
                    isset($kits["leggings"]) and $world->dropItem($position, $this->loadItem(...explode(":", $kits["leggings"])));
                    isset($kits["boots"]) and $world->dropItem($position, $this->loadItem(...explode(":", $kits["boots"])));
                }
            }
            $item->setCount($item->getCount() - 1);
            $player->getInventory()->setItemInHand($item);
            $player->sendMessage($this->chestkits->getMessage("kit.open"));
            $event->cancel();
        }
    }

    /**
     * @param string $itemName
     * @param string $name
     * @param mixed  ...$enchantments
     * @return Item
     */
    public function loadItem(string $itemName, int $amount, string $name = "default", ...$enchantments): Item{
        $item = StringToItemParser::getInstance()->parse($itemName);
        if ($item === null) {
            $item = VanillaItems::AIR();
        }
        $item->setCount($amount);
        if(strtolower($name) !== "default"){
            $item->setCustomName($name);
        }
        $enchantment = null;
        foreach($enchantments as $key => $name_level){
            if($key % 2 === 0){ //Name expected
                $enchantment = StringToEnchantmentParser::getInstance()->parse((string)$name_level);
                if($enchantment === null && class_exists(CustomEnchantManager::class)){
                    $enchantment = CustomEnchantManager::getEnchantmentByName((string)$name_level);
                }
            }elseif($enchantment !== null){
                $item->addEnchantment(new EnchantmentInstance($enchantment, (int)$name_level));
            }
        }

        return $item;
    }
}
