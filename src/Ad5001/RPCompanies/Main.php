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


    public $instance;




   public function onEnable(){


        $this->reloadConfig();


        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        CountryManager::registerCountry(new USA($this, "USA"));
        CountryManager::registerCountry(new Russia($this, "Russia"));
        CountryManager::registerCountry(new Peru($this, "Peru"));
        CountryManager::registerCountry(new France($this, "France"));
        CountryManager::registerCountry(new Egypt($this, "Egypt"));
        CountryManager::registerCountry(new China($this, "China"));
        CountryManager::registerCountry(new Australia($this, "Australia"));
        CountryManager::registerCountry(new Amazonia($this, "Amazonia"));


        self::$instance = $this;

    }




    public function onLoad(){


        $this->saveDefaultConfig();


    }




    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){


        switch($cmd->getName()){


            case 'default':


            break;


        }


     return false;


    }


}