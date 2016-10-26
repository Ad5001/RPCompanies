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
		$cm->register(self::class, new Commands($main, "mngcountry", "Manage your country.", "/mngcountry <command> [parameter]"));
		$cm->register(self::class, new Commands($main, "select", "Choose the one who will succeed you in the dicatorship..", "/select <player EXACT usename of your country>"));
	}
	
	
	
	
	public function __construct(Main $main, string $command, string $description, string $usage) {
		
		$this->main = $main;
		
		$this->usage = $usage;
		
		$this->server = $main->getServer();
		
		$this->setDescription($description);
		
		parent::__construct($command, $main);
		
		$this->cmd = $command;
		$this->usageMessage = $usage;
		
		
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
						$sender->sendMessage(Main::PREFIX . "§cYour country isn't democratic ! You cannot vote !");
					}
					elseif(!$c->isElectionStarted()) {
						$sender->sendMessage(Main::PREFIX . "§cNo election currently running on your country ! Wait " . $this->seconds2human(time()- $c->getNextElectionTime()));
					} elseif(isset($args[0])) {
						if(in_array($args[0], $c->getCitizens())) {
							$c->vote($sender, $args[0]);
							$sender->sendMessage(Main::PREFIX . "§2Succefully set your vote to $args[0]");
						} else {
							$sender->sendMessage(Main::PREFIX . "§cNo player in your country has name $args[0]");
						}
					} else {
						return false;
					}
					break;
					
					
					case 'mngcountry':
					$c = CountryManager::getCountryOfPlayer($sender);
					if($sender->getName() == $c->getOwner()) {
						if(isset($args[0])) {
							switch (strtolower($args[0])) {
								case 'seemoney':
								$sender->sendMessage(Main::PREFIX . "§aYour country's money: ".Main::$instance->getEconomyProvider()->getMoney("§aCountry_" . $c->getName()));
								break;

								case 'givemoney':
								if(isset($args[2])) {
									$ep = Main::$instance->getEconomyProvider();
									if($ep->accountExists("§aCountry_" . $args[1])) {
										if (is_int($args[2])) {
											if($args[2] < $ep->getMoney("§aCountry_" . $c->getName()) / 10) {
												$ep->addMoney($args[2], "§aCountry_" . $args[1]);
												$ep->takeMoney($args, "§aCountry_" . $c->getName());
											} else {
												$sender->sendMessage(Main::PREFIX . "§cYou cannot transact that much money !");
											}
										} else {
											$sender->sendMessage(Main::PREFIX . "§cAmount must be numeric !");
										}
									} else {
										$sender->sendMessage(Main::PREFIX . "§cNo country exists with name $args[1] !");
									}
								} else {
									$sender->sendMessage(Main::PREFIX . "§cUsage: /mngcountry givemoney <amount> !");
								}
								break;

								case 'pay':
								if(isset($args[2])) {
									$ep = Main::$instance->getEconomyProvider();
									if($ep->accountExists("§aCountry_" . $args[1])) {
										if (is_int($args[2])) {
											if($args[2] < 4000) { // Preventing from taking so much money. But this is based on fair :)
												$ep->addMoney($args[2], "§aCountry_" . $args[1]);
												$ep->takeMoney($args, "§aCountry_" . $c->getName());
											} else {
												$sender->sendMessage(Main::PREFIX . "§cYou cannot transact that much money !");
											}
										} else {
											$sender->sendMessage(Main::PREFIX . "§cAmount must be numeric !");
										}
									} else {
										$sender->sendMessage(Main::PREFIX . "§cNo country exists with name $args[1] !");
									}
								} else {
									$sender->sendMessage(Main::PREFIX . "§cUsage: /mngcountry givemoney <amount> !");
								}
								break;
								
								case "settaxtime":
								if(isset($args[1])) {
									switch (strtolower($args[1])) {
										case 'year':
										case 'yearly':
											$c->db->exec("UPDATE taxs SET tax_rate = " . Country::YEARLY . " WHERE name = '" . $c->getName() ."'");
											$sender->sendMessage(Main::PREFIX . "§2Taxes from " . $c->getName() . " (" . constant(get_class($c) . "::COMPANYTAX") . "%) is now applied yearly.");
										break;
										case 'quarter':
										case 'quarterly':
											$c->db->exec("UPDATE taxs SET tax_rate = " . Country::QUARTERLY . " WHERE name = '" . $c->getName() ."'");
											$sender->sendMessage(Main::PREFIX . "§2Taxes from " . $c->getName() . " (" . constant(get_class($c) . "::COMPANYTAX") . "%) is now applied quarterly.");
										break;
										case 'mounthly':
										case 'mounth':
											$c->db->exec("UPDATE taxs SET tax_rate = " . Country::MOUNTHLY . " WHERE name = '" . $c->getName() ."'");
											$sender->sendMessage(Main::PREFIX . "§2Taxes from " . $c->getName() . " (" . constant(get_class($c) . "::COMPANYTAX") . "%) is now applied mounthly.");
										break;
										case 'weekly':
										case 'week':
											$c->db->exec("UPDATE taxs SET tax_rate = " . Country::WEEKLY . " WHERE name = '" . $c->getName() ."'");
											$sender->sendMessage(Main::PREFIX . "§2Taxes from " . $c->getName() . " (" . constant(get_class($c) . "::COMPANYTAX") . "%) is now applied weekly.");
										break;
										case 'daily':
										case 'day':
											$c->db->exec("UPDATE taxs SET tax_rate = " . Country::DAILY . " WHERE name = '" . $c->getName() ."'");
											$sender->sendMessage(Main::PREFIX . "§2Taxes from " . $c->getName() . " (" . constant(get_class($c) . "::COMPANYTAX") . "%) is now applied daily.");
										break;
										default:
										$sender->sendMessage(Main::PREFIX . "§c$args[1] is not an applicable time tax taking. It can be yearly, mounthly, weekly or daily.");
										break;
										
									}
								} else {
									$sender->sendMessage(Main::PREFIX . "§cUsage: /mngcountry settaxtime <yearly|mounthly|weekly|daily> !");
								}
								break;
							}
						} else {
							return false;
						}
					} else {
						$sender->sendMessage(Main::PREFIX . "§cYou need to be the leader your country to manage it.");
					}
					break;
					
					
					case "select":
					$c = CountryManager::getCountryOfPlayer($sender);
					if($sender->getName() == $c->getOwner()) {
						if(isset($args[0])) {
							if ($c->isElectionStarted() && constant(get_class($c) . "::MODEL") == Country::DICTATORSHIP) {
								foreach ($c->getCitizens() as $citi) {
									if ($citi == $args[0]) {
										$c->select($citi);
										$sender->sendMessage(Main::PREFIX . "§2Succefully choosed $citi as your successor !");
										$c->stopElection();
									}
								}
							} else {
								$sender->sendMessage(Main::PREFIX . "§cLooks like your country is not a dicatorship or/and you cannot pick a successor yet.");
							}
						} else {
							return false;
						}
					} else {
						$sender->sendMessage(Main::PREFIX . "§cYou need to be the leader your country to manage it.");
					}
					break;
				}
			} else {
				$sender->sendMessage(Main::PREFIX."§cYou must be on the RP level to execute this command.");
			}
		}
		else {
			$sender->sendMessage(Main::PREFIX."§cYou must be ingame to execute this command.");
		}
	}
	
	
	function seconds2human($ss) {
		$s = $ss%60;
		$m = floor(($ss%3600)/60);
		$h = floor(($ss%86400)/3600);
		$d = floor(($ss%2592000)/86400);
		
		return "$d days, $h hours, $m minutes and $s seconds";
	}
	
	
	
	
}
