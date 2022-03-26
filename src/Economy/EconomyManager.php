<?php

namespace DavidGlitch04\ChestKits\Economy;

use DavidGlitch04\ChestKits\ChestKits;
use pocketmine\player\Player;

/**
 * Class EconomyManager
 * @package DavidGlitch04\ChestKits\Economy
 */
class EconomyManager{
    /** @var \pocketmine\plugin\Plugin|null $eco */
    private $eco;
    /** @var ChestKits $plugin */
    private ChestKits $plugin;

    /**
     * EconomyManager constructor.
     * @param ChestKits $plugin
     */
    public function __construct(ChestKits $plugin){
        $this->plugin = $plugin;
        $manager = $plugin->getServer()->getPluginManager();
        $this->eco = $manager->getPlugin("EconomyAPI") ?? $manager->getPlugin("BedrockEconomy") ?? null;
        unset($manager);
    }

    /**
     * @param Player $player
     * @return int
     */
    public function getMoney(Player $player): int {
        switch ($this->eco->getName()){
            case "EconomyAPI":
                $balance = $this->eco->myMoney($player);
                break;
            case "BedrockEconomy":
                $balance = $this->eco->getPlayerBalance($player->getName());
                break;
            default:
                $balance = 0;
        }
        return $balance;
    }

    /**
     * @param Player $player
     * @param int $amount
     * @return bool
     */
    public function reduceMoney(Player $player, int $amount){
        if($this->eco == null){
            $this->plugin->getLogger()->warning("You not have Economy plugin");
            return true;
        }
        switch ($this->eco->getName()){
            case "EconomyAPI":
                $this->eco->reduceMoney($player, $amount);
                break;
            case "BedrockEconomy":
                $this->eco->addToPlayerBalance($player->getName(), (int) ceil($amount));
                break;
        }
    }
}