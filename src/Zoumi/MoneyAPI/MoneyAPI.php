<?php

namespace Zoumi\MoneyAPI;

use CortexPE\Commando\exception\HookAlreadyRegistered;
use CortexPE\Commando\PacketHooker;
use Exception;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use Zoumi\MoneyAPI\commands\admin\AddMoney;
use Zoumi\MoneyAPI\commands\admin\RemoveMoney;
use Zoumi\MoneyAPI\commands\admin\SetMoney;
use Zoumi\MoneyAPI\commands\all\Money;
use Zoumi\MoneyAPI\listeners\PlayerListener;
use Zoumi\MoneyAPI\providers\JSONProvider;
use Zoumi\MoneyAPI\providers\MySQLProvider;
use Zoumi\MoneyAPI\providers\ProviderTemplate;
use Zoumi\MoneyAPI\providers\SQLITEProvider;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use Zoumi\MoneyAPI\providers\YAMLProvider;

class MoneyAPI extends PluginBase
{
    use SingletonTrait;

    private static ProviderTemplate $provider;

    protected function onLoad(): void
    {
        self::setInstance($this);
    }

    /**
     * @throws HookAlreadyRegistered
     * @throws Exception
     */
    protected function onEnable(): void
    {
        $this->saveDefaultConfig();

        // Commando
        if (!PacketHooker::isRegistered()) {
            PacketHooker::register($this);
        }

        $this->initProvider();
        $this->initCommands();

        $this->getServer()->getPluginManager()->registerEvents(new PlayerListener(), $this);
    }

    /**
     * @return void
     */
    protected function onDisable(): void
    {
        $this->getServer()->getLogger()->error("NE FORCER PAS LA FERMETURE SOUS PEINE DE PERDRE DES DONNéES!\nDO NOT FORCE CLOSING OR LOSE DATA!");
        if (self::getProvider() instanceof MySQLProvider || self::getProvider() instanceof SqliteProvider) {
            self::getProvider()->getDataBase()->waitAll();
            self::getProvider()->getDataBase()->close();
        }
    }

    /**
     * @throws Exception
     */
    protected function initProvider(): void
    {
        switch (self::getInstance()->getConfig()->get("provider")) {
            case "json":
                self::$provider = new JsonProvider();
                self::$provider->onInit();
                break;
            case "yml":
            case "yaml":
                self::$provider = new YAMLProvider();
                self::$provider->onInit();
                break;
            case "mysql":
                self::$provider = new MySQLProvider();
                self::$provider->onInit();
                break;
            case "sql":
            case "sqlite":
                self::$provider = new SqliteProvider();
                self::$provider->onInit();
                break;
            default:
                throw new Exception("The '" . self::getInstance()->getConfig()->get("provider") . "' provider is not supported.");
        }
    }

    /**
     * @return void
     */
    private function initCommands(): void
    {
        $this->getServer()->getCommandMap()->registerAll("MoneyAPI", [
            new Money($this,"money", "Allows you to see your or a player's money."),
            new SetMoney($this,"setmoney", "Allows you to redefine a player's money."),
            new AddMoney($this,"addmoney", "Allows you to add money to a player."),
            new RemoveMoney($this,"removemoney", "Allows you to withdraw money from a player.")
        ]);
    }

    /**
     * @return ProviderTemplate
     */
    public function getProvider(): ProviderTemplate
    {
        return self::$provider;
    }

    /**
     * @return Config
     */
    public function getSecure(): Config
    {
        return new Config($this->getDataFolder() . "secure.json", Config::JSON);
    }

}