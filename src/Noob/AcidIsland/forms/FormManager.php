<?php

namespace Noob\AcidIsland\forms;

/*
███╗   ██╗██╗  ██╗██╗   ██╗████████╗    ██████╗ ███████╗██╗   ██╗
████╗  ██║██║  ██║██║   ██║╚══██╔══╝    ██╔══██╗██╔════╝██║   ██║
██╔██╗ ██║███████║██║   ██║   ██║       ██║  ██║█████╗  ██║   ██║
██║╚██╗██║██╔══██║██║   ██║   ██║       ██║  ██║██╔══╝  ╚██╗ ██╔╝
██║ ╚████║██║  ██║╚██████╔╝   ██║       ██████╔╝███████╗ ╚████╔╝ 
╚═╝  ╚═══╝╚═╝  ╚═╝ ╚═════╝    ╚═╝       ╚═════╝ ╚══════╝  ╚═══╝  
        Copyright © 2024 - 2025 NoobMCGaming
*/   

use Noob\AcidIsland\libs\jojoe77777\FormAPI\ModalForm;
use pocketmine\{Server, player\Player};
use Noob\AcidIsland\AcidIsland as Island;
use Noob\AcidIsland\libs\jojoe77777\FormAPI\CustomForm;
use Noob\AcidIsland\libs\jojoe77777\FormAPI\SimpleForm;
use pocketmine\world\Position;
use pocketmine\math\Vector3;
use pocketmine\scheduler\ClosureTask;
use czechpmdevs\multiworld\util\WorldUtils;
class FormManager{

    public string $prefix = "[Island] ";

    public function createMenu(Player $player){
        $form = new SimpleForm(function(Player $player, $data){
            if($data === null){
                return true;
            }
            if($data == 0){
                $player->sendMessage($this->prefix . "Island is creating..");
                Island::getInstance()->createIsland($player);
            }
        });
        $form->setTitle("Create Island");
        $form->setContent("Oops, You don't have island, do you want to create ?");
        $form->addButton("Create Island");
        $form->addButton("Not now");
        $form->sendToPlayer($player);
    }

    public function islandMenu(Player $player){
        $form = new SimpleForm(function(Player $player, $data){
            if($data === null){
                return true;
            }
            switch($data){
                case 0:
                    break;
                case 1:
                    $worldName = "Island-". $player->getName();
                    Island::getInstance()->teleportToIsland($player, Island::getInstance()->getIsland()->getNested($player->getName(). ".World-Name"));
                    break;
                case 2:
                    $this->friendMenu($player);
                    break;
                case 3:
                    $this->visitMenu($player);
                    break;
                case 4:
                    $this->settingMenu($player);
                    break;
                case 5:
                    $this->topMenu($player);
                    break;
                case 6:
                    $this->deleteQuestion($player);
                    break;
            }
        });
        $form->setTitle("Island Menu");
        $form->addButton("Close");
        $form->addButton("Join Island", 1, "https://cdn-icons-png.flaticon.com/128/2544/2544087.png");
        $form->addButton("Add/Remove Friends", 1, "https://cdn-icons-png.flaticon.com/128/4951/4951356.png");
        $form->addButton("Visit Island", 1, "https://cdn-icons-png.flaticon.com/128/921/921490.png");
        $form->addButton("Settings", 1, "https://cdn-icons-png.flaticon.com/128/3953/3953226.png");
        $form->addButton("Top Island", 1, "https://cdn-icons-png.flaticon.com/128/3884/3884708.png");
        $form->addButton("Delete Island", 1, "https://cdn-icons-png.flaticon.com/128/2602/2602768.png");
        $form->sendToPlayer($player);
    }

    public function friendMenu(Player $player){
        $form = new ModalForm(function(Player $player, $data){
            if($data === null){
                return true;
            }
            if($data == true){
                $this->addMenu($player);
            }
            else{
                $this->removeMenu($player);
            }
        });
        $form->setTitle("Add/Remove Friends");
        $form->setContent("Choose A Button, Bro :>");
        $form->setButton1("Add Friends");
        $form->setButton2("Remove Friends");
        $form->sendToPlayer($player);
    }

    public function addMenu(Player $player){
        $form = new CustomForm(function(Player $player, $data){
            if($data === null){
                return true;
            }
            if(!isset($data[0])){
                $player->sendMessage($this->prefix . "Please Enter Player Name");
                return true;
            }
            if(Island::getInstance()->isFriend($player, $data[0])){
                $player->sendMessage($this->prefix . "This Player is Already a Member of the Island");
                return true;
            }
            Island::getInstance()->addFriend($player, $data[0]);
            $player->sendMessage($this->prefix . "Added ". $data[0]);
        });
        $form->setTitle("Add Friends");
        $form->addInput("Enter Player's Name: ", "NoobLovePMMP");
        $form->sendToPlayer($player);
    }

    public function removeMenu(Player $player){
        $friend = [];
        if(Island::getInstance()->getIsland()->getNested($player->getName(). ".Friends") != ""){
            $ex = explode(", ", Island::getInstance()->getIsland()->getNested($player->getName(). ".Friends"));
            foreach($ex as $friendName){
                $friend[] = $friendName;
            }
        }
        $form = new CustomForm(function(Player $player, $data) use ($friend){
            if($data === null){
                return true;
            }
            $friend_name = $friend[$data[0]];
            Island::getInstance()->removeFriend($player, $friend_name);
            $player->sendMessage($this->prefix . "Removed ". $friend_name);
        });
        $form->setTitle("Remove Friends");
        $form->addDropdown("Choose Player's Name: ", $friend, 0);
        $form->sendToPlayer($player);
    }

    public function visitMenu(Player $player){
        $name = [];
        foreach(Island::getInstance()->getIsland()->getAll() as $playerName => $value){
            if($playerName != "island"){
                $island = Island::getInstance()->getIsland()->getNested($playerName . ".World-Name");
                $ex = explode("-", $island);
                $name[] = $ex[1];
            }
        }
        $form = new CustomForm(function(Player $player, $data) use ($name){
            if($data === null){
                return true;
            }
            $visitPlayer = $name[$data[0]];
            $worldName = Island::getInstance()->getIsland()->getNested($visitPlayer . ".World-Name");
            if(!Island::getInstance()->isIslandLocked($worldName)){
                Island::getInstance()->visitIsland($player, $visitPlayer);
                $player->sendMessage($this->prefix . "Welcome to ". $visitPlayer ."'s Island");
            }
            else{
                $player->sendMessage($this->prefix . "This Player's Island Is Locked");
            }
        });
        $form->setTitle("Visit Island");
        $form->addDropdown("Choose Player's Name: ", $name, 0);
        $form->sendToPlayer($player);
    }

    public function settingMenu(Player $player){
        $lock = Island::getInstance()->getIsland()->getNested($player->getName(). ".Island-Lock");
        $pvp = Island::getInstance()->getIsland()->getNested($player->getName(). ".Island-PvP");
        $form = new SimpleForm(function(Player $player, $data) use ($lock, $pvp){
            if($data === null){
                return true;
            }
            switch($data){
                case 0:
                    $msg = "";
                    if($lock == "Yes"){
                        Island::getInstance()->setLockIsland($player, false);
                        $msg = "Your island is unlocked";
                    }
                    else{
                        Island::getInstance()->setLockIsland($player, true);
                        $msg = "Your island is locked";
                    }
                    $player->sendMessage($this->prefix . "" . $msg);
                    break;
                case 1:
                    $msg = "";
                    if($pvp == "Yes"){
                        Island::getInstance()->setPvPIsland($player, false);
                        $msg = "Pvp is turn off in your island";
                    }
                    else{
                        Island::getInstance()->setPvPIsland($player, true);
                        $msg = "Pvp is turn on in your island";
                    }
                    $player->sendMessage($this->prefix . "" . $msg);
                    break;
            }
        });
        $status_lock = "";
        $status_pvp = "";
        if($lock == "No"){
            $status_lock = "§aUnlock";
        }
        else{
            $status_lock = "§cLock";
        }
        if($pvp == "Yes"){
            $status_pvp = "§cUnlock";
        }
        else{
            $status_pvp = "§cLock";
        }
        $form->setTitle("Setting Island");
        $form->setContent("How do you want to set up your island?");
        $form->addButton("Lock Island\n". $status_lock, 1, "https://cdn-icons-png.flaticon.com/128/595/595586.png");
        $form->addButton("PvP In Island\n". $status_pvp, 1, "https://cdn-icons-png.flaticon.com/128/3763/3763558.png");
        
        $form->sendToPlayer($player);
    }

    public function topMenu(Player $player){
        $form = new SimpleForm(function(Player $player, $data){
            if($data === null){
                return true;
            }
        });
        $content = "";
        $points = Island::getInstance()->getPointPlayer();
        arsort($points);
        $top = 0;
        foreach($points as $playerName => $point){
            $top++;
            if($content == ""){
                $content = "TOP ". (string)$top . ": ". $playerName . " - ". $point ." point\n";
            }
            else{
                $content .= "TOP ". (string)$top . ": ". $playerName . " - ". $point ." point\n";
            }
        }
        $form->setTitle("Top Island");
        $form->setContent($content);
        $form->addButton("Close");
        $form->sendToPlayer($player);
    }

    public function deleteQuestion(Player $player){
        $form = new SimpleForm(function(Player $player, $data){
            if($data === null){
                return true;
            }
            if($data == 0){
                Island::getInstance()->removeIsland(Island::getInstance()->getIsland()->getNested($player->getName() . ".World-Name"));
                Island::getInstance()->removeIslandPlayer($player);
                Island::getInstance()->getIsland()->remove($player->getName());
                Island::getInstance()->getIsland()->save();
                $player->sendMessage($this->prefix . "Delete Island Successfully");
            }
        });
        $form->setTitle("Delete Island");
        $form->setContent("Do you want to delete your island ?");
        $form->addButton("Yes, delete it");
        $form->addButton("Not now");
        $form->sendToPlayer($player);
    }
}