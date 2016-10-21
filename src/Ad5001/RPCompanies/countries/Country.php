<?php


namespace Ad5001\RPCompanies;



use pocketmine\Server;


use pocketmine\Player;


use pocketmine\level\FullChunk;



use Ad5001\RPCompanies\Main;







abstract class Country {


    protected $main;
    protected $name;

    // Political models
    const DEMOCRATIC = 0; // Election every 5 years (approximativly 1 real life month)
    const DICTATORSHIP = 1; // Designation every half mounth





   public function __construct(Main $main, string $name, string $class) {


        $this->main = $main;
        $this->name = $name;
        $this->db = new \SQLite3($main->getDataFolder() . "database.db");
        $this->db->exec("IF OBJECT_ID('countries', 'U') IS NULL 
BEGIN
CREATE TABLE countries {
    name STRING,
    chunks STRING,
    owner STRING,
    old_owner STRING,
    next_election INT,
    is_claimed BOOL
}
END
");
       $res = $this->db->query("SELECT * FROM countries WHERE name = $name");
       if (!($res->numColumns() && $res->columnType(0) != SQLITE3_NULL)) {
           $nextEl=(constant($class ."::MODEL") == self::DEMOCRATIC ? time() + (30*24*60*60) : time() + (15*24*60*60));
           $defaultOwner = Main::$instance->getConfig()->get("default_owner");
           $this->db->exec("INSERT INTO countries VALUES ('$name', '{}', '$defaultOwner', '{}', $nextEl, 0) ");
       }

    }


    public function getName() {
        return $this->name;
    }


    abstract function startElection();


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



    public function setOwner(Player $player) {
        return $this->db->exec("UPDATE countries SET owner = '{$player->getName()}'");
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
        $json = json_decode($json, true);
    }




}