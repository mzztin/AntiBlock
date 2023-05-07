<?php

namespace mzztin\AntiBlock;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginOwned;

class AntiBlockListener implements Listener, PluginOwned
{
    public function __construct(private Main $owner)
    {
    }

    public function onBreakBlock(BlockBreakEvent $ev): void
    {
        if (!$this->getOwningPlugin()->isActive())
            return;

        $player = $ev->getPlayer();

        if ($player->hasPermission("antiblock.bypass.break") && $this->getOwningPlugin()->getConfig()->get("permission-bypass", true))
            return;

        $block = $ev->getBlock();
        if (!in_array($block->getIdInfo()->getBlockId(), $this->getOwningPlugin()->getBreakingBlocks()))
            return;

        $ev->cancel();
        if ($this->getOwningPlugin()->getConfig()->get("message-player", true)) {
            $player->sendMessage($this->getOwningPlugin()->getPrefix() . " " . str_replace("%block%", $block->getName(), $this->getOwningPlugin()->getConfig()->get("break-message")));
        }
    }

    public function getOwningPlugin(): Main
    {
        return $this->owner;
    }

    public function onBlockPlace(BlockPlaceEvent $ev): void
    {
        if (!$this->getOwningPlugin()->isActive())
            return;

        $player = $ev->getPlayer();

        if ($player->hasPermission("antiblock.bypass.place") && $this->getOwningPlugin()->getConfig()->get("permission-bypass", true))
            return;

        $block = $ev->getBlock();
        if (!in_array($block->getIdInfo()->getBlockId(), $this->getOwningPlugin()->getPlacingBlocks()))
            return;

        $ev->cancel();
        if ($this->getOwningPlugin()->getConfig()->get("message-player", true)) {
            $player->sendMessage($this->getOwningPlugin()->getPrefix() . " " . str_replace("%block%", $block->getName(), $this->getOwningPlugin()->getConfig()->get("place-message")));
        }
    }
}