<?php

namespace Noob\AcidIsland;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\player\Player;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Server;
use Noob\AcidIsland\commands\AcidIslandCommand;
use Noob\AcidIsland\listener\EventListener;
use pocketmine\block\VanillaBlocks;
use czechpmdevs\multiworld\util\WorldUtils;
use pocketmine\world\WorldCreationOptions;
use pocketmine\world\Position;
use pocketmine\math\Vector3;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\generator\GeneratorManager;
use Noob\AcidIsland\generator\Worlds\WaterWorld;
use pocketmine\item\StringToItemParser;
use pocketmine\world\WorldManager;
use RecursiveDirectoryIterator;
use FilesystemIterator;
use RecursiveIteratorIterator;
use pocketmine\world\World;

class AcidIsland extends PluginBase {

    public $island;
    public $manager;
	public static $instance;

	public static function getInstance() : self {
		return self::$instance;
	}

	public function onEnable(): void{
        self::$instance = $this;
        GeneratorManager::getInstance()->addGenerator(WaterWorld::class, "waterworld", fn () => null, true);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        if($this->getIsland()->exists("island")){
            if($this->getIsland()->get("island") != ""){
                $islands = explode(", ", $this->getIsland()->get("island"));
                foreach($islands as $island){
                    if(!Server::getInstance()->getWorldManager()->isWorldLoaded($island)){
                        Server::getInstance()->getWorldManager()->loadWorld($island);
                    }
                }
                $this->getLogger()->notice("[AcidIsland] All Island Loaded");
            }
        }
        $this->getServer()->getCommandMap()->register("/acidisland", new AcidIslandCommand($this));
        $this->manager = new Config($this->getDataFolder() . "manager.yml", Config::YAML, [
            "item-starter" => [],
            "point-block" => []
        ]);
        
	}

    public function getIsland(){
        return $this->island ?? $this->island = new Config($this->getDataFolder() . "island.yml", Config::YAML, ["island" => ""]);;
    }

    public function getManager(){
        return $this->manager;
    }


    public function hasIsland(string $playerName): bool{
        if($this->getIsland()->exists($playerName)) return true;
        return false;
    }

    public function getItem(): array{
        $item = [];
        if($this->getManager()->get("item-starter") !== []){
            foreach($this->getManager()->get("item-starter") as $data => $value){
                $item[] = $value;
            }
        }
        return $item;
    }

    public function isFriend(Player $player, string $playerName): bool{
        if($this->getIsland()->getNested($player->getName(). ".Friends") == "") return false;
        $friends = explode(", ", $this->getIsland()->getNested($player->getName(). ".Friends"));
        foreach($friends as $friend){
            if($friend == $playerName) return true;
        }
        return false;
    }

    public function addFriend(Player $player, string $playerName){
        $friend = $this->getIsland()->getNested($player->getName(). ".Friends");
        if($friend == ""){
            $friend = $playerName;
        }
        else{
            $friend .= ", ";
            $friend .= $playerName;
        }
        $this->getIsland()->setNested($player->getName(). ".Friends", $friend);
        $this->getIsland()->save();
    }

    public function removeFriend(Player $player, string $playerName){
        $friends = $this->getIsland()->getNested($player->getName(). ".Friends");
        $ex = explode(", ", $friends);
        $this->getIsland()->setNested($player->getName().".Friends", "");
        $this->getIsland()->save();
        foreach($ex as $friend){
            if($friend != $playerName){
                $this->addFriend($player, $friend);
            }
        }

    }

    public function addIsland(string $worldName){
        if($this->getIsland()->get("island") == ""){
            $this->getIsland()->set("island", $worldName);
            $this->getIsland()->save();
        }
        else{
            $is_player = $this->getIsland()->get("island");
            $is_player .= ", ";
            $is_player .= $worldName;
            $this->getIsland()->set("island", $is_player);
            $this->getIsland()->save();
        }
    }

    public function removeIsland(string $worldName){
        $list = $this->getIsland()->get("island");
        $this->getIsland()->set("island", "");
        $this->getIsland()->save();
        $ex = explode(", ", $list);
        foreach($ex as $data){
            if($data != $worldName){
                $this->addIsland($data);
            }
        }
    }

    public function removeIslandPlayer(Player $player) {
		if (Server::getInstance()->getWorldManager()->isWorldLoaded("is-" . $player->getName())) {
			$world = Server::getInstance()->getWorldManager()->getWorldByName("is-" . $player->getName());
			if (count($world->getPlayers()) > 0) {
				foreach ($world->getPlayers() as $players) {
					$players->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
				}
			}
			Server::getInstance()->getWorldManager()->unloadWorld($world);
		}
		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($worldPath = Server::getInstance()->getDataPath() . "/worlds/is-" . $player->getName(), FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
		foreach ($files as $fileInfo) {
			if ($filePath = $fileInfo->getRealPath()) {
				if ($fileInfo->isFile()) {
					unlink($filePath);
				} else {
					rmdir($filePath);
				}
			}
		}
		rmdir($worldPath);
	}

    public function createIsland(Player $player){
        $worldName = "is-". $player->getName();
        $this->getIsland()->set($player->getName(), [
            "World-Name" => $worldName,
            "Friends" => "",
            "Position" => "5, 12, 5",
            "Point" => 0,
            "Island-Lock" => "No",
            "Island-PvP" => "Yes"
        ]);
        $this->getIsland()->save();
        $this->addIsland($worldName);
        if(!$this->getServer()->getWorldManager()->isWorldGenerated($worldName)){
            $generator = GeneratorManager::getInstance()->getGenerator("waterworld");
            $this->getServer()->getWorldManager()->generateWorld(
            $worldName, 
            WorldCreationOptions::create()
            ->setSeed(0)
            ->setGeneratorClass($generator->getGeneratorClass())
            ->setSpawnPosition(new Vector3(256, 66, 256))
            );
            $itemList = $this->getItem();

            $this->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player, $itemList, $worldName) : void {
                if(Server::getInstance()->getWorldManager()->isWorldLoaded($worldName)){
                    $this->teleportToIsland($player);
                    foreach($itemList as $item){
                        $ex = explode(":", $item);
                        $item_starter = StringToItemParser::getInstance()->parse($ex[0])->setCount((int)$ex[1]);
                        if($ex[2] != "Default"){
                            $item_starter->setCustomName($ex[2]);
                        }
                        $player->getInventory()->addItem($item_starter);
                    }
                }
                else{
                    Server::getInstance()->getWorldManager()->loadWorld($worldName, true);
                    if(Server::getInstance()->getWorldManager()->isWorldLoaded($worldName)){
                        $this->teleportToIsland($player);
                        foreach($itemList as $item){
                            $ex = explode(":", $item);
                            $item_starter = StringToItemParser::getInstance()->parse($ex[0])->setCount((int)$ex[1]);
                            if($ex[2] != "Default"){
                                $item_starter->setCustomName($ex[2]);
                            }
                            $player->getInventory()->addItem($item_starter);
                        }
                    }
                }
                
            }), 20 * 10);
        }
    }

    public function teleportToIsland(Player $player){
        $world = $this->getServer()->getWorldManager()->getWorldByName($this->getIsland()->getNested($player->getName(). ".World-Name"));
        $ex = explode(", ", $this->getIsland()->getNested($player->getName(). ".Position"));
        $x = (int)$ex[0];
        $y = (int)$ex[1];
        $z = (int)$ex[2];
        Server::getInstance()->getWorldManager()->loadWorld($this->getIsland()->getNested($player->getName(). ".World-Name"), true);
        $this->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player, $x, $y, $z, $world) : void {
            $player->teleport(new Position($x, $y, $z, $world));
        }), 20 * 2);
    }

    public function isIslandLocked(string $islandName): bool{
        $ex = explode("-", $islandName);
        $name = $ex[1];
        if($this->getIsland()->getNested($name. ".Island-Lock") == "No") return false;
        return true;
    }

    public function visitIsland(Player $player, string $ownerName){
        $world = $this->getServer()->getWorldManager()->getWorldByName($this->getIsland()->getNested($ownerName. ".World-Name"));
        $ex = explode(", ", $this->getIsland()->getNested($ownerName. ".Position"));
        $x = (int)$ex[0];
        $y = (int)$ex[1];
        $z = (int)$ex[2];
        Server::getInstance()->getWorldManager()->loadWorld($this->getIsland()->getNested($ownerName. ".World-Name"), true);
        if($world == null){
            $player->sendMessage("World Is Null");
            return 0;
        }
        $this->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player, $x, $y, $z, $world) : void {
            $player->teleport(new Position($x, $y, $z, $world));
        }), 20 * 2);
    }

    public function setLockIsland(Player $player, bool $status){
        if($status == true){
            $this->getIsland()->setNested($player->getName(). ".Island-Lock", "Yes");
            $this->getIsland()->save();
        }
        else{
            $this->getIsland()->setNested($player->getName(). ".Island-Lock", "No");
            $this->getIsland()->save();
        }
    }
    

    public function setPvPIsland(Player $player, bool $status){
        if($status == true){
            $this->getIsland()->setNested($player->getName(). ".Island-PvP", "Yes");
            $this->getIsland()->save();
        }
        else{
            $this->getIsland()->setNested($player->getName(). ".Island-PvP", "No");
            $this->getIsland()->save();
        }
    }
    
    public function getPlayerInIsland(Player $player): array{
        $player_list = [];
        $worldName = $this->getIsland()->getNested($player->getName(). ".World-Name");
        foreach(Server::getInstance()->getOnlinePlayers() as $playerName){
            $iplayer = Server::getInstance()->getPlayerByPrefix($playerName);
            if($iplayer != null){
                $worldPlayer = $iplayer->getWorld()->getDisplayName();
                if($worldPlayer == $worldName){
                    $player_list[] = $iplayer->getName();
                }
            }
        }
        return $player_list;
    }

    public function getPointPlayer(): array{
        $points = [];
        foreach($this->getIsland()->getAll() as $playerName => $value){
            if($playerName != "island") $points[$playerName] = $this->getIsland()->getNested($playerName . ".Point");
        }
        return $points;
    }

    public function teleportToSpawn(Player $player){
        $pos = $this->getIsland()->get("lobby");
        $ex = explode(":", $pos);
        $world = Server::getInstance()->getWorldManager()->getWorldByName($ex[3]);
        $player->teleport(new Position((int)$ex[0], (int)$ex[1], (int)$ex[2], $world));
    }

    public function isIsland(Player $player): bool{
        $world = $player->getWorld()->getFolderName();
        $ex = explode("-", $world);
        if($ex[0] == "is") return true;
        return false;
    }

    public function isFriendInIsland(Player $player, string $worldName): bool{
        $ex = explode("-", $worldName);
        $friends = $this->getIsland()->getNested($ex[1]. ".Friends");
        $a = explode(", ", $friends);
        foreach($a as $friend){
            if($friend == $player->getName()){
                return true;
            }
        }
        return false;
    }

    public function isOwnerInIsland(Player $player, string $worldName): bool{
        $ex = explode("-", $worldName);
        if($player->getName() == $ex[1]) return true;
        return false;
    }

    public function getPointBlock(): array{
        $points = [];
        if($this->getManager()->get("point-block") != []){
            foreach($this->getManager()->get("point-block") as $data => $value){
                $ex = explode(":", $value);
                if(!in_array($ex[0], $points)){
                    $points[$ex[0]] = (int)$ex[1];
                }
            }
        }
        return $points;
    }
}