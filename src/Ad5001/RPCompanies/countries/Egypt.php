<?php


namespace Ad5001\RPCompanies;



use pocketmine\Server;


use pocketmine\Player;


use pocketmine\level\generator\biome\Biome;



use Ad5001\RPCompanies\Main;







class Egypt extends Country {


    const BIOMEID = [Biome::DESERT];

    const MODEL = Country::DEMOCRATIC;


     /*
    Starts the Election after the ElectionCountdownTask
    */
    public function startElection() {
        $this->db->exec("UPDATE elections SET election_started = 1 WHERE name = '$this->name");
        $this->db->exec("UPDATE elections SET end_election = time() + 2*60*60*24 WHERE name = '$this->name");
        foreach($this->getCitizens() as $c) {
            $z = Server::getInstance()->getPlayer($c);
            if(!is_null($z)) {
                $z->sendMessage(Main::PREFIX . "§2Election started ! Choose your new prasident by using /choose <Player>. You have 2 (real life) days.");
            } else {
                // TODO, Add a way to notify the user (by any kind of mail for example).
            }
        }
    }


    /*
    Stops the Election
    */
    public function stopElection() {
        $query =  $this->db->query("SELECT votes FROM elections WHERE name = '$this->name'")->fetchArray();
		$query = $query[array_keys($query)[0]];
		if(is_array($query)) $query[array_keys($query)[0]];
		$jsonresult =  json_decode($query, true);
        $votes = [];
        foreach($jsonresult as $candidate) {
            if(!isset($votes[$candidate])) {
                $votes[$candidate] = 0;
            }
            $votes[$candidate]++;
        }
        arsort($votes);
        $winner = array_keys($votes)[0];
        $this->setOwner($player);
        $this->db->exec("UPDATE elections SET election_started = 0 WHERE name = '$this->name");
        foreach($this->getCitizens() as $c) {
            $z = Server::getInstance()->getPlayer($c);
            if(!is_null($z)) {
                $z->sendMessage(Main::PREFIX . "§2Election ended ! You new prasident is...");
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
            $msg = Country::translateDemocraticMSG($msgid / 60*60 . ' hours');
        }
        if($msgid < 60*60 && $msgid % (10*60)) {
            $msg = Country::translateDemocraticMSG($msgid / 10*60 . ' minutes');
        }
        if($msgid < 10*60 && $msgid % (60)) {
            $msg = Country::translateDemocraticMSG($msgid / 60 . ' minutes');
        }
        if($msgid < 60 && $msgid % (10)) {
            $msg = Country::translateDemocraticMSG($msgid / 10 . ' seconds');
        }
        if($msgid < 10) {
            $msg = Country::translateDemocraticMSG($msgid . ' seconds');
        }
        foreach($this->getCitizens() as $c) {
            $z = Server::getInstance()->getPlayer($c);
            if(!is_null($z)) {
                $z->sendMessage(Main::PREFIX . "§2Election started ! Choose your new prasident by using /vote <Player>. You have 2 (real life) days.");
            }
        }
    }


    /*
    Vote function
	@param     $from    Player
	@param     $to    Player
    */
    public function vote(Player $from, string $to) {
        $query =  $this->db->query("SELECT votes FROM elections WHERE name = '$this->name'")->fetchArray();
		$query = $query[array_keys($query)[0]];
		if(is_array($query)) $query[array_keys($query)[0]];
		$json =  json_decode($query, true);
		$json[$from->getName()] = $to->getName();
		$json = json_encode($json);
		$this->db->exec("UPDATE elections SET votes = '$json' WHERE name = '$this->name");
		return true;
    }



}