<?php

namespace Zoumi\MoneyAPI\event;

use pocketmine\event\Event;
use pocketmine\player\Player;

class PlayerUpdateMoneyEvent extends Event
{

    private string $username;
    private string $xuid;
    private float $old_money;
    private float $new_money;

    public function __construct(string $username, string $xuid, float $old_money, float $new_money)
    {
        $this->username = $username;
        $this->xuid = $xuid;
        $this->old_money = $old_money;
        $this->new_money = $new_money;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getXuid(): string
    {
        return $this->xuid;
    }

    /**
     * @return float
     */
    public function getOldMoney(): float
    {
        return $this->old_money;
    }

    /**
     * @return float
     */
    public function getNewMoney(): float
    {
        return $this->new_money;
    }

}