<?php


namespace Ad5001\RPCompanies\tasks;



use pocketmine\Server;


use pocketmine\schedulerPluginTask;


use pocketmine\Player;



use Ad5001\RPCompanies\Main;







class Task4 extends PluginTask {




   public function __construct(Main $main) {


        parent::__construct($main);


        $this->main = $main;


        $this->server = $main->getServer();


    }




   public function onRun($tick) {


        $this->main->getLogger()->debug('Task ' . get_class($this) . ' is running on $tick'); 


    }




}