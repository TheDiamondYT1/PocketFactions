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
 * PocketFactions v1.0.1 by Luke (TheDiamondYT)
 * All rights reserved.                         
 */
                                                                                     
namespace TheDiamondYT\PocketFactions;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TF;

use TheDiamondYT\PocketFactions\provider\Provider;
use TheDiamondYT\PocketFactions\provider\JSONProvider;
use TheDiamondYT\PocketFactions\provider\SQLiteProvider;
use TheDiamondYT\PocketFactions\command\FCommandManager;
use TheDiamondYT\PocketFactions\listener\FPlayerListener;
use TheDiamondYT\PocketFactions\entity\Faction;
use TheDiamondYT\PocketFactions\struct\Relation;
use TheDiamondYT\PocketFactions\struct\Role;
use TheDiamondYT\PocketFactions\util\TextUtil;

class PF extends PluginBase {

    const PREFIX = "§b[§dPocketFactions§b] ";

    /* @var Provider */
    private $provider;
    
    /* @var Config */
    private $language;
    
    /* @var array */
    private $cfg;
    
    /* @var FCommandManager */
    private $fcommandManager;
    
    /* @var PF */
    public static $instance = null;
    
    /**
     * @deprecated
     */
    public static function get(): PF {
        return self::$instance;
    }
    
    /**
     * @return PF
     */
    public static function getInstance(): PF {
        return self::$instance;
    }
   
    /**
     * Log a message to the console.
     *
     * @param string
     */
    public static function log($text) {
        Server::getInstance()->getLogger()->info(self::PREFIX . TF::YELLOW . "$text");
    }
    
    /**
     * Log an error to the console.
     *
     * @param string
     */
    public static function logError($text) {
        Server::getInstance()->getLogger()->critical(self::PREFIX . TF::RED . "$text");
    }
    
    public function onLoad() {
        self::$instance = $this;
        
        // Create config
        $this->saveResource("config.yml");
        $this->cfg = yaml_parse_file($this->getDataFolder() . "config.yml");

        // Load language
        $this->language = new Config($this->getFile() . "resources/lang/" . $this->cfg["language"] . ".json", Config::JSON);
    }

	public function onEnable() {
	    if(!defined("START_TIME")) {
	        define("START_TIME", microtime(true));
	    }

	    // Initialize command manager and events
	    $this->fcommandManager = new FCommandManager($this); // This takes a long time!
	    $this->getServer()->getCommandMap()->register(FCommandManager::class, $this->fcommandManager);
	    $this->getServer()->getPluginManager()->registerEvents(new FPlayerListener($this), $this);

        // Load faction data (this is in a specific order)
	    $this->setProvider(); 
	    $this->provider->loadFactions();
	    $this->checkFactions();
	    $this->provider->loadPlayers();
	    
	    Role::init(); // Initialize faction roles
	    
	    self::log($this->translate("console.data-loaded", [
	        round(microtime(true) - START_TIME, 2), 
	        round(microtime(true) * 1000) - round(START_TIME * 1000)
	    ]));
	}
	
	public function onDisable() {
	    if($this->provider !== null) {
	        $this->provider->save();
	    }
	}
	
	
	/**
	 * Checks if the default factions are created. 
	 * If not, create them.
	 */
	private function checkFactions() {
        if(!$this->factionExists("Wilderness")) {
            (new Faction($id = Faction::WILDERNESS_ID, [
                "tag" => "Wilderness",
                "id" => $id,
                "description" => "It's dangerous to go alone.",
                "flags" => [
                     "open" => true,
                     "permanent" => true
                 ]
            ]))->create(true);
        }
        if(!$this->factionExists("WarZone")) {
            (new Faction($id = Faction::WARZONE_ID, [
                "tag" => "WarZone",
                "id" => $id,
                "description" => "Not the safest place to be.",
                "flags" => [
                    "open" => false,
                    "permanent" => true
                ]
            ]))->create(true);
        }
	}
	
	/**
	 * Sets the data provider for the plugin.
	 *
	 * @param Provider
	 */
	public function setProvider($provider = null) {
	    if(!$provider) {
	        switch($this->cfg["provider"]) { 
	            case "sqlite":
	                if(!extension_loaded("sqlite3")) {
	                    self::log("Unable to find the SQLite3 exstension. Setting data provider to json.");
	                    $this->provider = new YamlProvider($this);
	                    return;
	                }
	                $provider = new SQLiteProvider($this);
	                break;
	            case "json":
	                $provider = new JSONProvider($this);
	                break;
	            default:
	                $provider = new JSONProvider($this);
	                break;
	        }
	    }
	    // This check is to allow custom data providers
	    if($provider instanceof Provider) {
	        $this->provider = $provider;
	    } else {
	        self::logError("Data provider " . get_class($provider) . " is not an instance of Provider.");
	        $this->getServer()->getPluginManager()->disablePlugin($this);
	    }
	}
	
	/** 
	 * Returns the faction command manager.
	 *
	 * @return FCommandManager
	 */
	public function getCommandManager(): FCommandManager {
	    return $this->fcommandManager;
	}
	
	/**
	 * Returns the data provider for the plugin.
	 *
	 * @return Provider
	 */
	public function getProvider(): Provider {
	    return $this->provider;
	}
	
	/**
	 * Returns the config as an array.
	 *
	 * @return array
	 */
	public function getConfig(): array {
	    return $this->cfg;
	}

    /**
     * Returns a faction from its tag.
     *
     * @param string
     * @return Faction|null
     */
	public function getFaction(string $faction) {      
	    return $this->provider->getFaction($faction);
	}
	
	/**
	 * Returns an faction player.
	 *
	 * @param Player|string
	 * @return FPlayer|null
	 */
	public function getPlayer($player) {
	    return $this->provider->getPlayer($player);
	} 
	
	/**
	 * Returns true if the specified faction exists.
	 *
	 * @param string
	 * @return bool 
	 */
	public function factionExists(string $faction) {
	    return $this->provider->factionExists($faction);
	}
	
	/** 
	 * Returns true if the specified player exists.
	 *
	 * @param Player|string
	 * @return bool
	 */
	public function playerExists($player) {
	    return $this->provider->playerExists($player);
	}
	
	/**
	 * Translates a message.
	 *
	 * @param string 
	 * @param array 
	 * @return string
	 */
	public function translate(string $text, array $params = []) {
	    if($this->language->getNested($text)) {
	        if(!empty($params)) 
	            return vsprintf($this->language->getNested($text), $params);
	      
	        return $this->language->getNested($text);
	    }
	}
}
