<?php
/*
 *  _____           _        _   ______         _   _                 
 * |  __ \         | |      | | |  ____|       | | (_)                
 * | |__) |__   ___| | _____| |_| |__ __ _  ___| |_ _  ___  _ __  ___ 
 * |  ___/ _ \ / __| |/ / _ \ __|  __/ _` |/ __| __| |/ _ \| '_ \/ __|
 * | |  | (_) | (__|   <  __/ |_| | | (_| | (__| |_| | (_) | | | \__ \
 * |_|   \___/ \___|_|\_\___|\__|_|  \__,_|\___|\__|_|\___/|_| |_|___/
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.     
 *
 * PocketFactions v1.0.0 by Luke (TheDiamondYT)
 * All rights reserved.                         
 */
                                                                                     
namespace TheDiamondYT\PocketFactions;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\lang\BaseLang;
use pocketmine\Player;

use TheDiamondYT\PocketFactions\provider\Provider;
use TheDiamondYT\PocketFactions\provider\YamlProvider;
use TheDiamondYT\PocketFactions\provider\SQLiteProvider;
use TheDiamondYT\PocketFactions\commands\FCommandManager;

class Main extends PluginBase {

    private $provider;
    private $language = null;
    private $cfg;
    
    private $factions = [];
    
    public static $object = null;
   
    public function onLoad() {
        if(!(self::$object instanceof Main)) {
            self::$object = $this;
        }
    }
    
    public static function get() {
        return self::$object;
    }

	public function onEnable() {
	    $this->saveResource("config.yml");
	    $this->cfg = yaml_parse_file($this->getDataFolder() . "config.yml");
	    $this->language = new BaseLang($this->cfg["language"], $this->getFile() . "resources/lang/");
	    $this->getServer()->getCommandMap()->register(FCommandManager::class, new FCommandManager($this));
	    $this->setProvider();
	    $this->provider->loadFactions();
	}
	
	private function setProvider() {
	    switch($this->cfg["provider"]) { 
	        case "sqlite":
	            $provider = new SQLiteProvider($this);
	            break;
	        case "yaml":
	            $provider = new YamlProvider($this);
	            break;
	        default:
	            $provider = new YamlProvider($this);
	            break;
	    }
	    if($provider instanceof Provider) 
	        $this->provider = $provider;
	}
	
	public function getProvider() {
	    return $this->provider;
	}
	
	public function getConfig() {
	    return $this->cfg;
	}
	
	public function getLanguage() {
	    return $this->language;
	}
	
	public function getFaction($id) {
	    return $this->provider->getFaction($id);
	}
	
	public function getPlayer(Player $player) {
	
	} 
	
	public function factionExists($id) {
	    return $this->provider->factionExists($id);
	}
}
