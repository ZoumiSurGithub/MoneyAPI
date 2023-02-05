<?php

namespace Zoumi\MoneyAPI\providers;

use JsonException;
use Zoumi\MoneyAPI\event\PlayerUpdateMoneyEvent;
use Zoumi\MoneyAPI\MoneyAPI;
use Zoumi\MoneyAPI\utils\Utils;
use pocketmine\player\Player;
use pocketmine\utils\Config;

class JSONProvider implements ProviderTemplate
{

    public function onInit(): void
    {
        // TODO: Implement onInit() method.
    }

    /**
     * @return Config
     */
    public function getDataBase(): Config
    {
        return new Config(MoneyAPI::getInstance()->getDataFolder() . "money.json", Config::JSON);
    }

    /**
     * @throws JsonException
     */
    public function verifyAccount(string $xuid): void
    {
        if (!$this->getDataBase()->exists($xuid)) {
            $config = $this->getDataBase();
            $config->set($xuid, MoneyAPI::getInstance()->getConfig()->get("start-money"));
            $config->save();
        }
    }

    /**
     * @param string $xuid
     * @param float $money
     * @return void
     * @throws JsonException
     */
    public function setMoney(string $xuid, float $money): void
    {
        $secure = MoneyAPI::getInstance()->getSecure()->get($xuid);
        $ev = new PlayerUpdateMoneyEvent($secure["pseudo"], $xuid, $this->getMoney($xuid), $money);
        $ev->call();
        $config = $this->getDataBase();
        $config->set($xuid, $money);
        $config->save();
    }

    /**
     * @param string $xuid
     * @param float $money
     * @return void
     * @throws JsonException
     */
    public function addMoney(string $xuid, float $money): void
    {
        $config = $this->getDataBase();
        $secure = MoneyAPI::getInstance()->getSecure()->get($xuid);
        $actus = $config->get($xuid);
        if (($this->getMoney($xuid) + $money) > MoneyAPI::getInstance()->getConfig()->get("max-money")) {
            $config->set($xuid, MoneyAPI::getInstance()->getConfig()->get("max-money"));
        } else {
            $config->set($xuid, $this->getMoney($xuid) + $money);
        }
        $config->save();
        $ev = new PlayerUpdateMoneyEvent($secure["pseudo"], $xuid, $actus, $config->get($xuid));
        $ev->call();
    }

    /**
     * @param string $xuid
     * @param float $money
     * @return void
     * @throws JsonException
     */
    public function removeMoney(string $xuid, float $money): void
    {
        $config = $this->getDataBase();
        $secure = MoneyAPI::getInstance()->getSecure()->get($xuid);
        $actus = $config->get($xuid);
        if (($this->getMoney($xuid) - $money) < 0){
            $config->set($xuid, 0);
        }else {
            $config->set($xuid, $this->getMoney($xuid) - $money);
        }
        $config->save();
        $ev = new PlayerUpdateMoneyEvent($secure["pseudo"], $xuid, $actus, $config->get($xuid));
        $ev->call();
    }

    /**
     * @param string $xuid
     * @return float
     */
    public function getMoney(string $xuid): float
    {
        return $this->getDataBase()->get($xuid);
    }

    public function saveCache(string $xuid): void
    {
        // TODO: Implement saveCache() method.
    }

    public function saveAllCached(): void
    {
        // TODO: Implement saveAllCached() method.
    }

    /**
     * @param string $target
     * @return bool
     */
    public function exist(string $target): bool
    {
        $secure = MoneyAPI::getInstance()->getSecure();
        if ($secure->exists($target)) {
            return $this->getDataBase()->exists($secure->get($target)["xuid"]);
        }
        return false;
    }

}