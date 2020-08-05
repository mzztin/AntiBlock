<?php

declare(strict_types=1);

namespace Martin\AntiBlock;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener
{
    /**
     * @var Config
     */
    private $cfg;

    public function onEnable()
    {
        $this->saveResource("config.yml");
        $this->cfg = new Config($this->getDataFolder() . "config.yml");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        switch ($command->getName()) {
            case "antiblockreload":
            {
                $this->cfg->reload();
                $sender->sendMessage($this->cfg->get("prefix") . " " . $this->cfg->get("reloaded"));
                break;
            }
        }

        return true;
    }

    public function onBlockPlace(BlockPlaceEvent $ev): void
    {
        $player = $ev->getPlayer();
        $bl = $ev->getItem();
        $name = str_replace(" ", "_", strtoupper($bl->getName()));

        if (in_array($name, $this->cfg->get("blocks")) && !$player->hasPermission("antiblock.bypass")) {
            if ($this->cfg->get("message-player")) {
                $player->sendMessage($this->cfg->get("prefix") . " " . str_replace("%block%", $ev->getBlock()->getName(), $this->cfg->get("place-message")));
            }

            $ev->setCancelled();
        }
    }
}
