<?php


namespace Ad5001\RPCompanies;



use pocketmine\Server;
use pocketmine\Player;
use pocketmine\inventory\InventoryHolder;



use Ad5001\RPCompanies\Main;







interface Buyer extends InventoryHolder {
    
    // Can be a player or a company


   public function getMoney();


   public function getName();
   
   
   public function __toString();


   public static function __fromString(string $str);


   public function setMoney(int $amount);


   public function addMoney(int $amount);


   public function takeMoney(int $amount);


}