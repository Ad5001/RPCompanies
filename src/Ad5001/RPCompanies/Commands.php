<?php


namespace Ad5001\RPCompanies;



use pocketmine\Server;

use pocketmine\Player;

use pocketmine\command\PluginCommand;

use pocketmine\command\CommandSender;

use pocketmine\level\Position;

// Countries releated classes
use Ad5001\RPCompanies\contries\CountryManager;
use Ad5001\RPCompanies\contries\Country;
use Ad5001\RPCompanies\generation\CountryChooser;


use Ad5001\RPCompanies\Main;







class Commands extends PluginCommand  {
	
	
	
	
	
	/*
	Register all the Commands
		    @param     $cmd    string
		    */
		public static function registerAll() {
		$main = Main::$instance;
		$cm = Server::getInstance()->getCommandMap();
		$cm->register(self::class, new Commands($main, "vote", "Vote for a player on your countrie's elections !", "/vote <player EXACT usename of your country>"));
	}
	
	
	
	
	public function __construct(Main $main, string $command, string $description, string $usage) {
		
		$this->main = $main;
		
		$this->usage = $usage;
		
		$this->server = $main->getServer();
		
		$this->setDescription($description);
		
		parent::__construct($command, $main);
		
		$this->cmd = $command;
		$this->usageMessage = $this->core->getLang("eng")->translateString($usage, []);
		
		
	}
	
	
	
	
	/*
	Called when one of the defined commands of the plugin has been called
		    @param     $sender     \pocketmine\command\CommandSender
		    @param     $cmd          \pocketmine\command\Command
		    @param     $label         mixed
		    @param     $args          array
		    */
		public function execute(\pocketmine\command\CommandSender $sender, \pocketmine\command\Command $cmd, $label, array $args) {
		if($sender instanceof Player) {
			if($sender->getLevel()->getName() == $this->main->getConfig()->get("RPLevel")) {
				switch($cmd->getName()) {
					case "vote":
					$c = CountryManager::getCountryOfPlayer($sender);
					if(constant(get_class($c) . "::MODEL") !== Country::DEMOCRATIC) {
						$sender->sendMessage(self::PREFIX . "§cYour country isn't democratic ! You cannot vote for your coountry leader !");
					} elseif(!$c->isElectionStarted()) {
						$sender->sendMessage(self::PREFIX . "§cNo election currently running on your country ! Wait " . $this->seconds2human(time()- $c->getNextElectionTime()));
					} elseif(isset($args[0])) {
                        $c->vote($sender, $args[0]);
                    } else {
                        return false;
                    }
					break;
				}
			} else {
				$sender->sendMessage(Main::PREFIX."§cYou must be on the RP level to execute this command.");
			}
		} else {
			$sender->sendMessage(Main::PREFIX."§cYou must be ingame to execute this command.");
		}
	}
	
	
	function seconds2human($ss) {
		$s = $ss%60;
		$m = floor(($ss%3600)/60);
		$h = floor(($ss%86400)/3600);
		$d = floor(($ss%2592000)/86400);
		
		return "$d days, $h hours, $m minutes, $s seconds";
	}
	
	
	
	
}
