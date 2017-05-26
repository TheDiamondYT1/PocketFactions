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
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;

use TheDiamondYT\PocketFactions\PF;
use TheDiamondYT\PocketFactions\Faction;
use TheDiamondYT\PocketFactions\entity\IMember;
use TheDiamondYT\PocketFactions\struct\Relation;
use TheDiamondYT\PocketFactions\util\RoleUtil;

class CommandBypass extends FCommand {

    public function __construct(PF $plugin) {
        parent::__construct($plugin, "bypass", $plugin->translate("commands.bypass.description"));
    }
    
    public function getRequirements(): array {
        return [
            "player",
            "operator"
        ];
    }

    public function perform(IMember $fme, array $args) {
        if($fme->isAdminBypassing()) {
            $status = "disabled";
            $value = false;
        } else {
            $status = "enabled";
            $value = true;
        }
        $fme->setAdminBypassing($value);
        $this->msg($this->plugin->translate("commands.bypass.success", [$status]));
    }
}
