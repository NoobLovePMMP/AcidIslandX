<?php

namespace Noob\AcidIsland\commands;

use pocketmine\player\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginOwned;
use Noob\AcidIsland\AcidIsland;
use pocketmine\Server;
use Noob\AcidIsland\forms\FormManager;

class AcidIslandCommand extends Command implements PluginOwned
{
    private AcidIsland $plugin;
    public string $prefix = "[Island] ";

    public function __construct(AcidIsland $plugin)
    {
        $this->plugin = $plugin;
        parent::__construct("acidisland", "Open Menu AcidIsland", null, ["island"]);
        $this->setPermission("acidisland.cmd");
    }

    public function execute(CommandSender $player, string $label, array $args)
    {
        if(!$player instanceof Player){
            return 1;
        }
        $form = new FormManager;
        if(!$this->plugin->hasIsland($player->getName())){
            $form->createMenu($player);
        }
        else{
            $form->islandMenu($player);
        }
    }

    public function getOwningPlugin(): AcidIsland
    {
        return $this->plugin;
    }
}