<?php

namespace Zoumi\MoneyAPI\commands\admin;

use CortexPE\Commando\args\FloatArgument;
use CortexPE\Commando\args\TargetArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use JsonException;
use Zoumi\MoneyAPI\MoneyAPI;
use Zoumi\MoneyAPI\providers\JSONProvider;
use Zoumi\MoneyAPI\providers\YAMLProvider;
use Zoumi\MoneyAPI\utils\Lang;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;

class AddMoney extends BaseCommand
{

    /**
     * @return void
     * @throws ArgumentOrderException
     * @throws JsonException
     */
    protected function prepare(): void
    {
        $this->registerArgument(0, new TargetArgument("target"));
        $this->registerArgument(1, new FloatArgument("money"));
        $this->setPermission("moneyapi.command.addmoney");
        $this->setPermissionMessage(Lang::get("no-permission"));
        $this->setUsage("§cPlease do /addmoney [target] [money]");
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
        if ($args["money"] < 0) {
            $sender->sendMessage(Lang::get("invalid-number"));
            return;
        }elseif ($args["money"] > MoneyAPI::getInstance()->getConfig()->get("max-money")){
            $sender->sendMessage(Lang::get("number-limit"));
            return;
        }
        $provider = MoneyAPI::getInstance()->getProvider();
        $player = Server::getInstance()->getPlayerExact($args["target"]);
        if ($player instanceof Player) {
            $provider->addMoney($player->getXuid(), $args["money"]);
            $sender->sendMessage(str_replace(["{player}", "{money}"], [$player->getName(), $args["money"]], Lang::get("addmoney-sender-msg")));
            $player->sendMessage(str_replace(["{player}", "{money}"], [$sender->getName(), $args["money"]], Lang::get("addmoney-player-msg")));
            return;
        } else {
            if ($provider instanceof YAMLProvider || $provider instanceof JSONProvider) {
                if ($provider->exist($args["target"])) {
                    $provider->addMoney(MoneyAPI::getInstance()->getSecure()->get($args["target"])["xuid"], $args["money"]);
                    $sender->sendMessage(str_replace(["{player}", "{money}"], [$args["target"], $args["money"]], Lang::get("addmoney-sender-msg")));
                } else {
                    $sender->sendMessage(Lang::get("player-no-in-db"));
                }
            } else {
                $secure = MoneyAPI::getInstance()->getSecure();
                if ($secure->exists($args["target"])) {
                    $provider->getDataBase()->executeSelect("exist", [
                        "xuid" => $secure->get($args["target"])["xuid"]
                    ], function (array $rows) use ($sender, $provider, $secure, $args) {
                        if (empty($rows)) {
                            $sender->sendMessage(Lang::get("player-no-in-db"));
                        } else {
                            $value = $rows[0];
                            $provider->getDataBase()->executeChange("update", [
                                "xuid" => $secure->get($args["target"])["xuid"],
                                "money" => $value["money"] + $args["money"]
                            ], function (int $affected) use ($sender, $args) {
                                $sender->sendMessage(str_replace(["{player}", "{money}"], [$args["target"], $args["money"]], Lang::get("addmoney-sender-msg")));
                            });
                        }
                    });
                }
            }
        }
    }

}