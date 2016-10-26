<?php


namespace Ad5001\RPCompanies;



use pocketmine\Server;


use pocketmine\Player;


use pocketmine\level\generator\biome\Biome;



use Ad5001\RPCompanies\Main;







class Russia extends Country {


    const BIOMEID = [Biome::ICE_PLAINS, Biome::TAIGA];

    const MODEL = Country::DICTATORSHIP;

    const COMPANYTAX = 30;




     /*
    Starts the Election after the ElectionCountdownTask
    */
    public function startElection() {
        $this->db->exec("UPDATE elections SET election_started = 1 WHERE name = '$this->name");
        $this->db->exec("UPDATE elections SET end_election = time() + 2*60*60*24 WHERE name = '$this->name");
        $this->voted = $this->getCitizens()[rand(0, count($this->getCItizens()))]; // Random player in case no one is choosed
        foreach($this->getCitizens() as $c) {
            $z = Server::getInstance()->getPlayer($c);
            if(!is_null($z)) {
                $z->sendMessage(Main::PREFIX . "§2Election started ! Choose your new prasident by using /vote <Player>. You have 2 (real life) days.");
            } else {
                // TODO, Add a way to notify the user (by any kind of mail for example).
            }
        }
    }



    /*
    Selects the player that will be as the new dictator
    @param     $player    Player
    */
    public function select(string $player) {
        $this->voted = $player;
    }


    /*
    Stops the Election
    */
    public function stopElection() {
        $this->setOwner($this->voted);
        $this->db->exec("UPDATE elections SET election_started = 0 WHERE name = '$this->name");
        $this->db->exec("UPDATE elections SET next_election = time() + (15*24*60*60) WHERE name = '$this->name");
        foreach($this->getCitizens() as $c) {
            $z = Server::getInstance()->getPlayer($c);
            if(!is_null($z)) {
                $z->sendMessage(Main::PREFIX . "§2Your new dictator is $this->voted !");
            } else {
                // TODO, Add a way to notify the user (by any kind of mail for example).
            }
        }
    }


    /*
    Sends the election before starting message
    @param     $msgid    int
    */
    public function sendMessage(int $msgid) {
        if($msgid % (60*60) == 0) {
            $msg = Country::translateDictatorshipMSG($msgid / 60*60 . ' hours');
        }
        if($msgid < 60*60 && $msgid % (10*60) == 0) {
            $msg = Country::translateDictatorshipMSG()($msgid / 10*60 . ' minutes');
        }
        if($msgid < 10*60 && $msgid % (60) == 0) {
            $msg = Country::translateDictatorshipMSG($msgid / 60 . ' minutes');
        }
        if($msgid < 60 && $msgid % (10) == 0) {
            $msg = Country::translateDictatorshipMSG($msgid / 10 . ' seconds');
        }
        if($msgid < 10) {
            $msg = Country::translateDictatorshipMSG($msgid . ' seconds');
        }
        foreach($this->getCitizens() as $c) {
            $z = Server::getInstance()->getPlayer($c);
            if(!is_null($z)) {
                $z->sendMessage($msg);
            }
        }
    }
}