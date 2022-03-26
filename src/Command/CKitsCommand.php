<?php

namespace DavidGlitch04\ChestKits\Command;

use DavidGlitch04\ChestKits\ChestKits;
use DavidGlitch04\ChestKits\Form\CKitsForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;

/**
 * Class CKitsCommand
 * @package DavidGlitch04\ChestKits\Command
 */
class CKitsCommand extends Command implements PluginOwned{
    /** @var ChestKits $chestkits */
    private ChestKits $chestkits;

    /**
     * CKitsCommand constructor.
     * @param ChestKits $chestkits
     */
    public function __construct(ChestKits $chestkits) {
        $this->chestkits = $chestkits;
        parent::__construct("chestkits");
        $this->setDescription("Chestkits command");
    }

    /**
     * @return Plugin
     */
    public function getOwningPlugin(): Plugin
    {
        return $this->chestkits;
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void{
        if($sender instanceof Player){
            new CKitsForm(
                $this->chestkits,
                $sender
            );
            return;
        } else{
            $this->chestkits->getLogger()->warning("Please use this command in game!");
        }
    }
}