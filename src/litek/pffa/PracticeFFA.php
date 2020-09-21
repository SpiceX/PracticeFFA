<?php

namespace litek\pffa;

use litek\pffa\command\OperatorCommand;
use litek\pffa\entity\LeaderboardTops;
use litek\pffa\form\FormManager;
use litek\pffa\provider\SQLiteProvider;
use litek\pffa\provider\YamlProvider;
use litek\pffa\task\EnderpearlCooldown;
use litek\pffa\task\UpdateBossbar;
use litek\pffa\task\UpdateScoreboard;
use litek\pffa\utils\BossBar;
use litek\pffa\utils\CpsCounter;
use litek\pffa\utils\Scoreboard;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;

class PracticeFFA extends PluginBase
{
    public const DATABASE_FILENAME = "database.sq3";

    /** @var PracticeFFA */
    private static $instance;
    /**@var YamlProvider */
    private $yamlProvider;
    /**@var FormManager */
    private $formManager;
    /**@var SQLiteProvider */
    private $sqliteProvider;
    /** @var BossBar */
    private $bossbar;
    /** @var Scoreboard[] */
    public $scoreboards = [];
    /** @var CpsCounter */
    private $cpsCounter;

    public function onEnable(): void
    {
        $this->initVariables();
        $this->getServer()->getPluginManager()->registerEvents(new PracticeListener($this), $this);
        $this->getServer()->getPluginManager()->registerEvents($this->cpsCounter, $this);
        $this->getScheduler()->scheduleRepeatingTask(new UpdateBossbar(), 20);
        $this->getScheduler()->scheduleRepeatingTask(new UpdateScoreboard(), 20);
        $this->getScheduler()->scheduleRepeatingTask(new EnderpearlCooldown($this), 20);
        $this->getServer()->getCommandMap()->register('pffa', new OperatorCommand($this));
        Entity::registerEntity(LeaderboardTops::class, true);
        $comboLevel = (new Config($this->getDataFolder() . 'arenas/' . 'combo.yml', Config::YAML))->get('arena_name');
        $nodebuffLevel = (new Config($this->getDataFolder() . 'arenas/' . 'nodebuff.yml', Config::YAML))->get('arena_name');
        $gappleLevel = (new Config($this->getDataFolder() . 'arenas/' . 'gapple.yml', Config::YAML))->get('arena_name');
        if ($comboLevel !== false) {
            if (Server::getInstance()->isLevelGenerated($comboLevel)) {
                Server::getInstance()->loadLevel($comboLevel);
            }
        }
        if ($nodebuffLevel !== false) {
            if (Server::getInstance()->isLevelGenerated($nodebuffLevel)) {
                Server::getInstance()->loadLevel($nodebuffLevel);
            }
        }
        if ($gappleLevel !== false) {
            if (Server::getInstance()->isLevelGenerated($gappleLevel)) {
                Server::getInstance()->loadLevel($gappleLevel);
            }
        }
    }

    private function initVariables(): void
    {
        self::$instance = $this;
        $this->yamlProvider = new YamlProvider($this);
        $this->formManager = new FormManager($this);
        $this->sqliteProvider = new SQLiteProvider($this->getDataFolder() . self::DATABASE_FILENAME);
        $this->bossbar = new BossBar("", 1, 1);
        $this->cpsCounter = new CpsCounter();
    }

    public function applyNoDebuff(Player $player): void
    {
        $inventory = $player->getInventory();
        $armorInventory = $player->getArmorInventory();
        $inventory->clearAll();
        $player->addEffect(new EffectInstance(Effect::getEffect(Effect::REGENERATION), 72000));
        $player->addEffect(new EffectInstance(Effect::getEffect(Effect::SPEED), 72000));
        $helmet = Item::get(Item::DIAMOND_HELMET);
        $chestplate = Item::get(Item::DIAMOND_CHESTPLATE);
        $leggings = Item::get(Item::DIAMOND_LEGGINGS);
        $boots = Item::get(Item::DIAMOND_BOOTS);
        $helmet->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 5));
        $chestplate->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 5));
        $leggings->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 5));
        $boots->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 5));
        $diamondSword = Item::get(Item::DIAMOND_SWORD);
        $diamondSword->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::SHARPNESS)));
        $diamondSword->setCustomName("§l§dNoDebuff");
        $armorInventory->setHelmet($helmet);
        $armorInventory->setChestplate($chestplate);
        $armorInventory->setLeggings($leggings);
        $armorInventory->setBoots($boots);
        $inventory->addItem($diamondSword);
        $inventory->addItem(Item::get(Item::ENDER_PEARL, 0, 16));
        for ($i = 0, $max = $inventory->getSize(); $i < $max; $i++) {
            if ($inventory->isSlotEmpty($i)) {
                $inventory->addItem(Item::get(Item::SPLASH_POTION, 22));
            }
        }
    }

    public function applyGapple(Player $player): void
    {
        $inventory = $player->getInventory();
        $armorInventory = $player->getArmorInventory();
        $inventory->clearAll();
        $helmet = Item::get(Item::DIAMOND_HELMET);
        $chestplate = Item::get(Item::DIAMOND_CHESTPLATE);
        $leggings = Item::get(Item::DIAMOND_LEGGINGS);
        $boots = Item::get(Item::DIAMOND_BOOTS);
        $player->addEffect(new EffectInstance(Effect::getEffect(Effect::REGENERATION), 72000));
        $player->addEffect(new EffectInstance(Effect::getEffect(Effect::SPEED), 72000));
        $helmet->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 5));
        $chestplate->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 5));
        $leggings->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 5));
        $boots->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 5));
        $diamondSword = Item::get(Item::DIAMOND_SWORD);
        $diamondSword->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::SHARPNESS)));
        $diamondSword->setCustomName("§l§dNoDebuff");
        $armorInventory->setHelmet($helmet);
        $armorInventory->setChestplate($chestplate);
        $armorInventory->setLeggings($leggings);
        $armorInventory->setBoots($boots);
        $inventory->setItem(0, $diamondSword);
        $inventory->setItem(1, Item::get(Item::GOLDEN_APPLE, 0, 12));
        $inventory->setItem(2, Item::get(Item::STEAK, 0, 64));
        $inventory->setItem(7, Item::get(Item::BUCKET, 1, 1));
        $inventory->setItem(8, Item::get(Item::POTION, 15, 1));
    }

    public function applyCombo(Player $player): void
    {
        $inventory = $player->getInventory();
        $armorInventory = $player->getArmorInventory();
        $inventory->clearAll();
        $player->removeAllEffects();
        $helmet = Item::get(Item::IRON_HELMET);
        $chestplate = Item::get(Item::IRON_CHESTPLATE);
        $leggings = Item::get(Item::IRON_LEGGINGS);
        $boots = Item::get(Item::IRON_BOOTS);
        $helmet->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 5));
        $chestplate->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 5));
        $leggings->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 5));
        $boots->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 5));
        $armorInventory->setHelmet($helmet);
        $armorInventory->setChestplate($chestplate);
        $armorInventory->setLeggings($leggings);
        $armorInventory->setBoots($boots);
        $inventory->addItem(Item::get(Item::STEAK, 0, 64));
    }

    public function onDisable(): void
    {
        $this->sqliteProvider->closeDatabase();
    }

    /**
     * @return PracticeFFA
     */
    public static function getInstance(): PracticeFFA
    {
        return self::$instance;
    }

    /**
     * @return YamlProvider
     */
    public function getYamlProvider(): YamlProvider
    {
        return $this->yamlProvider;
    }

    /**
     * @return FormManager
     */
    public function getFormManager(): FormManager
    {
        return $this->formManager;
    }

    /**
     * @return SQLiteProvider
     */
    public function getSqliteProvider(): SQLiteProvider
    {
        return $this->sqliteProvider;
    }

    /**
     * @return BossBar
     */
    public function getBossbar(): BossBar
    {
        return $this->bossbar;
    }

    /**
     * @return CpsCounter
     */
    public function getCpsCounter(): CpsCounter
    {
        return $this->cpsCounter;
    }
}