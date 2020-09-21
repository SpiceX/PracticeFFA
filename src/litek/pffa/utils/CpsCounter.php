<?php


namespace litek\pffa\utils;


use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\Player;

class CpsCounter implements Listener
{
    private const ARRAY_MAX_SIZE = 100;

    /** @var array[] */
    private $clicksData = [];

    public function initPlayerClickData(Player $p): void
    {
        $this->clicksData[$p->getLowerCaseName()] = [];
    }

    public function addClick(Player $p): void
    {
        array_unshift($this->clicksData[$p->getLowerCaseName()], microtime(true));
        if (count($this->clicksData[$p->getLowerCaseName()]) >= self::ARRAY_MAX_SIZE) {
            array_pop($this->clicksData[$p->getLowerCaseName()]);
        }
    }

    /**
     * @param Player $player
     * @param float $deltaTime Interval of time (in seconds) to calculate CPS in
     * @param int $roundPrecision
     * @return float
     */
    public function getCps(Player $player, float $deltaTime = 1.0, int $roundPrecision = 1): float
    {
        if (!isset($this->clicksData[$player->getLowerCaseName()]) || empty($this->clicksData[$player->getLowerCaseName()])) {
            return 0.0;
        }
        $ct = microtime(true);
        return round(count(array_filter($this->clicksData[$player->getLowerCaseName()], static function (float $t) use ($deltaTime, $ct) : bool {
                return ($ct - $t) <= $deltaTime;
            })) / $deltaTime, $roundPrecision);
    }

    public function removePlayerClickData(Player $p): void
    {
        unset($this->clicksData[$p->getLowerCaseName()]);
    }

    public function playerJoin(PlayerJoinEvent $e): void
    {
        $this->initPlayerClickData($e->getPlayer());
    }

    public function playerQuit(PlayerQuitEvent $e): void
    {
        $this->removePlayerClickData($e->getPlayer());
    }

    public function packetReceive(DataPacketReceiveEvent $e): void
    {
        if (
            isset($this->clicksData[$e->getPlayer()->getLowerCaseName()]) &&
            (
                ($e->getPacket()::NETWORK_ID === InventoryTransactionPacket::NETWORK_ID && $e->getPacket()->transactionType === InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY) ||
                ($e->getPacket()::NETWORK_ID === LevelSoundEventPacket::NETWORK_ID && $e->getPacket()->sound === LevelSoundEventPacket::SOUND_ATTACK_NODAMAGE) ||
                ($e->getPacket()::NETWORK_ID === PlayerActionPacket::NETWORK_ID && $e->getPacket()->action === PlayerActionPacket::ACTION_START_BREAK)
            )
        ) {
            $this->addClick($e->getPlayer());
        }
    }

}