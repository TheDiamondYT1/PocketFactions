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
 
namespace TheDiamondYT\PocketFactions\command;

use pocketmine\command\CommandSender;

use TheDiamondYT\PocketFactions\PF;
use TheDiamondYT\PocketFactions\entity\IPlayer;

class CommandReload extends FCommand {

    public function __construct(PF $plugin) {
        parent::__construct($plugin, "reload", $plugin->translate("commamds.reload.description"));
    }

    public function perform(IPlayer $fme, array $args) {
        $startTime = microtime(true);
        $this->plugin->reloadConfig();
        $this->plugin->getProvider()->loadFactions();
        $this->plugin->getProvider()->loadPlayers();
        $this->plugin->getProvider()->save();
        $this->msg($this->plugin->translate("commands.reload.success", [round(microtime(true) - $startTime, 2), round(microtime(true) * 1000) - round($startTime * 1000)]));
    }
}
