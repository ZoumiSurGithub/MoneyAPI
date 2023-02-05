<?php

namespace Zoumi\MoneyAPI\utils;

use JsonException;
use Zoumi\MoneyAPI\MoneyAPI;
use pocketmine\utils\Config;

class Lang {

    /**
     * @return Config
     * @throws JsonException
     */
    public static function getLang(): Config{
        $lang = MoneyAPI::getInstance()->getConfig()->get("lang");
        if (!in_array($lang, ["fr", "en", "es"])){
            MoneyAPI::getInstance()->getLogger()->error("The language \"{$lang}\" is not supported by the plugin. Restore the default language.");
            $config = MoneyAPI::getInstance()->getConfig();
            $config->set("lang", "en");
            $config->save();
            if (!file_exists(MoneyAPI::getInstance()->getDataFolder() . "lang-en.yml")){
                MoneyAPI::getInstance()->saveResource("lang-en.yml");
            }
            return new Config(MoneyAPI::getInstance()->getDataFolder() . "lang-en.yml", Config::YAML);
        }
        if (!file_exists(MoneyAPI::getInstance()->getDataFolder() . "lang-{$lang}.yml")){
            MoneyAPI::getInstance()->saveResource("lang-{$lang}.yml");
        }
        return new Config(MoneyAPI::getInstance()->getDataFolder() . "lang-" . $lang . ".yml", Config::YAML);
    }

    /**
     * @param string $key
     * @return bool|mixed
     * @throws JsonException
     */
    public static function get(string $key): mixed
    {
        return self::getLang()->get($key);
    }

}