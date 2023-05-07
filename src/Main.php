<?php

declare(strict_types=1);

namespace mzztin\AntiBlock;

use mzztin\AntiBlock\commands\AntiBlockCommand;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Main extends PluginBase
{
    protected bool $active = true;

    public function addBreakBlock(int $id): bool
    {
        if (in_array($id, $this->getBreakingBlocks()))
            return false;

        $arr = $this->getBreakingBlocks();
        $arr[] = $id;

        $this->getConfig()->set("break-blocks", $arr);
        return true;
    }

    /**
     * @return array<int>
     */
    public function getBreakingBlocks(): array
    {
        return $this->getConfig()->get("break-blocks");
    }

    public function addPlaceBlock(int $id): bool
    {
        if (in_array($id, $this->getPlacingBlocks()))
            return false;

        $arr = $this->getPlacingBlocks();
        $arr[] = $id;

        $this->getConfig()->set("place-blocks", $arr);
        return true;
    }

    /**
     * @return array<int>
     */
    public function getPlacingBlocks(): array
    {
        return $this->getConfig()->get("place-blocks");
    }

    public function getPrefix(): string
    {
        return $this->getConfig()->get("prefix") . TextFormat::RESET;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $value): void
    {
        $this->active = $value;
    }

    protected function onEnable(): void
    {
        $this->saveDefaultConfig();
        $this->getServer()->getCommandMap()->register("_antiblock", new AntiBlockCommand($this));
        $this->getServer()->getPluginManager()->registerEvents(new AntiBlockListener($this), $this);
    }

    protected function onLoad(): void
    {
        $this->reloadConfig();
    }

    protected function onDisable(): void
    {
        $this->getConfig()->save();
    }
}
