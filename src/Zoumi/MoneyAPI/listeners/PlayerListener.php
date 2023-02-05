<?php

namespace Zoumi\MoneyAPI\listeners;

use JsonException;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\XboxLivePlayerInfo;
use Zoumi\MoneyAPI\MoneyAPI;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerPreLoginEvent;

class PlayerListener implements Listener
{

    /**
     * @param PlayerPreLoginEvent $event
     * @return void
     * @throws JsonException
     */
    public function onPreLogin(PlayerPreLoginEvent $event): void
    {
        $player = $event->getPlayerInfo();
        if ($player instanceof XboxLivePlayerInfo) {
            $config = MoneyAPI::getInstance()->getSecure();
            $config->set($player->getXuid(), [
                "xuid" => $player->getXuid(),
                "pseudo" => $player->getUsername()
            ]);
            $config->set($player->getUsername(), [
                "xuid" => $player->getXuid(),
                "pseudo" => $player->getUsername()
            ]);
            $config->save();
            MoneyAPI::getInstance()->getProvider()->verifyAccount($player->getXuid());
        }
    }

    /**
     * @param PlayerQuitEvent $event
     * @return void
     */
    public function onQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        $playerInfo = $player->getPlayerInfo();
        if ($playerInfo instanceof XboxLivePlayerInfo){
            MoneyAPI::getInstance()->getProvider()->saveCache($playerInfo->getXuid());
        }
    }

}