<?php


namespace Ad5001\RPCompanies;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\level\format\FullChunk;
use pockemtine\item\Item;

use Ad5001\RPCompanies\Main;
use Ad5001\RPCompanies\country\CountryManager;
use Ad5001\RPCompanies\country\Country;







class Company implements \Ad5001\RPCompanies\sellable\Buyer {


    // Company kinds
    const TYPES = ["MINING" => 0, "FARMING" => 1, "TOOLS" => 2, "BUILDS" => 3];




   public function __construct(string $name) {
        $this->name = $name;
        Main::$instance->getEconomyProvider()->register("§eCompany_$name");
		$this->db = new \SQLite3($main->getDataFolder() . "countries.db");
        $items  = $this->db->query("SELECT current_gain FROM companies WHERE name = $this->name")->fetchArray();
		$itemsjson = $items[array_keys($items)[0]];
		if(is_array($items)) {
			$itemsjson = $itemsjson[array_keys($itemsjson)[0]];
		}
        $items = [];
        foreach(json_decode($itemsjson) as $item) {
            $parts = explode(":", $item);
            $item = Item::get($parts[0], $parts[1]);
            $item->setCount($parts[2]);
            $items[] = $item;
        }
        $this->inventory = new CompanyInventory($this, new InventoryType(100, $this->name), $items);
        $this->inventory->reload(); // Basicly loads from the database
    }


    /*
    Create a company
    @param     $name    string
    @param     $owner   Player
    @param     $kind   Int
    */
    public static function createCompany(string $name, Player $owner, int $kind) {
		$main->getEconomyProvider()->register("§aCompany_$name", Main::$instance->getConfig()->get("DefaultCompanyPrice"));
		$this->db = new \SQLite3($main->getDataFolder() . "companies.db");
		$res = $this->db->query("SELECT * FROM companies WHERE name = $name");
		if (!($res->numColumns() && $res->columnType(0) != SQLITE3_NULL)) {
            $c = CountryManager::getCountryOfPlayer($owner)->getName();
            $price = Main::$instance->getConfig()->get("DefaultCompanyPrice") / 10;
			$this->db->exec("INSERT INTO companies VALUES ('$name', '{$owner->getName()}', '$c', 0, $kind, '{\"{$owner->getName()}\":\"$price}\"}', '{}', 1, '{}', '{}') ");
		}
        $c = new Company($name);
        CompanyManager::register($c);
        return $c;
    }



    /*
    Returns company's name
    */
    public function getName() : string {
        return $this->name;
    }


    /*
    Returns the owner's name
    */
    public function getOwner() : string {
        $ownerarray  = $this->db->query("SELECT owner FROM companies WHERE name = $this->name")->fetchArray();
		$ownerarray = $ownerarray[array_keys($ownerarray)[0]];
		if(is_array($ownerarray)) {
			$ownerarray = $ownerarray[array_keys($ownerarray)[0]];
		}
		return $ownerarray;
    }


    /*
    Sets the owner
    @param     $owner    Player|string
    */
    public function setOwner($owner) {
		if($player instanceof Player) {
			$player = $player->getName();
		}
		return $this->db->exec("UPDATE companies SET owner = '{$player}'");
    }


    /*
    Return company's country (for taxs and infos)    
    */
    public function getBasedCountry() : Country {
        $ownerarray  = $this->db->query("SELECT based_country FROM companies WHERE name = $this->name")->fetchArray();
		$ownerarray = $ownerarray[array_keys($ownerarray)[0]];
		if(is_array($ownerarray)) {
			$ownerarray = $ownerarray[array_keys($ownerarray)[0]];
		}
		return CountryManager::getCountries()[$ownerarray];
    }

    
    /*
    Sets based country
    @param     $country    Country
    */
    public function setBasedCountry(Country $country) {
        return $this->db->exec("UPDATE companies SET based_country = '{$country->getName()}'");
    }


    /*
    Gets the current gain.
    */
    public function getCurrentGain() : int {
        $ownerarray  = $this->db->query("SELECT current_gain FROM companies WHERE name = $this->name")->fetchArray();
		$ownerarray = $ownerarray[array_keys($ownerarray)[0]];
		if(is_array($ownerarray)) {
			$ownerarray = $ownerarray[array_keys($ownerarray)[0]];
		}
		return $ownerarray;
    }


    /*
    Resets current gain. It will not remove money from company, just restarting a new tax session for taxs.
    @param        
    */
    public function resetCurrentGain() {
        return $this->db->exec("UPDATE companies SET current_gain = 0");
    }


    /*
    Adds to current gain.
    @param     $money    int
    */
    public function addToCurrentGain(int $money) {
        $ownerarray  = $this->db->query("SELECT current_gain FROM companies WHERE name = $this->name")->fetchArray();
		$ownerarray = $ownerarray[array_keys($ownerarray)[0]];
		if(is_array($ownerarray)) {
			$ownerarray = $ownerarray[array_keys($ownerarray)[0]];
		}
        return $this->db->exec("UPDATE companies SET current_gain = $ownerarray + $money");
    }

    /*
    Return an array of all employes names
    */
    public function getEmployes() : array {
        $query =  $this->db->query("SELECT employes_salary FROM companies WHERE name = '$this->name'")->fetchArray();
		$query = $query[array_keys($query)[0]];
		if(is_array($query)) $query[array_keys($query)[0]];
		return  json_decode($query, true);
    }


    /*
    Add an employe
    @param     $employe    Player|string
    @param     $salary        int
    */
    public function engageEmploye($employe, int $salary) {
        if($employe instanceof Player) $employe = $employe->getName();
		$query =  $this->db->query("SELECT pending_requests FROM companies WHERE name = '$this->name'")->fetchArray();
		$query = $query[array_keys($query)[0]];
		if(is_array($query)) $query[array_keys($query)[0]];
		$json =  json_decode($query, true);
        if(isset($json[$employe])) unset($json_employe);
		$json = json_encode($json);
		$this->db->exec("UPDATE companies SET pending_requests = '$json' WHERE name = '$this->name");
		$query =  $this->db->query("SELECT employes_salary FROM companies WHERE name = '$this->name'")->fetchArray();
		$query = $query[array_keys($query)[0]];
		if(is_array($query)) $query[array_keys($query)[0]];
		$json =  json_decode($query, true);
		$json[$employe] = $salary;
		$json = json_encode($json);
		return $this->db->exec("UPDATE companies SET employes_salary = '$json' WHERE name = '$this->name");
    }



    /*
    Removes an employe
    @param     $employe   Player|string
    */
    public function fireEmploye($employe) {
        if($employe instanceof Player) $employe = $employe->getName();
		$query =  $this->db->query("SELECT employes_salary FROM companies WHERE name = '$this->name'")->fetchArray();
		$query = $query[array_keys($query)[0]];
		if(is_array($query)) $query[array_keys($query)[0]];
		$json =  json_decode($query, true);
		if(!isset($json[$employe])) return false;
        Main::$instance->getEconomyProvider()->addMoney($json[$employe->getName()], $employe->getName());
        Main::$instance->getEconomyProvider()->takeMoney($json[$employe->getName()], "§eCompany_$name");
		unset($json[$employe]);
		$json = json_encode($json);
		$this->db->exec("UPDATE countries SET citizens = '$json' WHERE name = '$this->name");
    }


    /*
    Get the kind of a company (what's the company doing)
    */
    public function getKind() : int {
        $ownerarray  = $this->db->query("SELECT kind FROM companies WHERE name = $this->name")->fetchArray();
		$ownerarray = $ownerarray[array_keys($ownerarray)[0]];
		if(is_array($ownerarray)) {
			$ownerarray = $ownerarray[array_keys($ownerarray)[0]];
		}
		return $ownerarray;
    }


    /*
    Sets company kind
    @param     $kind    int
    */
    public function setKind(int $kind) {
        return $this->db->exec("UPDATE companies SET kind = $kind");
    }


    /*
    Get lands bought by the company where they can author
    @param        
    */
    public function getLands() :array {
        $query =  $this->db->query("SELECT lands FROM companies WHERE name = '$this->name'")->fetchArray();
		$query = $query[array_keys($query)[0]];
		if(is_array($query)) $query[array_keys($query)[0]];
		return  json_decode($query, true);
    }


    /*
    Adds a land to the country
    @param     $land    FullChunk
    */
    public function addLand(FullChunk $land) {
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


    /*
    Removes a land from a country
    @param     $land    FullChunk
    */
    public function removeLand(FullChunk $land) {
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


    /*
    Get if is accepting requests
    @param        
    */
    public function isAcceptingRequests() {
        $ownerarray  = $this->db->query("SELECT accepts_requests FROM companies WHERE name = $this->name")->fetchArray();
		$ownerarray = $ownerarray[array_keys($ownerarray)[0]];
		if(is_array($ownerarray)) {
			$ownerarray = $ownerarray[array_keys($ownerarray)[0]];
		}
		return $ownerarray;
    }


    /*
    Set the requests acceptations
    @param     $accept    bool
    */
    public function setAcceptingRequests(bool $accept) {
        return $this->db->exec("UPDATE companies SET accepts_requets = $accept");
    }


     /*
    Return an array of all requests
    */
    public function getPendingRequests() : array {
        $query =  $this->db->query("SELECT pending_requests FROM companies WHERE name = '$this->name'")->fetchArray();
		$query = $query[array_keys($query)[0]];
		if(is_array($query)) $query[array_keys($query)[0]];
		return  json_decode($query, true);
    }



    /*
    Removes an employe
    @param     $employe   Player|string
    */
    public function refuseRequest($employe) {
        if($employe instanceof Player) $employe = $employe->getName();
		$query =  $this->db->query("SELECT pending_requests FROM companies WHERE name = '$this->name'")->fetchArray();
		$query = $query[array_keys($query)[0]];
		if(is_array($query)) $query[array_keys($query)[0]];
		$json =  json_decode($query, true);
		if(!isset($json[$employe])) return false;
		unset($json[$employe]);
		$json = json_encode($json);
		$this->db->exec("UPDATE companies SET pending_requets = '$json' WHERE name = '$this->name");
    }


    /*
    Deletes the country.
    */
    public function delete() {
        $this->db->exec("DELETE FROM companies WHERE name = '$this->name");
    }




    /*
    Add money to the company
    @param     $money    int
    */
    public function addMoney(int $money) {
        $this->addCurrentGain($money);
        return Main::$instance->getEconomyProvider()->addMoney($money, "§aCompany_$this->name");
    }


    /*
    Take money from this company
    @param     $money    int
    */
    public function takeMoney(int $money) {
        $this->addCurrentGain(-$money);
        return Main::$instance->getEconomyProvider()->takeMoney($money, "§aCompany_$this->name");
    }



    /*
    Set company's money.
    @param     $money    int
    */
    public function setMoney(int $money) {
        return Main::$instance->getEconomyProvider()->setMoney($money, "§aCompany_$this->name");
    }



    /*
    Get company's money
    */
    public function getMoney() {
        return Main::$instance->getEconomyProvider()->getMoney("§aCompany_$this->name");
    }



    /*
    Return inventory of company instance.
    */
    public function getInventory() {
        return $this->inventory;
    }


    /*
    Stringify the company
    */
    public function __toString() {
        return "Company(" . $this->name . ")";
    }


    /*
    Return company from string
    @param     $str    string
    */
    public static function __fromString(string $str) {
       if(substr($str, 0, strlen("Company(") - 1) == "Company(") {
           $str = substr($str, strlen("Company(") - 1, strlen($str) - 1);
           return new Company($str);
       }
    }



}