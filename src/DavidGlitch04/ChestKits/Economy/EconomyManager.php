<?php

declare(strict_types=1);

namespace DavidGlitch04\ChestKits\Economy;

use Closure;
use cooldogedev\BedrockEconomy\libs\cooldogedev\libSQL\context\ClosureContext;
use onebone\economyapi\EconomyAPI;
use DavidGlitch04\ChestKits\ChestKits;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

/**
 * Class EconomyManager
 * @package DavidGlitch04\ChestKits\Economy
 */
class EconomyManager{
    /** @var Plugin|null $eco */
    private ?Plugin $eco;
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
    public function getMoney(Player $player, Closure $callback): void {
        switch ($this->eco->getName()){
            case "EconomyAPI":
                $money = $this->eco->myMoney($player->getName());
		        assert(is_float($money));
		        $callback($money);
                break;
            case "BedrockEconomy":
                $this->eco->getAPI()->getPlayerBalance($player->getName(), ClosureContext::create(static function(?int $balance) use($callback) : void{
                    $callback($balance ?? 0);
                }));
                break;
            default:
                $this->eco->getAPI()->getPlayerBalance($player->getName(), ClosureContext::create(static function(?int $balance) use($callback) : void{
                    $callback($balance ?? 0);
                }));
        }
    }

    /**
     * @param Player $player
     * @param int $amount
     * @return bool
     */
    public function reduceMoney(Player $player, int $amount, Closure $callback){
        if($this->eco == null){
            $this->plugin->getLogger()->warning("You not have Economy plugin");
            return true;
        }
        switch ($this->eco->getName()){
            case "EconomyAPI":
                $callback($this->eco->reduceMoney($player->getName(), $amount) === EconomyAPI::RET_SUCCESS);
                break;
            case "BedrockEconomy":
                $this->eco->getAPI()->subtractFromPlayerBalance($player->getName(), (int) ceil($amount), ClosureContext::create(static function(bool $success) use($callback) : void{
                    $callback($success);
                }));
                break;
        }
    }
}