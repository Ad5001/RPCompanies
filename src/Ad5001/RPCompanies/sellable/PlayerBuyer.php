<?php


namespace Ad5001\RPCompanies;



use pocketmine\Server;
use pocketmine\Player;
use pocketmine\OfflinePlayer;



use Ad5001\RPCompanies\Main;







class PlayerBuyer extends Buyer {




   public function __construct($player) {

       if(!($player instanceof Player) and !($player instanceof OfflinePlayer)) {
           throw new Exception("Argument passed to the construction of player can only be a Player (\\pocketmine\\Player) or an offlineplayer (\\pocketmine\\OfflinePlayer", 1);   
       }
        $this->player = $player;
    }


    /*
    Return the name of the player
    */
    public function getName() {
        return $this->player->getName();
    }



    /*
    Return player's money
    */
    public function getMoney() {
        return Main::$instance->getEconomyProvider()->getMoney($player->getName());
    }


    /*
    Sets player's money.
    @param     $money    int
    */
    public function setMoney(int $money) {
        return Main::$instance->getEconomyProvider()->setMoney($money, $player->getName());
    }


    /*
    Adds to player's money.
    @param     $money    int
    */
    public function addMoney(int $money) {
        return Main::$instance->getEconomyProvider()->addMoney($money, $player->getName());
    }


    /*
    Takes from player's money.
    @param     $money    int
    */
    public function takeMoney(int $money) {
        return Main::$instance->getEconomyProvider()->takeMoney($money, $player->getName());
    }


    /*
    Get players's inventory
    @return \pocketmine\inventory\Inventory
    */
    public function getInventory() : \pocketmine\inventory\Inventory {
        return $this->player->getInventory();
    }


    /*
    Stringify the player.
    */
    public function __toString() {
        return "PlayerBuyer({$this->player->getName()})";
    }


    /*
    Return a player buyer from a string instance.
    @param     $str    string
    */
    public static function __fromString(string $str) {
       if(substr($str, 0, strlen("PlayerBuyer(") - 1) == "PlayerBuyer(") {
           $str = substr($str, strlen("PlayerBuyer(") - 1, strlen($str) - 1);
           return new PlayerBuyer(new OfflinePlayer(Server::getInstance(), $str));
       }
    }





}