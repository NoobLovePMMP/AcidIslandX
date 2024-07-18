<?php

namespace Noob\AcidIsland\listener;

use Noob\AcidIsland\AcidIsland as Island;
use pocketmine\block\BlockTypeIds;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\entity\EntityTrampleFarmlandEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\math\Vector3;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\Position;
use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\data\bedrock\EffectIds;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\block\Block;

class EventListener implements Listener{

    public string $prefix = "[Island] ";

    public function onDamage(EntityDamageByEntityEvent $ev) {
        $player = $ev->getDamager();
        $entity = $ev->getEntity();
        if ($player instanceof Player && $entity instanceof Player) {
            $worldName = explode("-", $player->getWorld()->getDisplayName());
            if(Island::getInstance()->isIsland($player)){
                $owner = $worldName[1];
                $status_pvp = Island::getInstance()->getIsland()->getNested($owner. ".Island-PvP");
                if($status_pvp == "Yes") return true;
                else{
                    $player->sendMessage($this->prefix . "You can't pvp in here");
                    $ev->cancel();
                }
            }
        }
    }

    public function onInteract(PlayerInteractEvent $ev){
        $player = $ev->getPlayer();
        if(Island::getInstance()->isIsland($player)){
            $worldName =  $player->getWorld()->getDisplayName();
            if(Island::getInstance()->isFriendInIsland($player, $worldName) || Island::getInstance()->isOwnerInIsland($player, $worldName)){
                return true;
            }
            else{
                $player->sendMessage($this->prefix . "You don't have permission to do!");
                $ev->cancel();
            }
        }
    }
    public function onPickup(EntityItemPickupEvent $ev){
        $player = $ev->getEntity();
        if($player instanceof Player){
            if(Island::getInstance()->isIsland($player)){
                $worldName =  $player->getWorld()->getDisplayName();
                if(Island::getInstance()->isFriendInIsland($player, $worldName) || Island::getInstance()->isOwnerInIsland($player, $worldName)){
                    return true;
                }
                else{
                    $ev->cancel();
                }
            }
        }
    }

    public function onMove(PlayerMoveEvent $ev){
        $player = $ev->getPlayer();
        if(Island::getInstance()->isIsland($player)){
            $worldName =  $player->getWorld()->getDisplayName();
            $world = Server::getInstance()->getWorldManager()->getWorldByName($worldName);
            $x = intval($player->getPosition()->getX());
            $y = intval($player->getPosition()->getY());
            $z = intval($player->getPosition()->getZ());
            $pos = new Position($x, $y, $z, $world);
            $block = $world->getBlock($pos);
            if($block->getTypeId() == BlockTypeIds::WATER){
				$player->getEffects()->add(new EffectInstance(EffectIdMap::getInstance()->fromId(EffectIds::POISON), 200, 1, true));
				$player->getEffects()->add(new EffectInstance(EffectIdMap::getInstance()->fromId(EffectIds::WITHER), 200, 1, true));
            }
            else{
                $player->getEffects()->clear();
            }
        }
    }

    public function onBreak(BlockBreakEvent $ev){
        $player = $ev->getPlayer();
        if(Island::getInstance()->isIsland($player)){
            $worldName =  $player->getWorld()->getDisplayName();
            if(Island::getInstance()->isFriendInIsland($player, $worldName) || Island::getInstance()->isOwnerInIsland($player, $worldName)){
                return true;
            }
            else{
                $player->sendMessage($this->prefix . "You don't have permission to do!");
                $ev->cancel();
            }
        }
    }

    public function onPlace(BlockPlaceEvent $ev){
        $player = $ev->getPlayer();
        $block = $ev->getItem();
        if(Island::getInstance()->isIsland($player)){
            $worldName =  $player->getWorld()->getDisplayName();
            $world = Server::getInstance()->getWorldManager()->getWorldByName($worldName);
            $ex = explode("-", $worldName);
            $owner = $ex[1];
            if(Island::getInstance()->isFriendInIsland($player, $worldName) || Island::getInstance()->isOwnerInIsland($player, $worldName)){
                $blockName = $block->getVanillaName();
                for($i = 0; $i < strlen($blockName); $i++){
                    if($blockName[$i] == ' '){
                        $blockName[$i] = '_';
                    }
                }
                $blockName = strtolower($blockName);
                $points = Island::getInstance()->getPointBlock();
                $player->sendMessage($blockName);
                foreach($points as $point => $value){
                    if($blockName == $point){
                        Island::getInstance()->getIsland()->setNested($owner. ".Point", Island::getInstance()->getIsland()->getNested($owner. ".Point") + $value);
                        Island::getInstance()->getIsland()->save();
                    }
                }
                
            }
            else{
                $player->sendMessage($this->prefix . "You don't have permission to do!");
                $ev->cancel();
            }
        }
    }
}