<?php

namespace mzztin\AntiBlock\commands;

use mzztin\AntiBlock\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\data\bedrock\LegacyBlockIdToStringIdMap;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat;

class AntiBlockCommand extends Command implements PluginOwned
{
    public function __construct(private Main $owner)
    {
        parent::__construct("antiblock", "The command to manage the AntiBlock plugin", "/antiblock <on|off|add|list|info>");
        $this->setPermission("antiblock.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$this->testPermission($sender)) {
            return;
        }

        if (count($args) == 0) {
            $sender->sendMessage($this->getUsage());
            return;
        }

        $option = array_shift($args);

        switch ($option) {
            case "on":
                $this->getOwningPlugin()->setActive(true);
                $sender->sendMessage($this->getOwningPlugin()->getPrefix() . TextFormat::GREEN . " Plugin is now actively running");
                break;

            case "off":
                $this->getOwningPlugin()->setActive(false);
                $sender->sendMessage($this->getOwningPlugin()->getPrefix() . TextFormat::RED . " Plugin is now disabled");
                break;

            case "add":
                if (count($args) < 2) {
                    $sender->sendMessage("Usage: /antiblock add <place|break> <hand|id>");
                    return;
                }

                $type = array_shift($args);
                $block = array_shift($args);

                if (!in_array($type, ["place", "break"])) {
                    $sender->sendMessage("Usage: /antiblock add <place|break> <hand|id|BLOCK_NAME>");
                    return;
                }

                switch ($block) {
                    case "hand":
                        if (!($sender instanceof Player)) {
                            $sender->sendMessage($this->getOwningPlugin()->getPrefix() . TextFormat::RED . " ");
                            return;
                        }

                        $actual_block = $sender->getInventory()->getItemInHand();

                        if ($type == "place")
                            $result = $this->getOwningPlugin()->addPlaceBlock($actual_block->getId());
                        else
                            $result = $this->getOwningPlugin()->addBreakBlock($actual_block->getBlock());

                        if ($result)
                            $sender->sendMessage($this->getOwningPlugin()->getPrefix() . TextFormat::GREEN . " Added \"" . $actual_block->getName() . "\" to the " . $type . " blocks");
                        else
                            $sender->sendMessage($this->getOwningPlugin()->getPrefix() . TextFormat::RED . " The block \"" . $actual_block->getName() . "\" is already in that list!");
                        break;
                    default:
                        if (!is_numeric($block)) {
                            $sender->sendMessage($this->getOwningPlugin()->getPrefix() . TextFormat::RED . " The argument has to be an numeric value");
                            return;
                        }

                        $id = (int)$block;
                        $name = LegacyBlockIdToStringIdMap::getInstance()->legacyToString($id);

                        if (is_null($name)) {
                            $sender->sendMessage($this->getOwningPlugin()->getPrefix() . TextFormat::RED . " Couldn't find an block with following id!");
                            return;
                        }

                        $result = false;
                        if ($type == "place")
                            $result = $this->getOwningPlugin()->addPlaceBlock($id);
                        else
                            $result = $this->getOwningPlugin()->addBreakBlock($id);

                        if ($result)
                            $sender->sendMessage($this->getOwningPlugin()->getPrefix() . TextFormat::GREEN . " Added \"" . $name . "\" to the " . $type . " blocks");
                        else
                            $sender->sendMessage($this->getOwningPlugin()->getPrefix() . TextFormat::RED . " The block \"" . $name . "\" is already in that list!");
                        break;

                }

                break;

            case "list":
                $sender->sendMessage($this->getOwningPlugin()->getPrefix() . TextFormat::AQUA . " Blocks that are blocked from placing");
                if (count($this->getOwningPlugin()->getPlacingBlocks()) === 0)
                    $sender->sendMessage(TextFormat::BLUE . "No blocks were added to the placing list!");
                else
                    $sender->sendMessage(TextFormat::BLUE . implode(", ",
                            array_map(
                                function (int $id) {
                                    return LegacyBlockIdToStringIdMap::getInstance()->legacyToString($id);
                                },
                                $this->getOwningPlugin()->getPlacingBlocks())
                        ));

                $sender->sendMessage("");

                $sender->sendMessage($this->getOwningPlugin()->getPrefix() . TextFormat::AQUA . " Blocks that are blocked from breaking");
                if (count($this->getOwningPlugin()->getBreakingBlocks()) === 0)
                    $sender->sendMessage(TextFormat::BLUE . "No blocks were added to the breaking list!");
                else
                    $sender->sendMessage(TextFormat::BLUE . implode(", ",
                            array_map(
                                function (int $id) {
                                    return LegacyBlockIdToStringIdMap::getInstance()->legacyToString($id);
                                },
                                $this->getOwningPlugin()->getBreakingBlocks())
                        ));

                break;

            case "reload":
                $sender->sendMessage($this->getOwningPlugin()->getPrefix() . TextFormat::GREEN . " Plugin has been reloaded");
                $this->getOwningPlugin()->reloadConfig();
                break;

            case "info":
                $sender->sendMessage($this->getOwningPlugin()->getPrefix() . TextFormat::AQUA . " Informations");
                $sender->sendMessage(TextFormat::AQUA . "Active: " . ($this->getOwningPlugin()->isActive() ? TextFormat::GREEN . "on" : TextFormat::RED . "off"));
                $sender->sendMessage(TextFormat::AQUA . "Number of blocks blocked from placing: " . (string)count($this->getOwningPlugin()->getPlacingBlocks()));
                $sender->sendMessage(TextFormat::AQUA . "Number of blocks blocked from breaking: " . (string)count($this->getOwningPlugin()->getBreakingBlocks()));
                break;

            default:
                $sender->sendMessage($this->getUsage());
                break;
        }
    }

    public function getOwningPlugin(): Main
    {
        return $this->owner;
    }
}