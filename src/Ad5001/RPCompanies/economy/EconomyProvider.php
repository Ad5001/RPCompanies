<?php


namespace Ad5001\RPCompanies;



use pocketmine\Server;


use pocketmine\Player;



use Ad5001\RPCompanies\Main;







abstract class EconomyProvider {




   public function __construct(Main $main, $api) {


        $this->main = $main;


        $this->server = $main->getServer();


        $this->api = $api;


    }




    /*
    Return server's instance.
    */
    public function getServer() {
        return $this->server;
    }


    /*
    Return the economy API instance
    */
    public function getAPI() {
        return $this->api;
    }


    /*
    Check if an account exists
    @param     $name    string
    */
    public abstract function accountExists(string $name);


    /*
    Adds money to the player
    @param     $money    int
    @param     $account    string
    */
    public abstract function addMoney(int $money, string $account);


    /*
    Take money from the player
    @param     $money    int
    @param     $account    string
    */
    public abstract function takeMoney(int $money, string $account);


    /*
    Sets player's money
    @param     $money    int
    @param     $account    string
    */
    public abstract function setMoney(int $money, string $account);


    /*
    Get someone's money
    @param     $account    string
    */
    public abstract function getMoney(string $account);

    /*
    Creates an account
    @param     $name    string
    @param     $default    int|bool
    */
    public abstract function register(string $name, $default);




}