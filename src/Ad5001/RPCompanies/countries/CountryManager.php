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




}