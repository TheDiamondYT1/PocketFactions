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
use pocketmine\lang\BaseLang;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat as TF;

use TheDiamondYT\PocketFactions\provider\Provider;
use TheDiamondYT\PocketFactions\provider\YamlProvider;
use TheDiamondYT\PocketFactions\provider\SQLiteProvider;
use TheDiamondYT\PocketFactions\commands\FCommandManager;
use TheDiamondYT\PocketFactions\listeners\FPlayerListener;
use TheDiamondYT\PocketFactions\struct\Relation;

class PF extends PluginBase {

    private $provider;
    private $language = null;
    private $cfg;
    
    private $fcommandManager;
    
    private $factions = [];
    
    public static $object = null;
   
    public function onLoad() {
        self::$object = $this;
    }
    
    /**
     * @return this
     */
    public static function get() {
        return self::$object;
    }
   
    /**
     * Log a message to the console.
     * WHY DID I MAKE THIS STATIC LOL
     *
     * @param string
     */
    public static function log(string $text) {
        Server::getInstance()->getLogger()->info("§b[§dPocketFactions§b]§e $text");
    }

	public function onEnable() {
	    $startTime = microtime(true);
	    $this->saveResource("config.yml");
	    $this->cfg = yaml_parse_file($this->getDataFolder() . "config.yml");
	    $this->language = new BaseLang($this->cfg["language"], $this->getFile() . "resources/lang/");
	    $this->fcommandManager = new FCommandManager($this);
	    
	    $this->getServer()->getCommandMap()->register(FCommandManager::class, $this->fcommandManager);
	    $this->getServer()->getPluginManager()->registerEvents(new FPlayerListener($this), $this);

	    $this->setProvider();
	    $this->provider->loadFactions();
	    $this->provider->loadPlayers();
	    self::log($this->translate("console.data.loaded", [round(microtime(true) - $startTime, 2), round(microtime(true) * 1000) - round($startTime * 1000)]));
	}
	
	private function setProvider() {
	    switch($this->cfg["provider"]) { 
	        case "sqlite":
	            if(!extension_loaded("sqlite3")) {
	                self::log("Unable to find the SQLite3 exstension. Setting data provider to yaml.");
	                $this->provider = new YamlProvider($this);
	                return;
	            }
	            $provider = new SQLiteProvider($this);
	            break;
	        case "yaml":
	            $provider = new YamlProvider($this);
	            break;
	        default:
	            $provider = new YamlProvider($this);
	            break;
	    }
	    // This check is to allow custom data providers in the future
	    if($provider instanceof Provider) 
	        $this->provider = $provider;
	}
	
	public function getCommandManager(): FCommandManager {
	    return $this->fcommandManager;
	}
	
	public function getProvider() {
	    return $this->provider;
	}
	
	public function getConfig() {
	    return $this->cfg;
	}
	
	public function getFaction(string $faction) {      
	    return $this->provider->getFaction($faction);
	}
	
	/**
	 * TODO: im bad at docs :/
	 *
	 * @param CommandSender|Player
	 * @return FPlayer|null
	 */
	public function getPlayer($player) {
	    if($player instanceof Player)
	        return $this->provider->getPlayer($player);
	        
	    return null;
	} 
	
	/**
	 * @return bool 
	 */
	public function factionExists(string $faction) {
	    return $this->provider->factionExists($faction);
	}
	
	/**
	 * Translates a message.
	 *
	 * @param string 
	 * @param array 
	 */
	public function translate(string $text, array $params = []) {
	    return $this->language->translateString($text, $params, null); 
	}
	
	/**
	 * TODO: remove
	 */
	public function getColorTo($me, $him) {
        return Relation::getColorTo($me, $him);
    }
   
    // TODO: move to Relation and fix format
    public function describeTo($thing, FPlayer $me) {
        $colour = TF::YELLOW;
        if($thing instanceof Faction) {
            if($thing === $me->getFaction()) {
                $text = "your faction";
            } else {
                $text = $thing->getTag();
            }
            return $this->getColorTo($me, $thing) . $text . $colour;
        }
        elseif($thing instanceof FPlayer) {
            if($thing === $me) {
                $text = "you";
            }
            elseif($thing->getFaction() === $me->getFaction()) {
                $text = TF::GREEN . $me->getNameAndTitle($thing); 
            } else {
                $text = $me->getName();
            }
            return $this->getColorTo($me, $thing->getFaction()) . $text . $colour;
        }
        return TF::RED . "unknown" . TF::YELLOW;
    }
}
