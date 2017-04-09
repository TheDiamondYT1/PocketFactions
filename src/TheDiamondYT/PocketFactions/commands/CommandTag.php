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
 
namespace TheDiamondYT\PocketFactions\commands;

use pocketmine\command\CommandSender;

use TheDiamondYT\PocketFactions\PF;
use TheDiamondYT\PocketFactions\FPlayer;
use TheDiamondYT\PocketFactions\struct\Role;
use TheDiamondYT\PocketFactions\struct\Relation;

class CommandTag extends FCommand {

    public function __construct(PF $plugin) {
        parent::__construct($plugin, "tag", $plugin->translate("commands.tag.description"));
        $this->setArgs("<tag>");
    }

    public function execute(CommandSender $sender, $fme, array $args) {
        //if(!$sender instanceof Player) {
        //    $this->msg($sender, $this->plugin->translate("commands.only-player"));
        //    return;
        //}
        if($fme->getFaction() === null) {
            $this->msg($sender, $this->plugin->translate("player.no-faction"));
            return;
        }
        if(!$fme->isLeader()) {
            $this->msg($sender, $this->plugin->translate("player.only-leader"));
            return;
        }
        if(empty($args)) {
            $this->msg($sender, $this->getUsage());
            return;
        }
        if(strlen($args[0]) > $this->cfg["faction"]["tag"]["maxLength"]) {
            $this->msg($sender, $this->plugin->translate("faction.tag.too-long"));
            return;
        }
        
        $fme->getFaction()->setTag($args[0]);
        
        foreach($this->plugin->getProvider()->getOnlinePlayers() as $player)
            $this->msg($sender, $this->plugin->translate("commands.tag.success", [Relation::describeToPlayer($fme, $player), Relation::describeToFaction($fme, $player), $args[0]]));
    }
}

