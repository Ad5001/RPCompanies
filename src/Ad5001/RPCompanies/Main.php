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
use Ad5001\RPCompanies\generation\CountryChooser;
use Ad5001\RPCompanies\contries\Country;
use Ad5001\RPCompanies\contries\USA;
use Ad5001\RPCompanies\contries\Russia;
use Ad5001\RPCompanies\contries\Peru;
use Ad5001\RPCompanies\contries\France;
use Ad5001\RPCompanies\contries\Egypt;
use Ad5001\RPCompanies\contries\China;
use Ad5001\RPCompanies\contries\Australia;
use Ad5001\RPCompanies\contries\Amazonia;

// Tasks
use Ad5001\RPCompanies\tasks\ElectionCountdownTask;
use Ad5001\RPCompanies\tasks\TaxTask;

// Economy providers:
use Ad5001\RPCompanies\economy\EconomyProvider;
use Ad5001\RPCompanies\economy\EconomySProvider;
use Ad5001\RPCompanies\economy\PocketMoneyProvider;
// use Ad5001\RPCompanies\economy\EconomyPlusProvider;

// Companies related classes.
use Ad5001\RPCompanies\Company;
use Ad5001\RPCompanies\CompanyManager;





class Main extends PluginBase implements Listener {
	
	
	const PREFIX = "§l§o§a[§r§l§bRPCompanies§o§a]§r§f ";
	
	const AUTHOR = "Ad5001";
	
	const GITHUB = "https://github.com/Ad5001/RPCompanies";
	
	
	public static $instance;
	protected $countryChange;
	protected $travel;
	protected $economy;
	
	
	
	
	public function onEnable(){

		$pm = $this->getServer()->getPluginManager();

		if($pm->getPlugin("EconomyAPI") !== null) {
			$this->setEconomyProvider(new EconomySProvider()); 
		} elseif ($pm->getPlugin("PocketMoney") !== null) {
			$this->setEconomyProvider(new PocketMoneyProvider());
		} /* elseif ($pm->getPlugin("EconomyPlus") !== null) {
			$this->setEconomyProvider(new EconomyPlusProvider());
		}*/
		
		$this->reloadConfig();
		
		$pm->registerEvents($this, $this);
		
		CountryManager::registerCountry(new USA($this, "USA", "Ad5001\\RPCompanies\\country\\USA"));
		CountryManager::registerCountry(new Russia($this, "Russia", "Ad5001\\RPCompanies\\country\\Russia"));
		CountryManager::registerCountry(new Peru($this, "Peru", "Ad5001\\RPCompanies\\country\\Peru"));
		CountryManager::registerCountry(new France($this, "France", "Ad5001\\RPCompanies\\country\\France"));
		CountryManager::registerCountry(new Egypt($this, "Egypt", "Ad5001\\RPCompanies\\country\\Egypt"));
		CountryManager::registerCountry(new China($this, "China", "Ad5001\\RPCompanies\\country\\China"));
		CountryManager::registerCountry(new Australia($this, "Australia", "Ad5001\\RPCompanies\\country\\Australia"));
		CountryManager::registerCountry(new Amazonia($this, "Amazonia", "Ad5001\\RPCompanies\\country\\Amazonia"));

		CompanyManager::registerCompanies($this);
		
		self::$instance = $this;
		
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new ElectionCountdownTask($this), 20);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new TaxTask($this), 20*60*20);
	}
	
	
	
	
	public function onLoad(){
		
		self::$instance = $this;
		
		$db = new \SQLite3($main->getDataFolder() . "countries.db");
		$db->exec("IF OBJECT_ID('countries', 'U') IS NULL 
BEGIN
CREATE TABLE countries {
    name STRING,
    chunks STRING,
    owner STRING,
    old_owners STRING,
    is_claimed BOOL,
    citizens STRING,
	trourists STRING
}
END
");
		$db->exec("IF OBJECT_ID('elections', 'U') IS NULL 
BEGIN
CREATE TABLE elections {
    name STRING,
	modal INT,
    owner STRING,
    next_election INT,
	end_election INT,
	election_started BOOL,
    citizens STRING,
	votes STRING
}
END
");
		$db->exec("IF OBJECT_ID('taxs', 'U') IS NULL 
BEGIN
CREATE TABLE taxs {
    name STRING,
	tax_rate INT,
	tax_percent INT
}
END
");
		$db = new \SQLite3($main->getDataFolder() . "companies.db");
		$db->exec("IF OBJECT_ID('companies', 'U') IS NULL 
BEGIN
CREATE TABLE companies {
    name STRING,
	owner STRING,
	based_country STRING,
	current_gain INT,
	kind INT,
	employes_salary STRING,
	lands STRING,
	accepts_requests BOOL,
	pending_requests STRING,
	inventory STRING
}
END
");
		
		$this->saveDefaultConfig();
		
		$this->countryChange = [];
		
		$this->travel = [];
		
	}



	/*
	Called when the plugin disables
	*/
	public function onDisable() {
		foreach (CountryManager::getCountries() as $c) {
			$c->db->close();
		}
		foreach (CompanyManager::getCompanies() as $c) {
			$c->db->close();
		}
	}
	
	
	
	
	/*
	Returns the economy provider instance.   
	*/
	public function getEconomyProvider() {
		return $this->economy;
	}


	/*
	Sets the economy provider.
	@param     $ep    EconomyProvider
	*/
	public function setEconomyProvider(EconomyProvider $ep) {
		$this->economy = $ep;
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




	/*
	Called when a chunk generates.
	@param     $event    pocketmine\event\level\ChunkLoadEvent
	*/
	public function onChunkLoad(\pocketmine\event\level\ChunkLoadEvent $event) {
		if($event->getChunk()->getLevel()->getName() == $this->getConfig()->get("RPLevel") && is_null(CountryManager::getCountryFromPos(new \pocketmine\level\Position($event->getChunk()->x, 10, $event->getChunk()->z, $event->getChunk()->getLevel())))) {
			$c = ContryChooser::getCountryByBiomeId($event->getChunk()->getBiomeId(7, 7));
			$c->addChunk($event->getChunk());
		}
		if($event->getChunk()->getLevel()->getName() == $this->getConfig()->get("RPLevel")) {
			foreach($chunk->getTiles() as $tile) {
				if($tile instanceof \pocketmine\tile\Sign) {
					if(isset($tile->namedtag->sellinfos)) {
						if(substr($tile->namedtag->sellinfos->value, 0, strlen("SellableItem(") - 1) == "SellableItem(") {
							$shop = SellableItem::__fromString($tile->namedtag->sellinfos->value);
							$this->getLogger()->info(self::PREFIX . "§2Succefully loaded shop at " . new pocketmine\math\Vector3($tile->getX(), $tile->getY(), $tile->getZ() . " owned by " . $shop->getSeller()->getName() . " selling " . (string) $shop->getThingToSell() . "."));
						}
					}
				}
			}
		}
	}


	/*
	Detects when a player place if not in his zone.
	@param     $event    \pocketmine\event\block\BlockPlaceEvent
	*/
	public function onBlockPlace(\pocketmine\event\block\BlockPlaceEvent $event) {
		if($event->getBlock()->getLevel() == $this->getConfig()->get("RPLevel")) {
			$owner = "country";
			$company = CompanyManager::getCompanyOfPlayer($event->getPlayer());
			foreach(CompanyManager::getCompanies() as $c) {
				foreach($c->getChunks() as $chunk) {
					if($event->getBlock()->chunk->x == $chunk["x"] && $event->getBlock()->chunk->z == $chunk["z"]) {
						$owner = $c->getName();
					}
				}
			}
			if($owner !== $company->getName()) {
				$event->setCancelled();
				$event->getPlayer()->sendPopup("§cYou cannot modify this block.");
			}
		}
	}


	/*
	Detects when a player breaks if not in his zone.
	@param     $event    \pocketmine\event\block\BlockPlaceEvent
	*/
	public function onBlockBreak(\pocketmine\event\block\BlockBreakEvent $event) {
		if($event->getBlock()->getLevel() == $this->getConfig()->get("RPLevel")) {
			$owner = "country";
			$company = CompanyManager::getCompanyOfPlayer($event->getPlayer());
			foreach(CompanyManager::getCompanies() as $c) {
				foreach($c->getChunks() as $chunk) {
					if($event->getBlock()->chunk->x == $chunk["x"] && $event->getBlock()->chunk->z == $chunk["z"]) {
						$owner = $c->getName();
					}
				}
			}
			if($owner !== $company->getName()) {
				$event->setCancelled();
				$event->getPlayer()->sendPopup("§cYou cannot modify this block.");
			}
		}
	}



	/*
	Launched when a player touches a block.
	@param     $event    \pocketmine\event\player\PlayerInteractEvent
	*/
	public function onInteract(\pocketmine\event\player\PlayerInteractEvent $event) {
		if($event->getBlock()->getLevel() == $this->getConfig()->get("RPLevel")) {
			switch($event->getBlock()->getId()) {
				case 63:
				case 68:
				$tile = $event->getBlock()->getLevel()->getTile($block);
				if(isset($tile->sellninstance)) {
					if($tile->sellinstance->buy(new PlayerBuyer($event->getPlayer()))) {
						$sender->sendMessage(self::PREFIX . "§2Succefully bought " . $tile->sellinstance->getThingToSell()->getCount() . " " . $tile->sellinstance->getThingToSell()->getName() . ":". $tile->sellinstance->getThingToSell()->getDamage() . " for " . $this->getEconomyProvider()->translate($tile->sellinstance->getPrice()));
					}
				}
				break;
			}
		}
	}


	/*
	Called when a player finished to write on a sign.
	@param     $event   \pocketmine\event\block\SignChangeEvent
	*/
	public function onSignChange(\pocketmine\event\block\SignChangeEvent $event) {
		if($event->getBlock()->getLevel() == $this->getConfig()->get("RPLevel")) {
			if($event->getLine(0) == "shop" && preg_match("/^(\d{1,3})(:\d{1,3}){0,1}(x\d+){0,1}$/", $event->getLine(1)) > 0 && is_int($event->getLine(2)) && $event->getPlayer() instanceof Player && CompanyManager::getCompanyOfPlayer($event->getPlayer()) instanceof Company) {
				if(preg_match("/^(\d{1,3})(:\d{1,3})(x\d+)$/", $event->getLine(1))) {
					$count = explode("x", $event->getLine(1))[1];
					$line = substr(0, strlen($event->getLine(1)) - strlen($count) -1);
					$damage = explode(":", $line)[1];
					$id = explode(":", $line)[0];
				} elseif(preg_match("/^(\d{1,3})(:\d{1,3})$/", $event->getLine(1))) {
					$count = 1;
					$damage = explode(":", $event->getLine(1))[1];
					$id = explode(":", $event->getLine(1))[0];
				} elseif (preg_match("/^(\d{1,3})(x\d+)$/", $event->getLine(1))) {
					$count = explode("x", $event->getLine(1))[1];
					$line = substr(0, strlen($event->getLine(1)) - strlen($count) -1);
					$damage = 0;
					$id = explode("x", $event->getLine(1))[0];
				} elseif(preg_match("/^(\d{1,3})$/", $event->getLine(1))) {
					$count = 1;
					$damage = 0;
					$id = $event->getLine(1);
				}
				$item = \pocketmine\item\Item::get($id, $damage);
				$item->setCount($count);
				$seller = CompanyManager::getCompanyOfPlayer($event->getPlayer());
				$shop = new SellableItem($seller, $event->getLine(2), $item, $event->getBlock());
				$sender->sendMessage(Main::PREFIX . "§2Succefully created shop !");
			}
		}
	}
}