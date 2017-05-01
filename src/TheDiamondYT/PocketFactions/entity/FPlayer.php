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
 
namespace TheDiamondYT\PocketFactions\entity;

use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;

use TheDiamondYT\PocketFactions\PF;
use TheDiamondYT\PocketFactions\struct\Role;
use TheDiamondYT\PocketFactions\struct\ChatMode;
use TheDiamondYT\PocketFactions\util\RelationUtil;
use TheDiamondYT\PocketFactions\util\RelationParticipator;
use TheDiamondYT\PocketFactions\util\TextUtil;

/**
 * Represents a faction player.
 */
class FPlayer implements IPlayer, RelationParticipator {

    /* @var Player */
    private $player;
    
    /* @var PF */
    private $plugin;
    
    /* @var array */
    private $data;
    
    /* @var Faction */
    private $faction;
    
    /* @var int */  
    private $chatMode = ChatMode::PUBLIC; 
 
    /* @var bool */
    private $adminBypassing = false;
    
    public function __construct(PF $plugin, Player $player, array $data) {
        $this->plugin = $plugin;
        $this->player = $player;
        $this->data = $data;
    }
    
    /**
     * Returns the player.
     *
     * @return Player
     */
    public function getPlayer(): Player {
        return $this->player;
    }
    
    /**
     * Returns the players name.
     *
     * @return string
     */
    public function getName(): string {
        return $this->data["name"];
    }
    
    /**
     * @return int
     */
    public function getChatMode(): int {
        return $this->chatMode;
    }
    
    /**
     * Sets the players chat mode.
     *
     * @param int
     */
    public function setChatMode(int $mode) {
        if(ChatMode::byName($mode) === "unknown")
            throw new \Exception("Invalid chat mode '$mode'");
            
        $this->chatMode = $mode;
    }
    
    /**
     * Return the players role prefix.
     *
     * @return string
     */
    public function getPrefix(): string { 
        if($this->getRole() === Role::get("Leader")) {
            return "**";
        } elseif($this->getRole() === Role::get("Moderator")) {
            return "*";
        } else {
            return "";
        }
    }
    
    /** 
     * Sets the players title in the faction.
     *
     * @param string
     */
    public function setTitle(string $title) {
        $this->data["faction"]["title"] = $title;
        $this->update();
    }
  
    /**
     * Return the players title.
     *
     * @return string
     */
    public function getTitle(): string {
        return $this->data["faction"]["title"];
    }
    
    /**
     * Return the players prefix, name and title.
     *
     * @return string
     */
    public function getNameAndTitle(): string {
        return $this->getPrefix() . ($this->getTitle() === "" ?? $this->getPrefix()) . " " . $this->getName();
    }
    
    /**
     * Return the players prefix and name.
     *
     * @return string
     */
    public function getNameAndPrefix(): string {
        return $this->getPrefix() . " " . $this->getName();
    }
    
    public function describeTo(RelationParticipator $that, bool $ucfirst = false) {
        return RelationUtil::describeThatToMe($this, $that, $ucfirst);
    }
    
    public function getColorTo(RelationParticipator $that) {
        return RelationUtil::getColorToMe($that, $this);
    }
    
    /**
     * Sends a message to the player.
     *
     * @param string
     */
    public function sendMessage(string $text) {
        if($this->player !== null)
            $this->player->sendMessage(TextUtil::parse($text));
    }
    
    /**
     * Sets the players role in their faction
     *
     * @param int 
     */
    public function setRole(int $role) {
        //if(!Role::exists($role))
        //    throw new \Exception("Tried to set role for {$this->getName()} to $role, but role doesnt exist.");
        
        $this->data["faction"]["role"] = $role;
        $this->update();
    }
    
    /**
     * @return int
     */
    public function getRole(): int {
        return $this->data["faction"]["role"];
    }
    
    /**
     * Toggle admin bypass mode.
     *
     * @param bool 
     */
    public function setAdminBypassing(bool $value) {
        $this->adminBypassing = $value;
    }
    
    /**
     * @return bool
     */
    public function isAdminBypassing(): bool {
        return $this->adminBypassing;
    }
    
    /**
     * Returns true if the player is leader.
     *
     * @return bool
     */
    public function isLeader(): bool {
        return $this->getRole() === Role::get("Leader");
    }
    
    /**
     * Sets the players faction;
     *
     * @param Faction 
     */
    public function setFaction(Faction $faction) { 
        if($this->faction !== null)
            $this->leaveFaction();
            
        $faction->addPlayer($this); 
        $this->faction = $faction;    
        $this->data["faction"]["id"] = $faction->getId();
        $this->update();
    }
    
    /**
     * Leave the current faction.
     */
    public function leaveFaction() {
        $this->faction->removePlayer($this);
        $this->faction = null;
    }
    
    /**
     * Returns true if the player is in a faction.
     *
     * @return bool
     */
    public function hasFaction(): bool {
        return $this->faction !== null;
    }
    
    /**
     * @return Faction|null
     */
    public function getFaction() {     
        return $this->faction ?? PF::getInstance()->getFaction("Wilderness");
    }
    
    public function update() {
        PF::getInstance()->getProvider()->updatePlayer($this->data);
    }
}
