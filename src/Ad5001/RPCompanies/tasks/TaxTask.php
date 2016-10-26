<?php


namespace Ad5001\RPCompanies\tasks;



use pocketmine\Server;
use pocketmine\schedulerPluginTask;
use pocketmine\Player;

use Ad5001\RPCompanies\Main;
use Ad5001\RPCompanies\country\Country;







class TaxTask extends PluginTask {




   public function __construct(Main $main) {

        parent::__construct($main);

        $this->main = $main;

        $this->server = $main->getServer();

        $this->days = 0;

    }




   public function onRun($tick) {


        foreach (CompanyManager::getCompanies() as $c) {
            $country = $c->getBasedCountry();
            $t = $country->getTaxApplyTime();
            switch(true) {
                case $t == Country::DAILY:
                $this->main->getEconomyProvider()->takeMoney($c->getCurrentGain() * ($country->getTaxPercent() / 100));
                break;
                case $t == Country::WEEKLY && $this->days % 7 == 0:
                $this->main->getEconomyProvider()->takeMoney($c->getCurrentGain() * ($country->getTaxPercent() / 100));
                break;
                case $t == Country::MOUNTHLY && $this->days % 31 == 0:
                $this->main->getEconomyProvider()->takeMoney($c->getCurrentGain() * ($country->getTaxPercent() / 100));
                break;
                case $t == Country::QUARTERLY && $this->days % 93 == 0:
                $this->main->getEconomyProvider()->takeMoney($c->getCurrentGain() * ($country->getTaxPercent() / 100));
                break;
                case $t == Country::WEEKLY && $this->days % 365 == 0:
                $this->main->getEconomyProvider()->takeMoney($c->getCurrentGain() * ($country->getTaxPercent() / 100));
                break;
            }
        }

        $this->days++;


    }




}