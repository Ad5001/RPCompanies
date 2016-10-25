<?php


namespace Ad5001\RPCompanies\tasks;



use pocketmine\Server;


use pocketmine\schedulerPluginTask;


use pocketmine\Player;



use Ad5001\RPCompanies\Main;







class ElectionCountdownTask extends PluginTask {




   public function __construct(Main $main) {


        parent::__construct($main);


        $this->main = $main;


        $this->server = $main->getServer();


    }




   public function onRun($tick) {


        foreach (\Ad5001\RPCompanies\country\CountryManager::getCountries() as $c) {
            if($c->getNextElectionTime() >= time() - 60*60*24) {
                $c->sendMessage(time() - $c->getNextElectionTime());
            }
            if($c->getNextElectionTime() <= time() && !$c->isElectionStarted()) {
                $c->startElection();
            }
            if($c->getEndElectionTime() <= time() && $c->isElectionStarted()) {
                $c->stopElection();
            }
        }


    }




}