<?php


namespace litek\pffa\task;


use litek\pffa\PracticeFFA;
use litek\pffa\utils\Padding;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\Config;

class UpdateBossbar extends Task
{
    /**@var bool|mixed */
    private $comboLevel;
    /**@var bool|mixed */
    private $nodebuffLevel;
    /**@var bool|mixed */
    private $gappleLevel;

    public function __construct()
    {
        $this->comboLevel = (new Config(PracticeFFA::getInstance()->getDataFolder() . 'arenas/' . 'combo.yml', Config::YAML))->get('arena_name');
        $this->nodebuffLevel = (new Config(PracticeFFA::getInstance()->getDataFolder() . 'arenas/' . 'nodebuff.yml', Config::YAML))->get('arena_name');
        $this->gappleLevel = (new Config(PracticeFFA::getInstance()->getDataFolder() . 'arenas/' . 'gapple.yml', Config::YAML))->get('arena_name');
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick): void
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
            switch ($onlinePlayer->getLevel()->getFolderName()) {
                case $this->nodebuffLevel:
                case $this->comboLevel:
                case $this->gappleLevel:
                    PracticeFFA::getInstance()->getBossbar()->updateFor($onlinePlayer, Padding::centerLine("§9§l» Kings §fNetwork «§9\n\n§r§9Ping: §7" . $onlinePlayer->getPing() . "  §9Cps: §7" . PracticeFFA::getInstance()->getCpsCounter()->getCps($onlinePlayer) . "  §9In Arena: §7" . count($onlinePlayer->getLevel()->getPlayers())));
                    break;
                default:
                    PracticeFFA::getInstance()->getBossbar()->hideFrom($onlinePlayer);
                    break;
            }
        }
    }
}