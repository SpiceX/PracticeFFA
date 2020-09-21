<?php


namespace litek\pffa\task;


use litek\pffa\PracticeFFA;
use pocketmine\scheduler\Task;
use pocketmine\Player;

class EnderpearlCooldown extends Task
{

    /** @var int[] */
    private static $queue = [];
    /** @var Player[] */
    private static $players = [];
    /** @var PracticeFFA */
    private $plugin;

    /**
     * KitUseTimer constructor.
     * @param PracticeFFA $plugin
     */
    public function __construct(PracticeFFA $plugin) {
        $this->plugin = $plugin;
    }

    /**
     * @param Player $player
     * @return bool
     */
    public static function canUseItem(Player $player): bool {
        return !isset(self::$queue[$player->getName()]);
    }

    /**
     * @param Player $player
     */
    public static function addToQueue(Player $player): void
    {
        self::$queue[$player->getName()] = 0;
        self::$players[$player->getName()] = $player;
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick): void {
        foreach (self::$queue as $name => $tick) {
            $player = self::$players[$name];

            if($tick === 8) {
                unset(self::$queue[$player->getName()], self::$players[$player->getName()]);
                continue;
            }

            $progress = "§b";
            for($i = 0; $i < 8; $i++) {
                $progress .= ($i === $tick ? "§7||" : "||");
            }
            $player->sendPopup($progress);

            self::$queue[$name]++;
        }
    }
}