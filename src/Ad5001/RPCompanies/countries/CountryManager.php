<?php


namespace Ad5001\RPCompanies;



use pocketmine\Server;


use pocketmine\Player;



use Ad5001\RPCompanies\Main;







class CountryManager {



   protected $countries = [];




   public function __construct(Main $main) {

        $this->main = $main;

        $this->server = $main->getServer();

    }



    public static function registerCountry(Country $country) {
        self::$countries[$country->getName()] = $country;
    }



    public static function getCountries() : array {
        return self::$countries;
    }


    public function getCountryByName(string $name) {
        return isset(self::$countries[$name]) ? self::$countries[$name] : null;
    }


    public static function getCountryOfPlayer(Player $player) {
        foreach(self::getCountries() as $c) {
            if(in_array($player->getName(), $c->getCitizens())) {
                return $c;
            }
        }
        return null;
    }




}