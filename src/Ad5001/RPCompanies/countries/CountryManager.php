<?php


namespace Ad5001\RPCompanies;



use pocketmine\Server;
use pocketmine\Player;
use pocketmine\level\Position;



use Ad5001\RPCompanies\Main;







class CountryManager {
	
	
	
	protected $countries = [];
	
	
	
	public static function registerCountry(Country $country) {

		self::$countries[$country->getName()] = $country;
		Main::$instance->getEconomyProvider()->createAccount("§aCountry_" . $country->getName());
		
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
	
	
	public static function getCountryFromPos(Position $pos) {
		if($pos->getLevel()->getName() == Main::$instance->getConfig()->get("RPLevel")) {
			$chunk = $pos->getLevel()->getChunk($pos->x, $pos->z);
			foreach(self::$countries as $name => $c) {
				foreach($c->getChunks() as $chunkid => $posarray) {
					if($chunk->x == $posarray["x"] && $chunk->z == $posarray["z"]) {
						return $c;
					}
				}
			}
		}
		return null;
	}
	
	
	
	public static function getCountryFromPlayer(Player $player) {
		foreach(self::$countries as $name => $c) {
			foreach($c->getChunks() as $chunkid => $posarray) {
				if($player->chunk->x == $posarray["x"] && $player->chunk->z == $posarray["z"]) {
					return $c;
				}
			}
		}
	}
	
	
	
	
	
}
