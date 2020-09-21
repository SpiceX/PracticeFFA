<?php

namespace litek\pffa;

use litek\pffa\task\EnderpearlCooldown;
use litek\pffa\utils\Scoreboard;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\utils\Config;

class PracticeListener implements Listener
{
    /**@var PracticeFFA */
    private $plugin;

    /**
     * PracticeListener constructor.
     * @param PracticeFFA $plugin
     */
    public function __construct(PracticeFFA $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        $scoreboard = new Scoreboard($player);
        $this->plugin->scoreboards[$player->getName()] = $scoreboard;
    }

    public function onQuit(PlayerQuitEvent $event): void
    {
        if (isset($this->plugin->scoreboards[$event->getPlayer()->getName()])) {
            unset($this->plugin->scoreboards[$event->getPlayer()->getName()]);
        }
        $this->plugin->getBossbar()->hideFrom($event->getPlayer());
    }

    /**
     * @param PlayerInteractEvent $event
     */
    public function onUseItem(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();
        if ($event->getItem()->getId() === Item::SPLASH_POTION) {
            $player->setMotion($player->getDirectionVector()->add(0, 0.2)->multiply(0.9));
        }
        if ($event->getItem()->getId() === Item::ENDER_PEARL) {
            if (EnderpearlCooldown::canUseItem($player)) {
                EnderpearlCooldown::addToQueue($player);
            } else {
                $event->setCancelled();
            }
        }
    }

    public function onEntityDamage(EntityDamageEvent $event){
        $playerLevel = $event->getEntity()->getLevel()->getFolderName();
        if ($event->getEntity() instanceof Player){
            $comboLevel = (new Config($this->plugin->getDataFolder() . 'arenas/' . 'combo.yml', Config::YAML))->get('arena_name');
            if ($playerLevel === $comboLevel) {
                if ($event->getCause() === EntityDamageEvent::CAUSE_FALL){
                    $event->setCancelled();
                }
                if ($event->getCause() === EntityDamageEvent::CAUSE_CONTACT){
                    $event->setCancelled();
                }
            }
        }
    }

    public function onEntityDamageByEntity(EntityDamageByEntityEvent $event): void
    {
        $attacker = $event->getDamager();
        $victim = $event->getEntity();
        if ($attacker === $victim) {
            $event->setCancelled();
        }
        $comboLevel = (new Config($this->plugin->getDataFolder() . 'arenas/' . 'combo.yml', Config::YAML))->get('arena_name');
        $nodebuffLevel = (new Config($this->plugin->getDataFolder() . 'arenas/' . 'nodebuff.yml', Config::YAML))->get('arena_name');
        $gappleLevel = (new Config($this->plugin->getDataFolder() . 'arenas/' . 'gapple.yml', Config::YAML))->get('arena_name');
        if ($attacker instanceof Player && $victim instanceof Player) {
            $attackerLevel = $attacker->getLevel()->getFolderName();
            $victimLevel = $victim->getLevel()->getFolderName();
            if ($attackerLevel === $comboLevel && $victimLevel === $comboLevel) {
                $event->setKnockBack(0.5);
            }
            if ($event->getFinalDamage() > $victim->getHealth()) {
                if ($attackerLevel === $comboLevel && $victimLevel === $comboLevel) {
                    $event->setCancelled();
                    $this->plugin->getSqliteProvider()->addKill($attacker, 'COMBO');
                    $this->plugin->getSqliteProvider()->addDeath($victim, 'COMBO');
                    $attacker->sendMessage("§aKill +1");
                    $victim->sendTitle(" ", "§cDead   +1 Death");
                    $victim->addEffect(new EffectInstance(Effect::getEffect(Effect::BLINDNESS), 20));
                    $victim->teleport($victim->getLevel()->getSafeSpawn());
                    $victim->setHealth(20.0);
                    $victim->setFood(20.0);
                    $this->plugin->applyCombo($victim);
                }
                if ($attackerLevel === $nodebuffLevel && $victimLevel === $nodebuffLevel) {
                    $event->setCancelled();
                    $this->plugin->getSqliteProvider()->addKill($attacker, 'NODEBUFF');
                    $this->plugin->getSqliteProvider()->addDeath($victim, 'NODEBUFF');
                    $attacker->sendMessage("§aKill +1");
                    $victim->sendTitle(" ", "§cDead   +1 Death");
                    $victim->addEffect(new EffectInstance(Effect::getEffect(Effect::BLINDNESS), 20));
                    $victim->teleport($victim->getLevel()->getSafeSpawn());
                    $victim->setHealth(20.0);
                    $victim->setFood(20.0);
                    $this->plugin->applyNoDebuff($victim);
                }
                if ($attackerLevel === $gappleLevel && $victimLevel === $gappleLevel) {
                    $event->setCancelled();
                    $this->plugin->getSqliteProvider()->addKill($attacker, 'GAPPLE');
                    $this->plugin->getSqliteProvider()->addDeath($victim, 'GAPPLE');
                    $attacker->sendMessage("§aKill +1");
                    $victim->sendTitle(" ", "§cDead   +1 Death");
                    $victim->addEffect(new EffectInstance(Effect::getEffect(Effect::BLINDNESS), 20));
                    $victim->teleport($victim->getLevel()->getSafeSpawn());
                    $victim->setHealth(20.0);
                    $victim->setFood(20.0);
                    $this->plugin->applyGapple($victim);
                }
            }
        }
    }

    public function onDeath(PlayerDeathEvent $event): void
    {
        $comboLevel = (new Config($this->plugin->getDataFolder() . 'arenas/' . 'combo.yml', Config::YAML))->get('arena_name');
        $nodebuffLevel = (new Config($this->plugin->getDataFolder() . 'arenas/' . 'nodebuff.yml', Config::YAML))->get('arena_name');
        $gappleLevel = (new Config($this->plugin->getDataFolder() . 'arenas/' . 'gapple.yml', Config::YAML))->get('arena_name');
        $victimLevel = $event->getPlayer()->getLevel()->getFolderName();
        if ($victimLevel === $comboLevel) {
            $event->setDrops([]);
        }
        if ($victimLevel === $nodebuffLevel) {
            $event->setDrops([]);
        }
        if ($victimLevel === $gappleLevel) {
            $event->setDrops([]);
        }
    }
}