<?php

namespace litek\pffa\provider;

use pocketmine\Player;
use SQLite3;

class SQLiteProvider extends SQLite3
{

    /**
     * BetterSQLite3 constructor.
     * @param string $filename
     */
    public function __construct(string $filename)
    {
        parent::__construct($filename);
        $this->busyTimeout(5000);
        $this->createTables();
    }

    /**
     * @query create table of Games
     */
    protected function createTables(): void
    {
        $this->exec('CREATE TABLE IF NOT EXISTS NODEBUFF (
            NAME TEXT NOT NULL,
            KILLS INT NOT NULL,
            DEATHS INT NOT NULL,
            UNIQUE(NAME)
        )');
        $this->exec('CREATE TABLE IF NOT EXISTS GAPPLE (
            NAME TEXT NOT NULL,
            KILLS INT NOT NULL,
            DEATHS INT NOT NULL,
            UNIQUE(NAME)
        )');
        $this->exec('CREATE TABLE IF NOT EXISTS COMBO (
            NAME TEXT NOT NULL,
            KILLS INT NOT NULL,
            DEATHS INT NOT NULL,
            UNIQUE(NAME)
        )');
    }

    /**
     * @param Player $player
     */
    public function addPlayer(Player $player): void
    {
        $player->sendMessage("§b§l» §r§7Hey, {$player->getName()}, is your first game!");
        $player->sendMessage("§9§l» §r§7We are adding you to the database to follow your progress in your battles...");
        $query2 = $this->prepare("INSERT OR IGNORE INTO NODEBUFF(NAME,KILLS,DEATHS) SELECT :name, :kills, :deaths WHERE NOT EXISTS(SELECT * FROM NoDebuff WHERE NAME = :name);");
        $query3 = $this->prepare("INSERT OR IGNORE INTO GAPPLE(NAME,KILLS,DEATHS) SELECT :name, :kills, :deaths WHERE NOT EXISTS(SELECT * FROM Gapple WHERE NAME = :name);");
        $query4 = $this->prepare("INSERT OR IGNORE INTO COMBO(NAME,KILLS,DEATHS) SELECT :name, :kills, :deaths WHERE NOT EXISTS(SELECT * FROM Combo WHERE NAME = :name);");
        $query2->bindValue(":name", $player->getName(), SQLITE3_TEXT);
        $query2->bindValue(":kills", 0, SQLITE3_NUM);
        $query2->bindValue(":deaths", 0, SQLITE3_NUM);
        $query3->bindValue(":name", $player->getName(), SQLITE3_TEXT);
        $query3->bindValue(":kills", 0, SQLITE3_NUM);
        $query3->bindValue(":deaths", 0, SQLITE3_NUM);
        $query4->bindValue(":name", $player->getName(), SQLITE3_TEXT);
        $query4->bindValue(":kills", 0, SQLITE3_NUM);
        $query4->bindValue(":deaths", 0, SQLITE3_NUM);
        $query2->execute();
        $query3->execute();
        $query4->execute();
    }

    /**
     * Close database
     */
    public function closeDatabase(): void
    {
        $this->close();
    }

    /**
     * @param Player $player
     * @return string
     */
    public function getScore(Player $player): string
    {
        $summary = $this->getSummary($player);
        return "§b§l» §r§bPractice Summary Score" . "\n§r" .
            "§9Player " . "§7: " . "{$player->getName()}\n" .
            "§3NoDebuff Kills " . "§7: " . "§7{$summary['NODEBUFF']['kills']}\n" .
            "§3Gapple Kills " . "§7: " . "§7{$summary['GAPPLE']['kills']}\n" .
            "§3Combo Kills " . "§7: " . "§7{$summary['COMBO']['kills']}\n" .
            "§6--------------------" . "\n" .
            "§3NoDebuff Deaths " . "§7: " . "§7{$summary['NODEBUFF']['deaths']}\n" .
            "§3Gapple Deaths " . "§7: " . "§7{$summary['GAPPLE']['deaths']}\n" .
            "§3Combo Deaths " . "§7: " . "§7{$summary['COMBO']['deaths']}\n" .
            "§6--------------------";
    }

    /**
     * @param Player $player
     * @return array[]
     */
    public function getSummary(Player $player): array
    {
        return [
            'NODEBUFF' => ['kills' => $this->getKills($player, "NODEBUFF"), 'deaths' => $this->getDeaths($player, "NODEBUFF")],
            'GAPPLE' => ['kills' => $this->getKills($player, "GAPPLE"), 'deaths' => $this->getDeaths($player, "GAPPLE")],
            'COMBO' => ['kills' => $this->getKills($player, "COMBO"), 'deaths' => $this->getDeaths($player, "COMBO")],
        ];
    }

    /**
     * @param Player $player
     * @param string $gameType
     * @return int
     */
    public function getKills(Player $player, string $gameType): int
    {
        $name = $player->getName();
        if (!$this->verifyPlayerInDB($player)){
            return 0;
        }
        switch ($gameType) {
            case "NODEBUFF":
                return $this->querySingle("SELECT KILLS FROM NODEBUFF WHERE NAME = '$name'");
            case "GAPPLE":
                return $this->querySingle("SELECT KILLS FROM GAPPLE WHERE NAME = '$name'");
            case "COMBO":
                return $this->querySingle("SELECT KILLS FROM COMBO WHERE NAME = '$name'");
        }
        return 0;
    }

    /**
     * @param Player $player
     * @param string $gameType
     * @return int
     */
    public function getDeaths(Player $player, string $gameType): int
    {
        $name = $player->getName();
        if (!$this->verifyPlayerInDB($player)){
            return 0;
        }
        switch ($gameType) {
            case "NODEBUFF":
                return (int)$this->querySingle("SELECT DEATHS FROM NODEBUFF WHERE NAME = '$name'");
            case "GAPPLE":
                return (int)$this->querySingle("SELECT DEATHS FROM GAPPLE WHERE NAME = '$name'");
            case "COMBO":
                return (int)$this->querySingle("SELECT DEATHS FROM COMBO WHERE NAME = '$name'");
        }
        return 0;
    }

    /**
     * @param Player $player
     * @param string $gametype
     * @return float|int
     */
    public function getKDR(Player $player, string $gametype)
    {
        $kills = $this->getKills($player, $gametype);
        $deaths = $this->getDeaths($player, $gametype);
        if ($deaths === 0 || $kills === 0) {
            return 0;
        }
        return round($kills / $deaths);
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function verifyPlayerInDB(Player $player): bool
    {
        $name = $player->getName();
        $query = $this->querySingle("SELECT NAME FROM NODEBUFF WHERE NAME = '$name'");
        return !($query === null);
    }

    /**
     * Configure leaderboard
     * @param string $gameType
     * @return string
     */
    public function getGlobalTops(string $gameType): string
    {
        $leaderboard = [];
        $result = null;
        switch ($gameType) {
            case "COMBO":
                $result = $this->query("SELECT NAME, KILLS FROM COMBO ORDER BY KILLS DESC LIMIT 10");
                break;
            case "GAPPLE":
                $result = $this->query("SELECT NAME, KILLS FROM GAPPLE ORDER BY KILLS DESC LIMIT 10");
                break;
            case "NODEBUFF":
                $result = $this->query("SELECT NAME, KILLS FROM NODEBUFF ORDER BY KILLS DESC LIMIT 10");
                break;
        }
        if ($result === null) {
            return '';
        }
        $index = 0;
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $leaderboard[$index++] = $row;
        }
        $count = count($leaderboard);
        $break = "\n";
        if ($count > 0) {
            $top1 = "§e1. §6Name: §a" . $leaderboard[0]['NAME'] . "  §6Kills: §a" . $leaderboard[0]['KILLS'];
        } else {
            $top1 = '';
        }
        if ($count > 1) {
            $top2 = "§e2. §6Name: §e" . $leaderboard[1]['NAME'] . "  §6Kills: §e" . $leaderboard[1]['KILLS'];
        } else {
            $top2 = '';
        }
        if ($count > 2) {
            $top3 = "§e3. §6Name: §e" . $leaderboard[2]['NAME'] . "  §6Kills: §e" . $leaderboard[2]['KILLS'];
        } else {
            $top3 = '';
        }
        if ($count > 3) {
            $top4 = "§e4. §6Name: §e" . $leaderboard[3]['NAME'] . "  §6Kills: §e" . $leaderboard[3]['KILLS'];
        } else {
            $top4 = '';
        }
        if ($count > 4) {
            $top5 = "§e5. §6Name: §e" . $leaderboard[4]['NAME'] . "  §6Kills: §e" . $leaderboard[4]['KILLS'];
        } else {
            $top5 = '';
        }
        if ($count > 5) {
            $top6 = "§e6. §6Name: §e" . $leaderboard[5]['NAME'] . "  §6Kills: §e" . $leaderboard[5]['KILLS'];
        } else {
            $top6 = '';
        }
        if ($count > 6) {
            $top7 = "§e7. §6Name: §e" . $leaderboard[6]['NAME'] . "  §6Kills: §e" . $leaderboard[6]['KILLS'];
        } else {
            $top7 = '';
        }
        if ($count > 7) {
            $top8 = "§e8. §6Name: §e" . $leaderboard[7]['NAME'] . "  §6Kills: §e" . $leaderboard[7]['KILLS'];
        } else {
            $top8 = '';
        }
        if ($count > 8) {
            $top9 = "§e9. §6Name: §e" . $leaderboard[8]['NAME'] . "  §6Kills: §e" . $leaderboard[8]['KILLS'];
        } else {
            $top9 = '';
        }
        if ($count > 9) {
            $top10 = "§e10. §6Name: §e" . $leaderboard[9]['NAME'] . "  §6Kills: §e" . $leaderboard[9]['KILLS'];
        } else {
            $top10 = '';
        }
        return "§b" . ucfirst($gameType) . " Leaderboard" . "\n" . "§9" . "§aTop Kills" . "\n" . $top1 . $break . $top2 . $break . $top3 . $break . $top4 . $break . $top5 . $break . $top6 . $break . $top7 . $break . $top8 . $break . $top9 . $break . $top10;
    }

    /**
     * @param Player $player
     * @param string $gametype
     */
    public function addKill(Player $player, string $gametype): void
    {
        $name = $player->getName();
        switch ($gametype) {
            case "NODEBUFF":
                $result = $this->getKills($player, $gametype) + 1;
                $this->exec("UPDATE `NODEBUFF` SET `KILLS`='$result' WHERE NAME='$name';");
                break;
            case "COMBO":
                $result = $this->getKills($player, $gametype) + 1;
                $this->exec("UPDATE `COMBO` SET `KILLS`='$result' WHERE NAME='$name';");
                break;
            case "GAPPLE":
                $result = $this->getKills($player, $gametype) + 1;
                $this->exec("UPDATE `GAPPLE` SET `KILLS`='$result' WHERE NAME='$name';");
                break;
        }
    }

    /**
     * @param Player $player
     * @param string $gametype
     */
    public function addDeath(Player $player, string $gametype): void
    {
        $name = $player->getName();
        switch ($gametype) {
            case "NODEBUFF":
                $result = $this->getDeaths($player, $gametype) + 1;
                $this->exec("UPDATE `NODEBUFF` SET `DEATHS`='$result' WHERE NAME='$name';");
                break;
            case "COMBO":
                $result = $this->getDeaths($player, $gametype) + 1;
                $this->exec("UPDATE `COMBO` SET `DEATHS`='$result' WHERE NAME='$name';");
                break;
            case "GAPPLE":
                $result = $this->getDeaths($player, $gametype) + 1;
                $this->exec("UPDATE `GAPPLE` SET `DEATHS`='$result' WHERE NAME='$name';");
                break;
        }
    }
}

