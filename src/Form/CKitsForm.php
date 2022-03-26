<?php

namespace DavidGlitch04\ChestKits\Form;

use DavidGlitch04\ChestKits\ChestKits;
use DavidGlitch04\ChestKits\Economy\EconomyManager;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\player\Player;

class CKitsForm {

    private Player $player;

    private ChestKits $chestkits;

    public function __construct(ChestKits $chestkits, Player $player){
        $this->chestkits = $chestkits;
        $this->player = $player;
        $this->openForm($this->player);
    }

    private function openForm(Player $player){
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
            $player->sendMessage("No have any kits");
            return true;
        }
        foreach ($this->chestkits->kits->getAll() as $key){
            $form->addButton($key["name"]."\n".$key["price"]);
        }
        $form->setTitle("ChestKits");
        $form->setContent("Please choose kit:");
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
                        $player->sendMessage("Buy kit success");
                        return false;
                    } else{
                        $player->sendMessage("You need ".(int)$kits["price"]." to buy this kit.");
                        return false;
                    }
                    break;
                case 1:
                    //NOTHING
                    break;
            }
        });
        $form->setTitle("Purchase Form");
        $form->setContent($kits["content"]);
        $form->addButton("Yes");
        $form->addButton("No");
        $player->sendForm($form);
        return $form;
    }
}