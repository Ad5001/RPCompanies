<?php


namespace Ad5001\RPCompanies;



use pocketmine\Server;


use pocketmine\Player;


use Ad5001\RPCompanies\Main;


use Ad5001\RPCompanies\country\CountryManager;


use Ad5001\RPCompanies\country\Country;







class CountryChooser {




   public static function getCountryByBiome(int $biomeid) {
       foreach(CountryManager::getCountries() as $c) {
           if(in_array($biomeid, constant(get_class($c) . "::BIOMEID"))) {
               return $c;
           }
       }
       return null;
   }




   public static function getCountrysByPoliticModel(int $model) : array {
       $cs = [];
       foreach(CountryManager::getCountries() as $c) {
           if($model == constant(get_class($c) . "::MODEL")) {
               $cs[] = $c;
           }
       }
       return $cs;
   }




}