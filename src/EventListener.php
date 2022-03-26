<?php

namespace DavidGlitch04\ChestKits;

use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use pocketmine\item\ItemFactory;
use pocketmine\item\Item;

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
        $vector3 = $event->getBlock()->getPosition()->asVector3();
        if($this->chestkits->isChestKit($item)){
            $kitname = $item->getNamedTag()->getString("chestkits");
            foreach ($this->chestkits->kits->getAll() as $kits){
                if($kits["name"] == $kitname){
                    $tag = 0;
                    foreach($kits["items"] as $itemString){
                        $tag++;
                        $world->dropItem($vector3, $i = $this->loadItem(...explode(":", $itemString)));
                    }
                    isset($kits["helmet"]) and $world->dropItem($vector3, $this->loadItem(...explode(":", $kits["helmet"])));
                    isset($kits["chestplate"]) and $world->dropItem($vector3, $this->loadItem(...explode(":", $kits["chestplate"])));
                    isset($kits["leggings"]) and $world->dropItem($vector3, $this->loadItem(...explode(":", $kits["leggings"])));
                    isset($kits["boots"]) and $world->dropItem($vector3, $this->loadItem(...explode(":", $kits["boots"])));
                }
            }
            $item->setCount($item->getCount() - 1);
            $player->getInventory()->setItemInHand($item);
            $player->sendMessage($this->chestkits->getMessage("kit.open"));
            $event->cancel();
        }
    }

    /**
     * @param int $id
     * @param int $damage
     * @param int $count
     * @param string $name
     * @param mixed ...$enchantments
     * @return Item
     */
    public function loadItem(int $id = 0, int $damage = 0, int $count = 1, string $name = "default", ...$enchantments): Item{
        $item = ItemFactory::getInstance()->get($id, $damage, $count);
        if(strtolower($name) !== "default"){
            $item->setCustomName($name);
        }
        $enchantment = null;
        foreach($enchantments as $key => $name_level){
            if($key % 2 === 0){ //Name expected
                $enchantment = StringToEnchantmentParser::getInstance()->parse((string)$name_level);
                if($enchantment === null){
                    $enchantment = CustomEnchant::getEnchantmentByName((string)$name_level);
                }
            }elseif($enchantment !== null){
                if($this->chestkits->piggyEnchants !== null && $enchantment instanceof CustomEnchant){
                    $item->addEnchantment($enchantment, (int)$name_level);
                }else{
                    $item->addEnchantment(new EnchantmentInstance($enchantment, (int)$name_level));
                }
            }
        }

        return $item;
    }
}