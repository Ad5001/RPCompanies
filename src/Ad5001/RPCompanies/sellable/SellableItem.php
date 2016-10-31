<?php


namespace Ad5001\RPCompanies;


use pocketmine\Server;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\tile\Sign;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use Ad5001\RPCompanies\Main;







class SellableItem implements Sellable {


    const SIGNTOP = "§o§l§b§r§l[§r§l§fBuy§b§o]";


    protected $buyer;
    protected $price;
    protected $thingtosell;
    protected $sign;




   public function __construct(Buyer $buyer, int $price, $thingtosell, Sign $sign) {
       if(!($thingtosell instanceof \pocketmine\item\Item)) {
           throw new Exception("Argument 3 passed in construction of \\Ad5001\\RPCompanies\\sellable\\SellableItem must be an instance of \\pocketmine\\item\\Item . Instance of ". get_class($thingtosell) . "pased.", 1);
       }
       $this->buyer = $buyer;
       $this->price = $price;
       $this->thingtosell = $thingtosell;
       $this->sign = $sign;
       $this->sign->namedtag->Text1 = new StringTag("Text1", (string) self::SIGNTOP);
       $this->sign->namedtag->Text2 = new StringTag("Text2", $thingtosell->getName() . ":" . $thingtosell->getDamage() . " x " . $thingtosell->getCount());
       $this->sign->namedtag->Text3 = new StringTag("Text3", "Price: " . (string) Main::$instance->getEconomyProvider()->translate($price));
       $this->sign->namedtag->Text4 = new StringTag("Text4", "Seller: " . $buyer->getName());
       $this->sign->namedtag->selling = new IntTag("selling", Sellable::ITEM);
       $this->sign->namedtag->sellinfos = new StringTag("sellinfos", $this->__toString());
       $this->sign->sellinstance = $this;
       $this->sign->spawnToAll();
       
       if($this->sign->chunk){
           $this->sign->chunk->setChanged();
           $this->sign->level->clearChunkCache($this->chunk->getX(), $this->chunk->getZ());
       }
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
       return "SellableItem(".get_class($this->buyer)." {$this->buyer->__toString()}, $this->price, ".$this->thingtosell->getId().", " . $this->thingtosell->getMeta() . ", " . $this->thingtosell->getCount() . ", " . str_ireplace(",", "﴿﴾", $this->thingtosell->getCustomName()) . ", ".$this->sign->x.", ".$this->sign->y.", " . $this->sign->z.")";
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
           $sign = $this->getServer()->getLevelByName(Main::$instance->getConfig()->get("RPLevel"))->getTile(new pocketmine\math\Vector3($args[5], $args[6], $args[7]));
           return new SellableItem($buyer, $price, $item, $sign);
       }
   }


   public function getPrice() {
       return $this->price;
   }


   public function getSeller() {
       return $this->buyer;
   }


   public function buy(Buyer $buyer) {
       if($this->seller->getInventory()->contains($this->thingtosell) && $buyer->getMoney() > $this->price) {
           foreach($this->seller->getInventory()->getContents() as $index => $i){
               if($i->getId() == $this->thingtosell->getId() && $i->getId() == $this->thingtosell->getDamage() && $i->getCount() >= $this->thingtosell->getCount()){
                   $i->setCount($i->getCount() - $this->thingtosell->getCount());
                   if($i->getCount() < 1) {
                       $this->seller->getInventory()->clear($index);
                   } else {
                       $this->seller->getInventory()->setItem($index, $i);
                   }
               }
           }
           $buyer->getInventory()->addItem($this->thingtosell);
           $buyer->takeMoney($this->price);
           $this->seller->addMoney($this->price);
           return true;
       } else {
           return false;
       }
   }




}