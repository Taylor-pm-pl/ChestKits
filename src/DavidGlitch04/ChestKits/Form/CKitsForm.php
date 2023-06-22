<?php

declare(strict_types=1);

namespace DavidGlitch04\ChestKits\Form;

use DavidGlitch04\ChestKits\{
    ChestKits,
    Economy\EconomyManager
};
use Vecnavium\FormsUI\SimpleForm;
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
     * @return void
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
            $player->sendMessage($this->chestkits->getMessage("no.kits"));
            return;
        }
        foreach ($this->chestkits->kits->getAll() as $key){
            $form->addButton($key["name"]."\n".$key["price"]);
        }
        $config = $this->chestkits->getConfig();
        $form->setTitle($config->get("form.title", "Chestkits Form"));
        $form->setContent($config->get("form.content", "Choose kit you want to buy:"));
        $player->sendForm($form);
    }

    private function PurchaseForm(Player $player, int $key): void
    {
        $plugin = $this->chestkits;
        $kits = $plugin->kits->get(array_keys($plugin->kits->getAll())[$key]);
        $form = new SimpleForm(function (Player $player, $data) use ($kits, $plugin){
            if(!isset($data)){
                $this->openForm($player);
                return;
            }
            switch ($data){
                case 0:
                    $ecoManager = new EconomyManager($plugin);
                    $chestkits = $this->chestkits;
                    $ecoManager->reduceMoney($player, (int)$kits["price"], static function(bool $success) use($player, $kits, $chestkits, $plugin) : void {
                        if($success){
                            $chestkits->sendKit(
                                $player,
                                $kits["name"],
                                $kits["lore"]
                            );
                            $player->sendMessage($plugin->getMessage("purchase.success"));
                        }else{                        
                            $player->sendMessage($plugin->getMessage("purchase.fail", [(int)$kits["price"]]));
                        }
                    });
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
    }
}