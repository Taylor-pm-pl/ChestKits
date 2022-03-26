<?php

namespace DavidGlitch04\ChestKits\Form;

use DavidGlitch04\ChestKits\ChestKits;
use DavidGlitch04\ChestKits\Economy\EconomyManager;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\player\Player;

/**
 * Class CKitsForm
 * @package DavidGlitch04\ChestKits\Form
 */
class CKitsForm {
    /** @var Player $player */
    private Player $player;
    /** @var ChestKits $chestkits */
    private ChestKits $chestkits;

    /**
     * CKitsForm constructor.
     * @param ChestKits $chestkits
     * @param Player $player
     */
    public function __construct(ChestKits $chestkits, Player $player){
        $this->chestkits = $chestkits;
        $this->player = $player;
        $this->openForm($this->player);
    }

    /**
     * @param Player $player
     * @return bool|SimpleForm
     */
    private function openForm(Player $player): void {
        $form = new SimpleForm(function (Player $player, $data){
            if(!isset($data)){
                return false;
            }
            $this->PurchaseForm(
                $player,
                $data
            );
        });
        if(empty($this->chestkits->kits->getAll())){
            $player->sendMessage(ChestKits::getLanguage()->translateString("no.kits"));
            return;
        }
        foreach ($this->chestkits->kits->getAll() as $key){
            $form->addButton($key["name"]."\n".$key["price"]);
        }
        $config = $this->chestkits->getConfig();
        $form->setTitle($config->get("Form.title", "Chestkits Form"));
        $form->setContent($config->get("Form.content", "Choose kit you want to buy:"));
        $player->sendForm($form);
    }

    private function PurchaseForm(Player $player, int $key): void{
        $plugin = $this->chestkits;
        $kits = $plugin->kits->get(array_keys($plugin->kits->getAll())[$key]);
        $form = new SimpleForm(function (Player $player, $data) use ($kits, $plugin){
            if(!isset($data)){
                return $this->openForm($player);
            }
            switch ($data){
                case 0:
                    $ecoManager = new EconomyManager($plugin);
                    $money = $ecoManager->getMoney($player);
                    if($money >= (int)$kits["price"]){
                        $ecoManager->reduceMoney($player, (int)$kits["price"]);
                        $this->chestkits->sendKit(
                            $player,
                            $kits["name"],
                            $kits["lore"]
                        );
                        $player->sendMessage(ChestKits::getLanguage()->translateString("purchase.success"));
                        return;
                    } else{
                        $player->sendMessage(ChestKits::getLanguage()->translateString("purchase.fail", [(int)$kits["price"]]));
                        return;
                    }
                case 1:
                    //NOTHING
                    break;
            }
        });
        $config = $this->chestkits->getConfig();
        $form->setTitle($config->get("Purchase.title", "Purchase Form"));
        $form->setContent($kits["content"]);
        $form->addButton($config->get("Purchase.accept", "Accept"));
        $form->addButton($config->get("Purchase.decline", "Decline"));
        $player->sendForm($form);
    }
}