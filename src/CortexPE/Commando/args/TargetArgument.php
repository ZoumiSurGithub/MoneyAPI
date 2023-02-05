<?php

namespace CortexPE\Commando\args;

use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\player\Player;
use pocketmine\Server;

class TargetArgument extends BaseArgument
{

    public function getNetworkType(): int
    {
        return AvailableCommandsPacket::ARG_TYPE_TARGET;
    }

    public function canParse(string $testString, CommandSender $sender): bool
    {
        return true;
    }

    public function parse(string $argument, CommandSender $sender): mixed
    {
        return $argument;
    }

    public function getTypeName(): string
    {
        return "target";
    }

}