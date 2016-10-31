<?php


namespace Ad5001\RPCompanies;



use pocketmine\Server;
use pocketmine\item\Item;

use pocketmine\Player;



use Ad5001\RPCompanies\Main;







class CompanyInventory extends \pocketmine\inventory\BaseInventory {

    const MAXSTACK = 128;

	public function getMaxStackSize(){
		return self::MAXSTACK;
	}


	public function setContents(array $items){
		if(count($items) > $this->size){
			$items = array_slice($items, 0, $this->size, true);
		}

		for($i = 0; $i < $this->size; ++$i){
			if(!isset($items[$i])){
				if(isset($this->slots[$i])){
					$this->clear($i);
				}
			}else{
				if (!$this->setItem($i, $items[$i])){
					$this->clear($i);
				}
			}
		}
		$this->refresh();
	}

	public function setItem($index, Item $item){
		$item = clone $item;
		if($index < 0 or $index >= $this->size){
			return false;
		}elseif($item->getId() === 0 or $item->getCount() <= 0){
			return $this->clear($index);
		}

		$holder = $this->getHolder();
		if($holder instanceof Entity){
			Server::getInstance()->getPluginManager()->callEvent($ev = new EntityInventoryChangeEvent($holder, $this->getItem($index), $item, $index));
			if($ev->isCancelled()){
				$this->sendSlot($index, $this->getViewers());
				return false;
			}
			$item = $ev->getNewItem();
		}

		$old = $this->getItem($index);
		$this->slots[$index] = clone $item;
		$this->onSlotChange($index, $old);
		$this->refresh();

		return true;
	}



	public function addItem(...$slots){
		/** @var Item[] $itemSlots */
		/** @var Item[] $slots */
		$itemSlots = [];
		foreach($slots as $slot){
			if(!($slot instanceof Item)){
				throw new \InvalidArgumentException("Expected Item[], got ".gettype($slot));
			}
			if($slot->getId() !== 0 and $slot->getCount() > 0){
				$itemSlots[] = clone $slot;
			}
		}

		$emptySlots = [];

		for($i = 0; $i < $this->getSize(); ++$i){
			$item = $this->getItem($i);
			if($item->getId() === Item::AIR or $item->getCount() <= 0){
				$emptySlots[] = $i;
			}

			foreach($itemSlots as $index => $slot){
				if($slot->equals($item) and $item->getCount() < $item->getMaxStackSize()){
					$amount = min($item->getMaxStackSize() - $item->getCount(), $slot->getCount(), $this->getMaxStackSize());
					if($amount > 0){
						$slot->setCount($slot->getCount() - $amount);
						$item->setCount($item->getCount() + $amount);
						$this->setItem($i, $item);
						if($slot->getCount() <= 0){
							unset($itemSlots[$index]);
						}
					}
				}
			}

			if(count($itemSlots) === 0){
				break;
			}
		}

		if(count($itemSlots) > 0 and count($emptySlots) > 0){
			foreach($emptySlots as $slotIndex){
				//This loop only gets the first item, then goes to the next empty slot
				foreach($itemSlots as $index => $slot){
					$amount = min($slot->getMaxStackSize(), $slot->getCount(), $this->getMaxStackSize());
					$slot->setCount($slot->getCount() - $amount);
					$item = clone $slot;
					$item->setCount($amount);
					$this->setItem($slotIndex, $item);
					if($slot->getCount() <= 0){
						unset($itemSlots[$index]);
					}
					break;
				}
			}
		}
		$this->refresh();
		return $itemSlots;
	}

	public function removeItem(...$slots){
		/** @var Item[] $itemSlots */
		/** @var Item[] $slots */
		$itemSlots = [];
		foreach($slots as $slot){
			if(!($slot instanceof Item)){
				throw new \InvalidArgumentException("Expected Item[], got ".gettype($slot));
			}
			if($slot->getId() !== 0 and $slot->getCount() > 0){
				$itemSlots[] = clone $slot;
			}
		}

		for($i = 0; $i < $this->getSize(); ++$i){
			$item = $this->getItem($i);
			if($item->getId() === Item::AIR or $item->getCount() <= 0){
				continue;
			}

			foreach($itemSlots as $index => $slot){
				if($slot->equals($item, $slot->getDamage() === null ? false : true, $slot->getCompoundTag() === null ? false : true)){
					$amount = min($item->getCount(), $slot->getCount());
					$slot->setCount($slot->getCount() - $amount);
					$item->setCount($item->getCount() - $amount);
					$this->setItem($i, $item);
					if($slot->getCount() <= 0){
						unset($itemSlots[$index]);
					}
				}
			}

			if(count($itemSlots) === 0){
				break;
			}
		}
		$this->refresh();
		return $itemSlots;
	}

	public function clear($index){
		if(isset($this->slots[$index])){
			$item = Item::get(Item::AIR, null, 0);
			$old = $this->slots[$index];
			$holder = $this->getHolder();
			if($holder instanceof Entity){
				Server::getInstance()->getPluginManager()->callEvent($ev = new EntityInventoryChangeEvent($holder, $old, $item, $index));
				if($ev->isCancelled()){
					$this->sendSlot($index, $this->getViewers());
					return false;
				}
				$item = $ev->getNewItem();
			}
			if($item->getId() !== Item::AIR){
				$this->slots[$index] = clone $item;
			}else{
				unset($this->slots[$index]);
			}

			$this->onSlotChange($index, $old);
		}
		$this->refresh();
		return true;
	}



	/*
	Refreshs with the database
	*/
	public function refresh() {
		$items = [];
		foreach($this->slots as $slot => $item) {
			$items[$slot] = $item->getId() . ":" . $item->getDamage() . ":" . $item->getCount() . $item->hasCustomName() ? $item->getCustomName() : $item->getName();
		}
		$json = json_encode($items);
		return $this->getHolder()->db->exec("UPDATE companies SET inventory = '$json' WHERE name = '{$this->getHolder()->name}");
	}


	/*
	Loads the inventory from the database
	*/
	public function reload() {
		$this->slots = [];
		$query =  $this->getHolder()->db->query("SELECT inventory FROM companies WHERE name = '{$this->getHolder()->name}'")->fetchArray();
		$query = $query[array_keys($query)[0]];
		if(is_array($query)) $query[array_keys($query)[0]];
		$items =  json_decode($query, true);
		foreach($items as $slot => $item) {
			$parts = explode(":", $item);
			$this->slots[$slot] = Item::get($item[0], $item[1]);
			$this->slots[$slot]->setCount($item[2]);
			if($this->slots[$slot]->getName() !== $item[3]) $this->slots[$slot]->setCustomName($items[3]);
		}
		return $this;
	}

}