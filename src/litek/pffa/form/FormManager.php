<?php

/**
 * Copyright 2020-2022 LiTEK - Josewowgame
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace litek\pffa\form;

use litek\pffa\form\elements\Button;
use litek\pffa\form\elements\Image;
use litek\pffa\form\types\MenuForm;
use litek\pffa\PracticeFFA;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;

class FormManager
{
    /**
     * @var PracticeFFA
     */
    private $plugin;

    public function __construct(PracticeFFA $plugin)
    {
        $this->plugin = $plugin;
    }

    public function sendGamePanel(Player $player): void
    {
        if (!$this->plugin->getSqliteProvider()->verifyPlayerInDB($player)) {
            $this->plugin->getSqliteProvider()->addPlayer($player);
        }
        $player->sendForm(new MenuForm("§b§lPractice Panel", "§7Select an option: ",
            [
                new Button("§9No Debuff\n§7Steak fight", new Image("textures/items/ender_pearl", Image::TYPE_PATH)),
                new Button("§9Gapple\n§7Golden apples!", new Image("textures/items/apple_golden", Image::TYPE_PATH)),
                new Button("§9Combo\n§7Beat your oponent.", new Image("textures/items/beef_cooked", Image::TYPE_PATH)),
            ], function (Player $player, Button $selected): void {
                switch ($selected->getValue()) {
                    case 0:
                        $arena = new Config($this->plugin->getDataFolder() . 'arenas/nodebuff.yml', Config::YAML);
                        $name = $arena->get('arena_name');
                        if ($name !== false) {
                            $level = Server::getInstance()->getLevelByName($name);
                            $this->plugin->applyNoDebuff($player);
                            $player->teleport($level->getSafeSpawn());
                            $this->plugin->getBossbar()->showTo($player);
                            if (isset($this->plugin->scoreboards[$player->getName()])) {
                                $this->plugin->scoreboards[$player->getName()]->spawn("§9§l»§r §3No Debuff §9§l«",);
                            }
                        } else {
                            $player->sendMessage("§c§l» §r§7There is not available arenas!");
                        }
                        break;
                    case 1:
                        $arena = new Config($this->plugin->getDataFolder() . 'arenas/gapple.yml', Config::YAML);
                        $name = $arena->get('arena_name');
                        if ($name !== false) {
                            $level = Server::getInstance()->getLevelByName($name);
                            $this->plugin->applyGapple($player);
                            $player->teleport($level->getSafeSpawn());
                            $this->plugin->getBossbar()->showTo($player);
                            if (isset($this->plugin->scoreboards[$player->getName()])) {
                                $this->plugin->scoreboards[$player->getName()]->spawn("§9§l»§r §3Gapple §9§l«",);
                            }
                        } else {
                            $player->sendMessage("§c§l» §r§7There is not available arenas!");
                        }
                        break;
                    case 2:
                        $arena = new Config($this->plugin->getDataFolder() . 'arenas/combo.yml', Config::YAML);
                        $name = $arena->get('arena_name');
                        if ($name !== false) {
                            $level = Server::getInstance()->getLevelByName($name);
                            $this->plugin->applyCombo($player);
                            $player->teleport($level->getSafeSpawn());
                            $this->plugin->getBossbar()->showTo($player);
                            if (isset($this->plugin->scoreboards[$player->getName()])) {
                                $this->plugin->scoreboards[$player->getName()]->spawn("§9§l»§r §3Combo §9§l«",);
                            }
                        } else {
                            $player->sendMessage("§c§l» §r§7There is not available arenas!");
                        }
                        break;
                }
            }));
    }
}