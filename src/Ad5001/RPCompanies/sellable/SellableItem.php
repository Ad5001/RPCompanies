<?php


namespace Ad5001\RPCompanies;


use pocketmine\Server;
use pocketmine\Player;
use pocketmine\item\Item;
use Ad5001\RPCompanies\Main;







class SellableItem implements Sellable {


    protected $buyer;
    protected $price;
    protected $thingtosell;




   public function __construct(Buyer $buyer, int $price, $thingtosell) {
       if(!($thingtosell instanceof \pocketmine\item\Item)) {
           throw new Exception("Argument 3 passed in construction of \\Ad5001\\RPCompanies\\sellable\\SellableItem must be an instance of \\pocketmine\\item\\Item . Instance of ". get_class($thingtosell) . "pased.", 1);
       }
       $this->buyer = $buyer;
       $this->price = $price;
       $this->thingtosell = $thingtosell;
   }


  /*
  Return the Item in sell
  */
  public function getThingToSell() {
      return $this->thingtosell;
  }


  /*
  Return the kind of the object to sell. In this case: Item.
  */
  public function getKind() {
      return Sellable::ITEM;
  }
   
   
   /*
   To save it easily.
   */
   public function __toString() {
       return "SellableItem(".get_class($this->buyer)." {$this->buyer->__toString()}, $this->price, ".$this->thingtosell->getId().", " . $this->thingtosell->getMeta() . ", " . $this->thingtosell->getCount() . ", " . str_ireplace(",", "﴿﴾", $this->thingtosell->getCustomName()) . ")";
   }


   public static function __fromString(string $str) {
       if(substr($str, 0, strlen("SellableItem(") - 1) == "SellableItem(") {
           $str = substr($str, strlen("SellableItem(") - 1, strlen($str) - 1);
           $args = explode(", ", $str);
           // Buyer
           $bclass = explode(" ", $args[0])[0];
           $buyer = $bclass::__fromString(explode(" ", $args[0])[1]);
           $price = $args[1];
           $item = Item::get($args[2], $args[3]);
           $item->setCustomName(str_ireplace("﴿﴾", ",", $args[4]));
           return new SellableItem($buyer, $price, $item);
       }
   }


   public function getPrice() {
       return $this->price;
   }


   public function getSeller() {
       return $this->buyer;
   }


   public function buy(Buyer $buyer) {
       $buyer->getInventory()->addItem($this->thingtosell);
       $buyer->takeMoney($this->price);
   }




}