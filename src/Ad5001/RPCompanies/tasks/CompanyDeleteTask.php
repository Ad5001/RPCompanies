<?php


namespace Ad5001\RPCompanies\tasks;


use pocketmine\Server;
use pocketmine\Player;

use Ad5001\RPCompanies\company\Company;
use Ad5001\RPCompanies\Main;







class CompanyDeleteTask extends RPPluginTask {



    /*
    Constructs the class
    @param     $main      Main
    */
    public function __construct(Main $main, Company $company) {
        parent::__construct($main);
        $this->company = $company;
    }




   public function onRun($tick) {
       if($this->company->getMoney() <= 0) {
           CompanyManager::deleteCompany($this->company->getName());
           // TODO Notify salaries and owner that the company went bankrupt
       }
    }




}