<?php


namespace Ad5001\RPCompanies;



use pocketmine\Server;


use pocketmine\Player;



use Ad5001\RPCompanies\Main;







interface Sellable {


	const ITEM = 0;
	const COMPANY = 1;
	const SERVICE = 2;


	public function __construct(Buyer $buyer, int $price, $thingtosell);


   public function getThingToSell();


   public function getKind();
   
   
   public function __toString();


   public static function __fromString(string $str);


   public function getPrice();


   public function getSeller();


   public function buy(Buyer $buyer);




}