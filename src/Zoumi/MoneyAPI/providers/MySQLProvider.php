<?php

namespace Zoumi\MoneyAPI\providers;

use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use Zoumi\MoneyAPI\event\PlayerUpdateMoneyEvent;
use Zoumi\MoneyAPI\MoneyAPI;

class MySQLProvider implements ProviderTemplate
{

    private DataConnector $db;
    private array $cached = [];

    public function onInit(): void
    {
        $this->db = libasynql::create(MoneyAPI::getInstance(), MoneyAPI::getInstance()->getConfig()->get("database"), [
            "mysql" => "mysql.sql"
        ]);
        $this->db->executeGeneric("init");
    }

    /**
     * @return DataConnector
     */
    public function getDataBase(): DataConnector
    {
        return $this->db;
    }

    /**
     * @param string $xuid
     * @return void
     */
    public function verifyAccount(string $xuid): void
    {
        $this->getDataBase()->executeSelect("exist", [
            "xuid" => $xuid
        ], function (array $rows) use ($xuid) {
            if (empty($rows)) {
                $secure = MoneyAPI::getInstance()->getSecure()->get($xuid);
                $this->getDataBase()->executeInsert("create", [
                    "xuid" => $xuid,
                    "pseudo" => $secure["pseudo"],
                    "money" => MoneyAPI::getInstance()->getConfig()->get("start-money")
                ]);
                $this->cached[$xuid] = MoneyAPI::getInstance()->getConfig()->get("start-money");
            } else {
                $value = $rows[0];
                $this->cached[$xuid] = $value["money"];
            }
        });
    }

    /**
     * @param string $xuid
     * @param float $money
     * @return void
     */
    public function setMoney(string $xuid, float $money): void
    {
        $this->cached[$xuid] = $money;
    }

    /**
     * @param string $xuid
     * @param float $money
     * @return void
     */
    public function addMoney(string $xuid, float $money): void
    {
        $actus = $this->cached[$xuid];
        $secure = MoneyAPI::getInstance()->getSecure()->get($xuid);
        if (($this->getMoney($xuid) + $money) > MoneyAPI::getInstance()->getConfig()->get("max-money")) {
            $this->cached[$xuid] = MoneyAPI::getInstance()->getConfig()->get("max-money");
        } else {
            $this->cached[$xuid] = $actus + $money;
        }
        $ev = new PlayerUpdateMoneyEvent($secure["pseudo"], $xuid, $actus, $this->cached[$xuid]);
        $ev->call();
    }

    /**
     * @param string $xuid
     * @param float $money
     * @return void
     */
    public function removeMoney(string $xuid, float $money): void
    {
        $actus = $this->cached[$xuid];
        $secure = MoneyAPI::getInstance()->getSecure()->get($xuid);
        if (($this->getMoney($xuid) - $money) < 0){
            $this->cached[$xuid] = 0;
        }else {
            $this->cached[$xuid] = $actus - $money;
        }
        $ev = new PlayerUpdateMoneyEvent($secure["pseudo"], $xuid, $actus, $this->cached[$xuid]);
        $ev->call();
    }

    /**
     * @param string $xuid
     * @return float
     */
    public function getMoney(string $xuid): float
    {
        return $this->cached[$xuid];
    }

    /**
     * @param string $xuid
     * @return void
     */
    public function saveCache(string $xuid): void
    {
        if (isset($this->cached[$xuid])) {
            $this->db->executeChange("update", [
                "xuid" => $xuid,
                "money" => $this->cached[$xuid]
            ], function (int $affected) use ($xuid) {
                unset($this->cached[$xuid]);
            });
        }
    }

    /**
     * @return void
     */
    public function saveAllCached(): void
    {
        $count = 1;
        foreach ($this->cached as $xuid => $money) {
            $percentage = count($this->cached) - $count / 100;
            $this->db->executeChange("update", [
                "xuid" => $xuid,
                "money" => $money
            ], function (int $affected) use ($percentage) {
                MoneyAPI::getInstance()->getLogger()->info($percentage . "/100% effectué");
            });
            $count++;
        }
    }

    public function exist(string $target): bool
    {
        return false;
    }

}