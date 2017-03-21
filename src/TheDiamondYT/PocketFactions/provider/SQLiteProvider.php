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
 
namespace TheDiamondYT\PocketFactions\provider;

use TheDiamondYT\PocketFactions\PF;
use TheDiamondYT\PocketFactions\Faction;

class SQLiteProvider implements Provider {

    private $plugin;
    private $db;
    
    private $factions = [];

    public function __construct(PF $plugin) {
        $this->plugin = $plugin;
        $this->db = new \SQLite3($plugin->getDataFolder() . "Factions.db");
    }
    
    public function loadFactions() {
    
    }
    
    public function getFaction($faction) {
    
    }
    
    public function createFaction(Faction $faction) {
        // You should have your own checks using SQLiteProvider::factionExists($tag)
        // before calling this function.
        if($this->factionExists($faction->getId()))
            throw new \Exception("Error while creating faction: faction exists.");
    }
    
    public function disbandFaction(Faction $faction) {
    
    }
    
    public function setFactionTag($tag) {
    
    }
   
    public function factionExists($faction) {
        
    }
}