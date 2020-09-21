<?php


namespace litek\pffa\entity;

use litek\pffa\PracticeFFA;
use pocketmine\entity\Human;
use pocketmine\entity\Skin;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;

class LeaderboardTops extends Human
{

    /** @var string */
    private $gameType;

    public function __construct(Level $level, CompoundTag $nbt)
    {
        parent::__construct($level, $nbt);
        $this->setSkin(new Skin('Standard_Custom', str_repeat("\x00", 8192), '', 'geometry.humanoid.custom'));
        $this->sendSkin();
        $this->gameType = $nbt->getString("LeaderboardType");
    }

    public function entityBaseTick(int $tickDiff = 1): bool
    {
        $this->setNameTag($this->getLeaderboardText());
        return parent::entityBaseTick($tickDiff);
    }

    private function getLeaderboardText(): string
    {
        return PracticeFFA::getInstance()->getSqliteProvider()->getGlobalTops($this->gameType);
    }
}