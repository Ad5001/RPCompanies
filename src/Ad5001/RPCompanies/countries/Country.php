<?php


namespace Ad5001\RPCompanies;



use pocketmine\Server;


use pocketmine\Player;


use pocketmine\level\FullChunk;



use Ad5001\RPCompanies\Main;







abstract class Country {
	
	
	protected $main;
	protected $name;
	
	// 	Political models
	    const DEMOCRATIC = 0;
	// 	Election every 5 years (approximativly 1 real life month)
	    const DICTATORSHIP = 1;
	// 	Designation every half mounth
	
	
	
	
	
	public function __construct(Main $main, string $name, string $class) {
		
		
		$this->main = $main;
		$this->name = $name;
		$main->getEconomyProvider()->register("§aCountry_$name", 10000);
		$res = $this->db->query("SELECT * FROM countries WHERE name = $name");
		if (!($res->numColumns() && $res->columnType(0) != SQLITE3_NULL)) {
			$defaultOwner = '';
			$this->db->exec("INSERT INTO countries VALUES ('$name', '{}', '$defaultOwner', '{}', 0, '{}', '{}') ");
		}
		$res = $this->db->query("SELECT * FROM elections WHERE name = $name");
		if (!($res->numColumns() && $res->columnType(0) != SQLITE3_NULL)) {
			$nextEl = (constant($class ."::MODEL") == self::DEMOCRATIC ? time() + (30*24*60*60) : time() + (15*24*60*60));
			$defaultOwner = '';
			$model = constant($class . "::MODEL");
			$this->db->exec("INSERT INTO elections VALUES ('$name', '$model', '$defaultOwner', '$nextEl', 0, 0, '{}', '{}') ");
		}
		
	}
	
	
	public function getName() {
		return $this->name;
	}
	
	
	public function getNextElectionTime() {
		$query =  $this->db->query("SELECT next_election FROM elections WHERE name = '$this->name'")->fetchArray();
		$query = $query[array_keys($query)[0]];
		if(is_array($query)) $query[array_keys($query)[0]];
		return $query;
	}
	
	
	public function getEndElectionTime() {
		$query =  $this->db->query("SELECT end_election FROM elections WHERE name = '$this->name'")->fetchArray();
		$query = $query[array_keys($query)[0]];
		if(is_array($query)) $query[array_keys($query)[0]];
		return $query;
	}
	
	
	public function setNextElectionTime(int $time) {
		return $this->db->exec("UPDATE election SET next_election = $time WHERE name = '$this->name'");
	}
	
	
	public function setEndElectionTime(int $time) {
		return $this->db->exec("UPDATE election SET end_election = $time WHERE name = '$this->name'");
	}
	
	
	public abstract function startElection();
	
	
	public abstract function stopElection();
	
	
	public abstract function sendMessage(int $timeleft);
	
	
	public function addChunk(FullChunk $chunk) {
		$chunks  = $this->db->query("SELECT chunks FROM countries WHERE name = $this->name");
		$chunks = $chunks->fetchArray();
		$json = $chunks[array_keys($chunks)[0]];
		if(is_array($json)) {
			$json = $json[array_keys($json)[0]];
		}
		$json = json_decode($json, true);
		$json[$chunk->getX() . "@" . $chunk->getY()] = ["x" => $chunk->getX(), $chunk->getY()];
		$json = json_encode($json);
		return $this->db->exec("UPDATE contries SET chunks = '$json' WHERE name = $this->name");
	}
	
	
	public function removeChunk(FullChunk $chunk) {
		$chunks  = $this->db->query("SELECT chunks FROM countries WHERE name = $this->name");
		$chunks = $chunks->fetchArray();
		$json = $chunks[array_keys($chunks)[0]];
		if(is_array($json)) {
			$json = $json[array_keys($json)[0]];
		}
		$json = json_decode($json, true);
		if(!isset($json[$chunk->getX() . "@" . $chunk->getY()])) return false;
		unset($json[$chunk->getX() . "@" . $chunk->getY()]);
		$json = json_encode($json);
		return $this->db->exec("UPDATE contries SET chunks = '$json' WHERE name = $this->name");
	}
	
	
	public function save() {
		$this->db->close();
	}
	
	
	
	public function setOwner($player) {
		if($player instanceof Player) {
			$player = $player->getName();
		}
		return $this->db->exec("UPDATE countries SET owner = '{$player}'");
	}
	
	
	public function getOwner() {
		$ownerarray  = $this->db->query("SELECT owner FROM countries WHERE name = $this->name")->fetchArray();
		$ownerarray = $ownerarray[array_keys($ownerarray)[0]];
		if(is_array($ownerarray)) {
			$ownerarray = $ownerarray[array_keys($ownerarray)[0]];
		}
		return $ownerarray;
	}
	
	
	public function getOldOwners() {
		$chunks  = $this->db->query("SELECT old_owners FROM countries WHERE name = $this->name");
		$chunks = $chunks->fetchArray();
		$json = $chunks[array_keys($chunks)[0]];
		if(is_array($json)) {
			$json = $json[array_keys($json)[0]];
		}
		return json_decode($json, true);
	}
	
	
	
	public function addOldOwner($player) {
		if($player instanceof Player) {
			$player = $player->getName();
		}
		$chunks  = $this->db->query("SELECT old_owners FROM countries WHERE name = $this->name");
		$chunks = $chunks->fetchArray();
		$json = $chunks[array_keys($chunks)[0]];
		if(is_array($json)) {
			$json = $json[array_keys($json)[0]];
		}
		$json = json_decode($json, true);
		$json[] = $player;
		$json = json_encode($json);
		return $this->db->exec("UPDATE contries SET old_owners = '$json' WHERE name = $this->name");
	}
	
	
	public function getChunks() {
		$chunks  = $this->db->query("SELECT chunks FROM countries WHERE name = $this->name");
		$chunks = $chunks->fetchArray();
		$json = $chunks[array_keys($chunks)[0]];
		if(is_array($json)) {
			$json = $json[array_keys($json)[0]];
		}
		return json_decode($json, true);
	}
	
	
	public function onCitizenEnter(Player $citizen) {
		$query =  $this->db->query("SELECT citizens FROM countries WHERE name = '$this->name'")->fetchArray();
		$query = $query[array_keys($query)[0]];
		if(is_array($query)) $query[array_keys($query)[0]];
		$json =  json_decode($query, true);
		$json[] = $citizen->getName();
		$json = json_encode($json);
		$this->db->exec("UPDATE countries SET citizens = '$json' WHERE name = '$this->name");
		return true;
	}
	
	
	public function onCitizenLeave(Player $citizen) {
		$query =  $this->db->query("SELECT citizens FROM countries WHERE name = '$this->name'")->fetchArray();
		$query = $query[array_keys($query)[0]];
		if(is_array($query)) $query[array_keys($query)[0]];
		$json =  json_decode($query, true);
		if(!in_array($citizen->getName(), $json)) return true;
		unset($json[$citizen->getName()]);
		$json = json_encode($json);
		$this->db->exec("UPDATE countries SET citizens = '$json' WHERE name = '$this->name");
		return true;
	}



    public function getCitizens() {
        $query =  $this->db->query("SELECT citizens FROM countries WHERE name = '$this->name'")->fetchArray();
		$query = $query[array_keys($query)[0]];
		if(is_array($query)) $query[array_keys($query)[0]];
		return  json_decode($query, true);
    }



	/*
	Adds a tourist to the Country
	@param     $tourist    \pocketmine\Player
	*/
	public function addTourist(\pocketmine\Player $tourist) {
		$query =  $this->db->query("SELECT tourists FROM countries WHERE name = '$this->name'")->fetchArray();
		$query = $query[array_keys($query)[0]];
		if(is_array($query)) $query[array_keys($query)[0]];
		$json =  json_decode($query, true);
		$json[] = $tourist->getName();
		$json = json_encode($json);
		$this->db->exec("UPDATE countries SET tourists = '$json' WHERE name = '$this->name");
		return true;
	}



	/*
	Remove tourists from a Country
	@param     $tourist    \pocketmine\Player
	*/
	public function removeTourist(\pocketmine\Player $tourist) {
		$query =  $this->db->query("SELECT tourists FROM countries WHERE name = '$this->name'")->fetchArray();
		$query = $query[array_keys($query)[0]];
		if(is_array($query)) $query[array_keys($query)[0]];
		$json =  json_decode($query, true);
		if(!in_array($tourist->getName(), $json)) return true;
		unset($json[$tourist->getName()]);
		$json = json_encode($json);
		$this->db->exec("UPDATE countries SET tourists = '$json' WHERE name = '$this->name");
		return true;
	}



	/*
	Get an array of tourists.
	*/
	public function getTourists() {
        $query =  $this->db->query("SELECT tourists FROM countries WHERE name = '$this->name'")->fetchArray();
		$query = $query[array_keys($query)[0]];
		if(is_array($query)) $query[array_keys($query)[0]];
		return  json_decode($query, true);
	}
	
	
	/*
	Check if the election started
	*/
	public function isElectionStarted() {
		$ownerarray  = $this->db->query("SELECT election_started FROM election WHERE name = $this->name")->fetchArray();
		$ownerarray = $ownerarray[array_keys($ownerarray)[0]];
		if(is_array($ownerarray)) {
			$ownerarray = $ownerarray[array_keys($ownerarray)[0]];
		}
		return $ownerarray;
	}


	/*
	Vote function. Only for Democratik countries
	@param     $from    Player
	@param     $to    Player
	*/
	public function vote(Player $from, string $to) {}


	/*
	Vote function. Only for Democratik countries
	@param     $from    Player
	@param     $to    Player
	*/
	public function select(string $player) {}


	/*
	Translate a countdown message for democratik
	@param     $msg    string
	*/
	public static function translateDemocraricMSG(string $msg) {
		return Main::PREFIX . "§2Elections starts in $msg ! Be prepared !";
	}


	/*
	Translate a countdown message for dictatorships
	@param     $msg    string
	*/
	public static function translateDictatorshipMSG(string $msg) {
		return Main::PREFIX . "§2{$this->getOwner()} will choose it's successor in $msg !";
	}
	
	
}
