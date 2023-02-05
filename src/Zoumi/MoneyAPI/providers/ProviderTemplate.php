<?php

namespace Zoumi\MoneyAPI\providers;

use pocketmine\player\Player;

interface ProviderTemplate
{

    public function onInit(): void;

    public function getDataBase();

    public function verifyAccount(string $xuid): void;

    public function setMoney(string $xuid, float $money): void;

    public function addMoney(string $xuid, float $money): void;

    public function removeMoney(string $xuid, float $money): void;

    public function getMoney(string $xuid): float;

    public function saveCache(string $xuid): void;

    public function saveAllCached(): void;

    public function exist(string $target): bool;

}