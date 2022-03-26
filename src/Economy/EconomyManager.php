<?php

namespace DavidGlitch04\ChestKits\Economy;

use DavidGlitch04\ChestKits\ChestKits;
use pocketmine\player\Player;

class EconomyManager{

    private $eco;

    private ChestKits $plugin;

    public function __construct(ChestKits $plugin){
        $this->plugin = $plugin;
        $manager = $plugin->getServer()->getPluginManager();
        $this->eco = $manager->getPlugin("EconomyAPI") ?? $manager->getPlugin("BedrockEconomy") ?? null;
        unset($manager);
    }

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