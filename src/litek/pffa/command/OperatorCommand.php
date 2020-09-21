<?php

namespace litek\pffa\command;

use litek\pffa\entity\LeaderboardTops;
use litek\pffa\PracticeFFA;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\entity\Entity;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\utils\Config;

class OperatorCommand extends Command implements PluginIdentifiableCommand
{

    /** @var PracticeFFA */
    private $plugin;

    public function __construct(PracticeFFA $plugin)
    {
        parent::__construct("practice", "practice command", "/ptc help", ['ptc', 'practice']);
        $this->plugin = $plugin;
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return bool
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if ($sender instanceof Player && $sender->isOp()) {
            if (!isset($args[0])) {
                $sender->sendMessage("§b§l» §cUsage /practice help.");
                return false;
            }
            switch ($args[0]) {
                case 'test':
                    $this->plugin->getFormManager()->sendGamePanel($sender);
                    break;
                case 'help':
                    $sender->sendMessage("§b§l» §r§7Practice Setup Command §8(§7a1/1§8)\n" .
                        "§3/ptc create §7<arenaName> - Creates a new arena\n" .
                        "§3/ptc arenas §7 - See arena list\n" .
                        "§3/ptc remove §7<arenaName> - Remove an arena\n" .
                        "§3/ptc tops §7 - Place leaderboard\n");
                    break;
                case 'create':
                    if (!isset($args[1])) {
                        $sender->sendMessage("§c§l» §r§7/ptc create <arenaName> <gameType>");
                        break;
                    }
                    if (!in_array($args[2], ['nodebuff', 'combo', 'gapple'], true)) {
                        $sender->sendMessage("§c§l» §r§7There is not {$args[2]} game type available");
                        $sender->sendMessage("§c§l» §r§7Available game types: nodebuff, gapple, combo");
                        break;
                    }
                    if (!Server::getInstance()->isLevelGenerated($args[1])) {
                        $sender->sendMessage("§c§l» §r§7There is no level called §3{$args[1]}!");
                        break;
                    }
                    if (!Server::getInstance()->isLevelLoaded($args[1])) {
                        Server::getInstance()->loadLevel($args[1]);
                    }
                    $file = $this->getPlugin()->getDataFolder() . "arenas" . DIRECTORY_SEPARATOR . $args[2] . ".yml";
                    $config = new Config($file, Config::YAML);
                    $config->reload();
                    $config->set('arena_name', $args[1]);
                    $config->save();
                    $sender->sendMessage("§a> Arena for {$args[2]} successfully created: {$args[1]}");
                    break;
                case 'remove':
                    if (!isset($args[1])) {
                        $sender->sendMessage("§c§l»§r§7 Usage: §7/ptc remove <gametype>");
                        break;
                    }
                    if (!in_array($args[1], ['nodebuff', 'combo', 'gapple'], true)) {
                        $sender->sendMessage("§c§l» §r§7There is not {$args[2]} game type available");
                        $sender->sendMessage("§c§l» §r§7Available game types: nodebuff, gapple, combo");
                        break;
                    }
                    $file = $this->getPlugin()->getDataFolder() . "arenas" . DIRECTORY_SEPARATOR . $args[1] . ".yml";
                    @unlink($file);

                    $sender->sendMessage("§a§l»§r§7 Arena removed!");
                    break;
                case 'tops':
                    if (!isset($args[1]) || !in_array($args[1], ['nodebuff', 'combo', 'gapple'])) {
                        $sender->sendMessage("§c§l»§r§7 Usage: /ptc tops <nodebuff|combo|gapple>");
                        return false;
                    }
                    foreach ($sender->getLevel()->getEntities() as $entity) {
                        if ($entity instanceof LeaderboardTops && $entity->namedtag->getString("LeaderboardType") === strtoupper($args[1])) {
                            $entity->close();
                        }
                    }
                    $nbt = Entity::createBaseNBT($sender->asVector3());
                    $nbt->setTag($sender->namedtag->getCompoundTag("Skin"));
                    $nbt->setString("LeaderboardType", $args[1], true);
                    $status = new LeaderboardTops($sender->level, $nbt);
                    $status->spawnToAll();
                    break;
                default:
                    $sender->sendMessage("§c§l»§r§7 Usage: /practice help");
            }
        }
        return true;
    }

    public
    function getPlugin(): Plugin
    {
        return $this->plugin;
    }
}