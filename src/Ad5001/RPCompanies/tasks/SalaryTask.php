<?php


namespace Ad5001\RPCompanies\tasks;



use pocketmine\Server;
use pocketmine\Player;



use Ad5001\RPCompanies\Main;







class SalaryTask extends RPPluginTask {
	
	
	
	
	public function onRun($tick) {
		
		
		foreach(CompanyManager::getCompanies() as $c) {
			foreach($c->getEmployes() as $e => $salary) {
				$c->takeMoney($salary);
				$this->getEconomyProvider()->addMoney($salary, $e);
			}
			if($c->getMoney() <= 0) {
				$this->getServer()->getScheduler()->scheduleDelayedTask(new CompanyDeleteTask($this->getOwner(), $c), 60*24*60*20);
				// 				Todo Notify the owner.
			}
		}
		
		
	}
	
	
	
	
}
