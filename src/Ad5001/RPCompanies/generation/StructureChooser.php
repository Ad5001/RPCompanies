<?php


namespace Ad5001\RPCompanies\generation;



use pocketmine\Server;


use pocketmine\Player;


use pocketmine\level\Level;


use pocketmine\block\Block;


use pocketmine\event\Listener;


use Ad5001\RPCompanies\Main;


use Ad5001\RPCompanies\country\Country;







class StructureChooser implements Listener  {




   public function __construct(Main $main, Level $level, $chunkX, $chunkZ, Country $country) {


        $this->main = $main;


        $this->server = $main->getServer();


        $this->level = $level;


        $this->chunk = $level->getChunk($chunkX, $chunkZ);


        $this->country = $country;


        $this->rand = rand(0, 200);


        $this->generate();


    }


    private function generate() {
        $class= get_class($this->country);
        if(isset(get_class_methods($class)["generate" . $this->rand])) {
            $baseX = rand(0, 15) + $this->chunk->x;
            $baseZ = rand(0, 15) + $this->chunk->z;
            $baseY = $this->getHighestWorkableBlock($baseX, $baseZ);
            $this->country->{"generate" . $this->rand}($baseX, $baseY, $baseZ);
        }
    }



    public function getHighestWorkableBlock($x, $z){
		for($y = 127; $y > 0; --$y){
			$b = $this->level->getBlockIdAt($x, $y, $z);
			if($b === Block::DIRT or $b === Block::GRASS){
				break;
			}elseif($b !== 0 and $b !== Block::SNOW_LAYER){
				return -1;
			}
		}

		return ++$y;
	}




}