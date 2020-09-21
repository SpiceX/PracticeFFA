<?php


namespace litek\pffa\task;


use Exception;
use litek\pffa\PracticeFFA;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\Config;

class UpdateScoreboard extends Task
{
    /**@var bool|mixed */
    private $COMBOLevel;
    /**@var bool|mixed */
    private $NODEBUFFLevel;
    /**@var bool|mixed */
    private $GAPPLELevel;

    public function __construct()
    {
        $this->COMBOLevel = (new Config(PracticeFFA::getInstance()->getDataFolder() . 'arenas/' . 'COMBO.yml', Config::YAML))->get('arena_name');
        $this->NODEBUFFLevel = (new Config(PracticeFFA::getInstance()->getDataFolder() . 'arenas/' . 'NODEBUFF.yml', Config::YAML))->get('arena_name');
        $this->GAPPLELevel = (new Config(PracticeFFA::getInstance()->getDataFolder() . 'arenas/' . 'GAPPLE.yml', Config::YAML))->get('arena_name');
    }

    /**
     * @param int $currentTick
     * @throws Exception
     */
    public function onRun(int $currentTick): void
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
            if (isset(PracticeFFA::getInstance()->scoreboards[$onlinePlayer->getName()])) {
                $scoreboard = PracticeFFA::getInstance()->scoreboards[$onlinePlayer->getName()];
                switch ($onlinePlayer->getLevel()->getFolderName()) {
                    case $this->NODEBUFFLevel:
                        if (!$scoreboard->isSpawned()) {
                            $scoreboard->spawn("§9§l»§r §3No Debuff §9§l«");
                        }
                        $lines = [
                            "§8----------",
                            "§8| §3Kills: §7" . PracticeFFA::getInstance()->getSqliteProvider()->getKills($scoreboard->getOwner(), "NODEBUFF"),
                            "§8| §3Deaths: §7" . PracticeFFA::getInstance()->getSqliteProvider()->getDeaths($scoreboard->getOwner(), "NODEBUFF"),
                            "§8| §3KDR: §7" . PracticeFFA::getInstance()->getSqliteProvider()->getKDR($scoreboard->getOwner(), "NODEBUFF"),
                            "§8----------",
                        ];
                        foreach ($lines as $index => $line) {
                            $scoreboard->setScoreLine(++$index, $line);
                        }
                        break;
                    case $this->COMBOLevel:
                        if (!$scoreboard->isSpawned()) {
                            $scoreboard->spawn("§9§l»§r §3Combo §9§l«");
                        }
                        $lines = [
                            "§8----------",
                            "§8| §3Kills: §7" . PracticeFFA::getInstance()->getSqliteProvider()->getKills($scoreboard->getOwner(), "COMBO"),
                            "§8| §3Deaths: §7" . PracticeFFA::getInstance()->getSqliteProvider()->getDeaths($scoreboard->getOwner(), "COMBO"),
                            "§8| §3KDR: §7" . PracticeFFA::getInstance()->getSqliteProvider()->getKDR($scoreboard->getOwner(), "COMBO"),
                            "§8----------",
                        ];
                        foreach ($lines as $index => $line) {
                            $scoreboard->setScoreLine(++$index, $line);
                        }
                        break;
                    case $this->GAPPLELevel:
                        if (!$scoreboard->isSpawned()) {
                            $scoreboard->spawn("§9§l»§r §3Gapple §9§l«");
                        }
                        $lines = [
                            "§8----------",
                            "§8| §3Kills: §7" . PracticeFFA::getInstance()->getSqliteProvider()->getKills($scoreboard->getOwner(), "GAPPLE"),
                            "§8| §3Deaths: §7" . PracticeFFA::getInstance()->getSqliteProvider()->getDeaths($scoreboard->getOwner(), "GAPPLE"),
                            "§8| §3KDR: §7" . PracticeFFA::getInstance()->getSqliteProvider()->getKDR($scoreboard->getOwner(), "GAPPLE"),
                            "§8----------",
                        ];
                        foreach ($lines as $index => $line) {
                            $scoreboard->setScoreLine(++$index, $line);
                        }
                        break;
                    default:
                        $scoreboard->despawn();
                        break;
                }
            }
        }
    }
}