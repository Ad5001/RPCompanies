<?php


namespace Ad5001\RPCompanies;

// Pocketmine classes
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\level\generator\Generator;

// Countries releated classes
use Ad5001\RPCompanies\contries\CountryManager;
use Ad5001\RPCompanies\contries\Country;
use Ad5001\RPCompanies\contries\USA;
use Ad5001\RPCompanies\contries\Russia;
use Ad5001\RPCompanies\contries\Peru;
use Ad5001\RPCompanies\contries\France;
use Ad5001\RPCompanies\contries\Egypt;
use Ad5001\RPCompanies\contries\China;
use Ad5001\RPCompanies\contries\Australia;
use Ad5001\RPCompanies\contries\Amazonia;






class Main extends PluginBase implements Listener {


    const PREFIX = "§l§o§a[§r§l§bRPCompanies§o§a]§r§f ";

    const AUTHOR = "Ad5001";

    const GITHUB = "https://github.com/Ad5001/RPCompanies";
	
	
	public $instance;
	
	
	
	
	public function onEnable(){
		
		$this->reloadConfig();
		
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		
		CountryManager::registerCountry(new USA($this, "USA", "Ad5001\\RPCompanies\\country\\USA"));
		CountryManager::registerCountry(new Russia($this, "Russia", "Ad5001\\RPCompanies\\country\\Russia"));
		CountryManager::registerCountry(new Peru($this, "Peru", "Ad5001\\RPCompanies\\country\\Peru"));
		CountryManager::registerCountry(new France($this, "France", "Ad5001\\RPCompanies\\country\\France"));
		CountryManager::registerCountry(new Egypt($this, "Egypt", "Ad5001\\RPCompanies\\country\\Egypt"));
		CountryManager::registerCountry(new China($this, "China", "Ad5001\\RPCompanies\\country\\China"));
		CountryManager::registerCountry(new Australia($this, "Australia", "Ad5001\\RPCompanies\\country\\Australia"));
		CountryManager::registerCountry(new Amazonia($this, "Amazonia", "Ad5001\\RPCompanies\\country\\Amazonia"));
		
		self::$instance = $this;
		
	}
	
	
	
	
	public function onLoad(){
		
		$this->saveDefaultConfig();
		
	}
	
	
	
	/*
	#########################
	Event methods !
	
	Used to power everything on the plugin.
	
	##########################
	*/
	
	
	
	public function onPlayerMove(\pocketmine\event\player\PlayerMoveEvent $event) {
		if($event->getPlayer()->getLevel()->getName() == $this->getConfig()->get("RPLevel")) {
			if(!isset($event->getPlayer()->country)) {
				$cOfP = CountryManager::getCountryOfPlayer($event->getPlayer());
				if(is_null($cOfP)) {
					$cOfP = CountryManager::getCountries()[array_keys(CountryManager::getCountries())[rand(0, count(CountryManager::getCountries()))]];
					// 					Beside this long line, it's basicly choosing a random country :P
                    $event->getPlayer()->sendMessage(self::PREFIX . "§2Welcome to RPCompanies !\n".self::PREFIX." §2You succefully joined country {$cOfP->getName()} !");
                }
            }
        }
    }
}