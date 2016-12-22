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
		$cm->register(self::class, new Commands($main, "company", "Commpany main command..", "/company <command> [parameter]"));
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
								default:
								$sender->sendMessage(Main::PREFIX . "-=< Help for /mngcountry  >=-");
								$sender->sendMessage(Main::PREFIX . "§aseemoney §6> §fSee the country's money.");
								$sender->sendMessage(Main::PREFIX . "§agivemoney <country> <amount> §6> §fGive money to an another country.");
								$sender->sendMessage(Main::PREFIX . "§apay <player> <amount> §6> §fPay someone on your country.");
								$sender->sendMessage(Main::PREFIX . "§asettaxtime <daily|weekly|mounthly|yearly> §6> §fSet tax apply time (time as minecraft. 1 day = 20 minutes).");
								break;
							}
							return true;
						} else {
							return false;
						}
					} else {
						$sender->sendMessage(Main::PREFIX . "§cYou need to be the leader your country to manage it.");
						return true;
					}
					break;


					case 'company':
					if($sender instanceof Player) {
						if(isset($args[0])) {
							switch($args[0]) {
								case 'create':
								if(Main::$instance->getEconomyProvider()->getMoney($sender->getName()) > (int) Main::$instance->getConfig()->get("DefaultCompanyPrice")) {
									if(isset($args[2])) {
										if(is_null(CompanyManager::getCompanyByName($args[1]))) {
												if(isset(Company::TYPES[strtoupper($args[2])])) {
													$kind = Company::TYPES[strtoupper($args[2])];
													$kindstr = strtolower($args[2]);
												} else {
													$sender->sendMessage(Main::PREFIX . "§cUnknown kind ! Possible kind are: 'mining', 'farming', 'tools' or 'blockbank'");
												}
											if(isset($kind)) {
												Main::$instance->getEconomyProvider()->takeMoney((int) Main::$instance->getConfig()->get("DefaultCompanyPrice"), $sender->getName());
												$company = Company::createCompany($args[1], $sender, $kind);
												$sender->sendMessage(Main::PREFIX . "§2Succefully created company " . $company->getName() . " in $kindstr ! Manage your comany by using /company mng <command> [arg]");
											}
										} else {
											$sender->sendMessage(Main::PREFIX . "§cCompany with name $args[1] already exists !");
										}
									} else {
										$sender->sendMessage(Main::PREFIX . "§cUsage: /company create <company name> <kind>");
									}
								} else {
									$sender->sendMessage(Main::PREFIX."§cYou're too poor to create a company (need " . Main::$instance->getEconomyProvider()->translate(Main::$instance->getConfig()->get("DefaultCompanyPrice")) . "$ to create one).");
								}
								break;
								
								case "mng":
								case "manage":
								if($sender->getName() == CompanyManager::getCompanyOfPlayer($sender)->getOwner()) {
									$c = CompanyManager::getCompanyOfPlayer($sender);
									if(isset($args[1])) {
										switch ($args[1]) {

											case 'employe':
											if(isset($args[2])) {
												if(isset($c->getPendingRequests()[$args[2]])) {
													$money = $c->getPendingRequests()[$args[2]];
													$c->engageEmploye($args[2], $c->getPendingRequests()[$args[2]]);
													$sender->sendMessage(Main::PREFIX . "§2Succefully engaged $args[2] for " . Main::$instance->getEconomyProvider()->translate($money));
												} else {
													$sender->sendMessage(Main::PREFIX . "§c$args[2] hasn't request to be an employe on your company.");
												}
											} else {
												$sender->sendMessage(Main::PREFIX."§cUsage: /company mng employe <player>");
											}
											break;

											case 'deny':
											if(isset($args[2])) {
												if(isset($c->getPendingRequests()[$args[2]])) {
													$money = $c->getPendingRequests()[$args[2]];
													$c->refuseRequest($args[2]);
													$sender->sendMessage(Main::PREFIX . "§2Succefully denied $args[2] for " . Main::$instance->getEconomyProvider()->translate($money));
												} else {
													$sender->sendMessage(Main::PREFIX . "§c$args[2] hasn't request to be an employe on your company.");
												}
											} else {
												$sender->sendMessage(Main::PREFIX."§cUsage: /company mng deny <player>");
											}
											break;

											case 'requests':
											if(isset($args[2])) {
												switch($args[2]) {
													case "false":
													case "f":
													case "no":
													case "0":
													case false:
													case 0:
													$c->setAcceptingRequests(false);
													$sender->sendMessage(Main::PREFIX . "§2Your company is now denying requests.");
													break;
													case "true":
													case "t":
													case "yes":
													case "1":
													case true:
													case 1:
													$c->setAcceptingRequests(true);
													$sender->sendMessage(Main::PREFIX . "§2Your company is now accepting requests.");
													break;
													case "view":
													$sender->sendMessage(Main::PREFIX . "§2-=< List of all your requests >=-");
													foreach ($c->getPendingRequests() as $player => $salary) {
														$sender->sendMessage(Main::PREFIX."§2$player §6> §f ".Main::$instance->getEconomyProvider()->translate($salary));
													}
													$sender->sendMessage(Main::PREFIX . "§2To accept a request, do /company mng employe <player>. To deny a request, do /company mng deny <player>. To not accept any longer requests a request, do /company mng requests false. ");
													break;
													default:
													$sender->sendMessage(Main::PREFIX . "§cUsage: /company mng requests <true|false|view>");
													break;
												}
											}
											break;

											case "land":
											if($sender instanceof Player) {
												$land = "free";
												foreach(CountryManager::getCompanies() as $cs) {
													foreach($cs->getLands() as $lands) {
														if($lands["x"] == $sender->chunk->x && $lands["z"] == $sender->chunk->z) {
															$land = $cs->getName();
														}
													}
												}
												if(isset($args[2])) {
													switch($args[2]) {
														case 'buy':
														if($land == "free") {
															if($c->getMoney() > 5000) {
																$c->takeMoney(5000);
																$c->addLand($sender->chunk);
																$sender->sendMessage(Main::PREFIX . "§2Succefully bought the land your currently standing on (x={$sender->chunk->x},z={$sender->chunk->z})");
															} else {
																$sender->sendMessage(Main::PREFIX . "§cYour company doesn't have enought money to buy a land for " . Main::$instance->getEconomyProvider()->translate(5000) .".");
															}
														} else {
															$sender->sendMessage(Main::PREFIX . "§cThis land is already a property of $land.You cannot buy it.");
														}
														break;
														case 'sell':
														if($land == $c->getName()) {
															$c->addMoney(5000);
															$c->removeLand($sender->chunk);
															$sender->sendMessage(Main::PREFIX . "§2Succefully bought the land your currently standing on (x={$sender->chunk->x},z={$sender->chunk->z})");
														} else {
															$sender->sendMessage(Main::PREFIX . "§cThis land isn't your companie's property !You cannot buy it. It's $land's !");
														}
														break;
														case 'view':
														$sender->sendMessage(Main::PREFIX . "§2This land is $land's. It's located in x={$sender->chunk->x} and z={$sender->chunk->z}. It's in the country " . CountryManager::getCountryFromPos(new Position($sender->chunk->x, 10, $sender->chunk->z,$sender->getLevel()))->getName() . ".");
														break;
														case "count":
														$sender->sendMessage(Main::PREFIX. "§2-=< Your companies lands >=-");
														foreach ($c->getLands() as $land) {
															$country = CountryManager::getCountryFromPos(new Position($land["x"], 10,$land["z"], $sender->getLevel()));
															$sender->sendMessage(Main::PREFIX . "§2x=" . $land['x'] . " | z=" . $land["z"] . " | " . $country->getName());
														}
														break;
														default:
														$sender->sendMessage(Main::PREFIX . "-=< Help for /company mng land >=-");
														$sender->sendMessage(Main::PREFIX."§abuy §6> §fBuy the land from the country to make it exploitable. Cost: " . Main::$instance->getEconomyProvider()->translate(5000));
														$sender->sendMessage(Main::PREFIX."§asell §6> §fSell a land. Gives you: " . Main::$instance->getEconomyProvider()->translate(2500));
														$sender->sendMessage(Main::PREFIX."§aview §6> §fView carateristsics of the land you're currently standing on.");
														$sender->sendMessage(Main::PREFIX."§acount §6> §fSee all your lands.");
														break;
													}
												} else {
													$sender->sendMessage(Main::PREFIX . "-=< Help for /company mng land >=-");
													$sender->sendMessage(Main::PREFIX."§abuy §6> §fBuy the land from the country to make it exploitable. Cost: " . Main::$instance->getEconomyProvider()->translate(5000));
													$sender->sendMessage(Main::PREFIX."§asell §6> §fSell a land. Gives you: " . Main::$instance->getEconomyProvider()->translate(2500));
													$sender->sendMessage(Main::PREFIX."§aview §6> §fView carateristsics of the land you're currently standing on.");
													$sender->sendMessage(Main::PREFIX."§acount §6> §fSee all your lands.");
												}
											}
											break;

											case "setowner":
											if(isset($args[2])) {
												if(isset($c->getEmployes()[$args[2]])) {
													$c->setOwner($args[2]);
												} else {
													$sender->sendMessage(Main::PREFIX . "§c$args[2] isn't one of your employes ! He cannot be the owner !");
												}
											} else {
												$sender->sendMessage(Main::PREFIX . "§cUsage: /company mng setowner <employe from your company>");
											}
											break;
											
											default:
											$sender->sendMessage(Main::PREFIX . "-=< Help for /company mng >=-");
											$sender->sendMessage(Main::PREFIX."§aemploye §6> §fEmploye someone that requested to join your company.");
											$sender->sendMessage(Main::PREFIX."§adeny §6> §fDeny a request from someone requesting to join your company.");
											$sender->sendMessage(Main::PREFIX."§arequests §6> §fManage your request system.");
											$sender->sendMessage(Main::PREFIX."§acount §6> §fSee all your lands.");
											break;
										}
									}
								}
								break;
							}
						}
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
		} else {
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
