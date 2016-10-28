<?php


namespace Ad5001\RPCompanies\tasks;



use pocketmine\Server;


use pocketmine\scheduler\PluginTask;


use pocketmine\Player;



use Ad5001\RPCompanies\Main;







class RPPluginTask extends PluginTask {




   public function __construct(Main $main) {


        parent::__construct($main);


        $this->main = $main;


        $this->server = $main->getServer();


    }




   public function onRun($tick) {}



   /*
   Return server instance.
   */
   public function getServer() {
       return $this->server;
   }


   /*
   Return economy provider.
   */
   public function getEconomyProvider() {
       return $this->main->getEconomyProvider();
   }


   /*
   Get the config  
   */
   public function getConfig() {
       return $this->main->getConfig();
   }

   /*
   Return the data folder
   */
   public function getDataFolder() {
       return $this->main->getDataFolder();
   }



}