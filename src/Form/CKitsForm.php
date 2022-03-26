<?php

namespace DavidGlitch04\ChestKits\Form;

use DavidGlitch04\ChestKits\{
    ChestKits,
    Economy\EconomyManager
};
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\{
    player\Player,
    utils\TextFormat
};

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
    private function openForm(Player $player) {
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
            $player->sendMessage($this->chestkits->getMessage("no.kits"));
            return false;
        }
        foreach ($this->chestkits->kits->getAll() as $key){
            $form->addButton($key["name"]."\n".$key["price"]);
        }
        $config = $this->chestkits->getConfig();
        $form->setTitle($config->get("form.title", "Chestkits Form"));
        $form->setContent($config->get("form.content", "Choose kit you want to buy:"));
        $player->sendForm($form);
        return $form;
    }

    private function PurchaseForm(Player $player, int $key){
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
                        $player->sendMessage($plugin->getMessage("purchase.success"));
                        return false;
                    } else{
                        $player->sendMessage($plugin->getMessage("purchase.fail", [(int)$kits["price"]]));
                        return false;
                    }
                case 1:
                    //NOTHING
                    break;
            }
        });
        $config = $this->chestkits->getConfig();
        $form->setTitle($config->get("purchase.title", "Purchase Form"));
        $form->setContent($kits["content"]);
        $form->addButton($config->get("purchase.accept", "Accept"));
        $form->addButton($config->get("purchase.decline", "Decline"));
        $player->sendForm($form);
        return $form;
    }
}