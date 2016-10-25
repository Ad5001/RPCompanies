<?php


namespace Ad5001\RPCompanies;



use pocketmine\Server;


use pocketmine\Player;



use Ad5001\RPCompanies\Main;







class EconomySProvider extends EconomyProvider {




   public function __construct() {

       parent::__construct(Main::$instance, Server::getInstance()->getPluginManager()->getPlugin("EconomyAPI"));

    }



    /*
    Check if an account exists
    @param     $account    string
    */
    public function accountExists(string $account) {
        return $this->getAPI()->accountExists($account);
    }


    /*
    Adds money to the player
    @param     $money    int
    @param     $account    string
    */
    public function addMoney(int $money, string $account) {
        return $this->getAPI()->addMoney($account, $money);
    }


    /*
    Take money from an account
    @param     $money    int
    @param     $account    string
    */
    public function takeMoney(int $money, string $account) {
        return $this->getAPI()->reduceMoney($account, $money);
    }



    /*
    Set player's money.
    @param     $money    int
    @param     $account    string
    */
    public function setMoney(int $money, string $account) {
        return $this->getAPI()->setMoney($account, $money);
    }



    /*
    Get someone's money
    @param     $account    string
    */
    public function getMoney(string $account) {
        return $this->getAPI()->getMoney($account);
    }



    /*
    Register an account.
    @param     $account    string
    @param     $default    int|bool
    */
    public function register(string $account, $default = false) {
        $this->getAPI()->createAccount($account, $default);
    }




}