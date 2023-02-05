<?php

namespace Zoumi\MoneyAPI\commands\all;

use CortexPE\Commando\args\TargetArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use JsonException;
use Zoumi\MoneyAPI\providers\JSONProvider;
use Zoumi\MoneyAPI\providers\YAMLProvider;
use Zoumi\MoneyAPI\utils\Lang;
use Zoumi\MoneyAPI\MoneyAPI;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use pocketmine\Server;

class Money extends BaseCommand
{

    /**
     * @return void
     * @throws ArgumentOrderException
     * @throws JsonException
     */
    protected function prepare(): void
    {
        $this->registerArgument(0, new TargetArgument("target", true));
        $this->setPermission("moneyapi.command.money");
        $this->setPermissionMessage(Lang::get("no-permission"));
        $this->setUsage("§cPlease do /money [target]");
    }

    /**
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param array $args
     * @return void
     * @throws JsonException
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$this->testPermission($sender)) return;
        if (!isset($args["target"])) {
            if ($sender instanceof Player) {
                $provider = MoneyAPI::getInstance()->getProvider();
                $sender->sendMessage(str_replace(["{money}"], [$provider->getMoney($sender->getXuid())], Lang::get("view-money")));
                return;
            }
            $sender->sendMessage("§cPlease do /money [target].");
        } else {
            $provider = MoneyAPI::getInstance()->getProvider();
            $player = Server::getInstance()->getPlayerExact($args["target"]);
            if ($player instanceof Player) {
                $sender->sendMessage(str_replace(["{money}", "{player}"], [$provider->getMoney($player->getXuid()), $player->getName()], Lang::get("view-money-player")));
                return;
            } else {
                if (MoneyAPI::getInstance()->getSecure()->exists($args["target"])) {
                    $secure = MoneyAPI::getInstance()->getSecure()->get($args["target"]);
                    if ($provider instanceof YAMLProvider || $provider instanceof JSONProvider) {
                        if ($provider->exist($args["target"])) {
                            $sender->sendMessage(str_replace(["{money}", "{player}"], [$provider->getMoney($secure["xuid"]), $args["target"]], Lang::get("view-money-player")));
                        } else {
                            $sender->sendMessage(Lang::get("player-no-in-db"));
                        }
                    } else {
                        $provider->getDataBase()->executeSelect("exist", [
                            "xuid" => $secure["xuid"]
                        ], function (array $rows) use ($sender, $args) {
                            if (empty($rows)){
                                $sender->sendMessage(Lang::get("player-no-in-db"));
                            }else{
                                $value = $rows[0];
                                $sender->sendMessage(str_replace(["{money}", "{player}"], [$value["money"], $args["target"]], Lang::get("view-money-player")));
                            }
                        });
                    }
                }
            }
        }
    }

}