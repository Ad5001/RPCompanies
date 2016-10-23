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
	protected $countryChange;
	protected $travel;
	
	
	
	
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
		
		$this->countryChange = [];
		
		$this->travel = [];
		
	}
	
	
	
	
	
	
	
	
	
	/*
	##########################
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
					$cOfP->onCitizenEnter($event->getPlayer());
                }
				$event->getPlayer()->country = $cOfP;
            }
			if(CountryManager::getCountryFromPlayer($event->getPlayer()) !== $event->getPlayer()->country && (!isset($this->travel[$event->getPlayer()->getName()]) || $this->travel[$event->getPlayer()->getName()]) !== CountryManager::getCountryFromPlayer($event->getPlayer())) {
				$event->getPlayer()->sendMessage(self::PREFIX . "§cYou're about to leave {
						$event->getPlayer()->country->getName()
					}
					to go to " . CountryManager::getCountryFromPlayer($event->getPlayer())->getName() . "\n" . self::PREFIX . "Do you want to (§ll§r)eave your current country, is this a simple (§lt§r)ravel (costs {
						$this->economy->translate(100)
					}
					) or doing (§ln§r)othing? Enter your choice in the chat.");
				$this->countryChange[$event->getPlayer()->getName()] = [CountryManager::getCountryFromPlayer($event->getPlayer()), $event->getTo()];
				$event->setCancelled();
			}
        }
    }


	/*
	Used to check if a player talks
	@param     $event    \pocketmine\event\player\PlayerChatEvent
	*/
	public function onPlayerChat(\pocketmine\event\player\PlayerChatEvent $event) {
		if($event->getPlayer()->getLevel()->getName() == $this->getConfig()->get("RPLevel")) {
			if(isset($this->countryChange[$event->getPlayer()->getName()])) {
				switch (strtolower($event->getMessage())) {
					case 'leave':
					case 'l':
					$event->getPlayer()->country->onCitizenLeave($event->getPlayer());
					$this->countryChange[$event->getPlayer()->getName()][0]->onCitizenEnter($event->getPlayer());
					$event->getPlayer()->country = $this->countryChange[$event->getPlayer()->getName()][0];
					$event->getPlayer()->teleport($this->countryChange[$event->getPlayer()->getName()][1]);
					$event->getPlayer()->sendMessage(self::PREFIX . "§2Welcome to " . $event->getPlayer()->country->getName() ." !");
					unset($this->countryChange[$event->getPlayer()->getName()]);
					break;
					case 'travel':
					case 't':
					$this->travel[$event->getPlayer()->getName()] = $this->countryChange[$event->getPlayer()->getName()][0];
					$this->economy->takeMoney($event->getPlayer()->getName(), 100);
					$this->economy->addMoney("§aCountry_" . $this->countryChange[$event->getPlayer()->getName()][0]->getName(), 100);
					$event->getPlayer()->sendMessage(self::PREFIX."§2You're now travelling on " . $this->travel[$event->getPlayer()->getName()]->getName() .".");
					unset($this->countryChange[$event->getPlayer()->getName()]);
					break;
					case 'nothing':
					case 'n':
					$event->getPlayer()->sendMessage(self::PREFIX."§2As you wish...");
					unset($this->countryChange[$event->getPlayer()->getName()]);
					break;
					default:
					$event->getPlayer()->sendMessage(self::PREFIX."§cWhat? I did not understand. You can choose 'n' to do nothing, 't' to go travel to this country for {$this->economy->translate(100)} or to 'l' to leave your country and install yourself into this new country.");
					break;
				}
			}
		}
	}
}