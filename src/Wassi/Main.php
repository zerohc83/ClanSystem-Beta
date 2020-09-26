<?php

namespace Wassi;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginDescription;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use oneborn\economyapi\EconomyAPI;
class Main extends PluginBase
{


    public function onEnable(): void
    {
		$this->clanCommand = new ClanCommands($this);
        $this->getServer()->getLogger()->info(TextFormat::AQUA . "Zero Cool.");
		$this->economy = Server::getInstance()->getPluginManager()->getPlugin("EconomyAPI");
		if (!$this->economy) {
	        $this->getLogger()->info("Add EconomyAPI to use the clan create or u might get errors.");
		}
$this->db = new \SQLite3($this->getDataFolder() . "Clans.db");
		$this->db->exec("CREATE TABLE IF NOT EXISTS master (player TEXT PRIMARY KEY COLLATE NOCASE, clans TEXT, rank TEXT);");
		$this->db->exec("CREATE TABLE IF NOT EXISTS money (clans TEXT PRIMARY KEY COLLATE NOCASE, bal INT);");
		$this->db->exec("CREATE TABLE IF NOT EXISTS level (clans TEXT PRIMARY KEY COLLATE NOCASE, lvl INT);");
		
        $this->db = new \SQLite3($this->getDataFolder() . "Clans.db");
        $this->db->exec("CREATE TABLE IF NOT EXISTS master (player TEXT PRIMARY KEY COLLATE NOCASE, clans TEXT, rank TEXT);");
		$this->db->exec("CREATE TABLE IF NOT EXISTS money (clans TEXT PRIMARY KEY COLLATE NOCASE, bal INT);");
		$this->db->exec("CREATE TABLE IF NOT EXISTS level (clans TEXT PRIMARY KEY COLLATE NOCASE, lvl INT);");
		         $this->prefs = new Config($this->getDataFolder() . "Prefs.yml", CONFIG::YAML, array(
            "MaxClanNameLength" => 10,
            "MaxPlayersPerClan" => 10,
			"MaxLevelPerClan" => 150,
			"Startinglevel" => 1,
			"Startingbalance" => 0,
			"Costtomakeclan" => 1,
			"CreateFormTitle" => "Create title",
			"CreateFormLabel" => "Create label",
			"DeleteFormTitle" => "delete title",
			"DeleteFormLabel" => "delete label",
			"InviteFormTitle" => "invite title",
			"InviteFormLabel" => "invite label \nyes",
		));
    }

public function onCommand(CommandSender $sender, Command $command, string $label, array $args) :bool {
        return $this->clanCommand->onCommand($sender, $command, $label, $args);
    }
	///////////clan///////////
	public function getPlayerClan($player) {
        $clans = $this->db->query("SELECT clans FROM master WHERE player='$player';");
        $clanArray = $clans->fetchArray(SQLITE3_ASSOC);
        return $clanArray["clans"];
    }
	public function clanExists($clans) {
        $lowercaseclans = strtolower($clans);
		$result = $this->db->query("SELECT clans FROM master WHERE lower(clans)='$lowercaseclans';");
		$array = $result->fetchArray(SQLITE3_ASSOC);
		return empty($array) == false;
    }
	public function isInClan($player) {
        $result = $this->db->query("SELECT player FROM master WHERE player='$player';");
        $array = $result->fetchArray(SQLITE3_ASSOC);
        return empty($array) == false;
    }
	public function isPresident($player) {
        $clans = $this->db->query("SELECT rank FROM master WHERE player='$player';");
        $clanArray = $clans->fetchArray(SQLITE3_ASSOC);
        return $clanArray["rank"] == "President";
    }
	public function isVicePresident($player) {
        $clans = $this->db->query("SELECT rank FROM master WHERE player='$player';");
        $clanArray = $clans->fetchArray(SQLITE3_ASSOC);
        return $clanArray["rank"] == "VicePresident";
    }
	public function isGeneral($player) {
        $clans = $this->db->query("SELECT rank FROM master WHERE player='$player';");
        $clanArray = $clans->fetchArray(SQLITE3_ASSOC);
        return $clanArray["rank"] == "General";
    }
	public function isProspect($player) {
        $clans = $this->db->query("SELECT rank FROM master WHERE player='$player';");
        $clanArray = $clans->fetchArray(SQLITE3_ASSOC);
        return $clanArray["rank"] == "Prospect";
    }
	public function Messages($string, $confirm = false) {
        if ($confirm) {
            return TextFormat::GREEN . "$string";
        } else {
            return TextFormat::LIGHT_PURPLE . "$string";
        }
    }
	public function getNumberOfPlayersinclan($clans) {
        $query = $this->db->query("SELECT COUNT(player) as count FROM master WHERE clans='$clans';");
        $number = $query->fetchArray();
        return $number['count'];
    }
	public function isclanFull($clan) {
        return $this->getNumberOfPlayersinclan($clan) >= $this->prefs->get("MaxPlayersPerClan");
    }
	//////////Balance//////////
	public function getBal($clans) {
        $lvl = $this->db->query("SELECT bal FROM money WHERE clans='$clans';");
        $clanArray = $lvl->fetchArray(SQLITE3_ASSOC);
        return $clanArray["bal"];
    }
	public function setBal($clans, int $bal){
		$stmt = $this->db->prepare("INSERT OR REPLACE INTO money (clans, bal) VALUES (:clans, :bal);");
		$stmt->bindValue(":clans", $clans);
		$stmt->bindValue(":bal", $bal);
		return $stmt->execute();
	}
	public function addToBal($clans, int $bal){
		if($bal < 0) return false;
		return $this->setBal($clans, $this->getBal($clans) + $bal);
	}
	public function reduceBal($clans, int $bal){
		if($bal < 0) return false;
		return $this->setBal($clans, $this->getBal($clans) - $bal);
	}
	//////////////////level////////////////
	public function getlvl($clans) {
        $lvl = $this->db->query("SELECT lvl FROM level WHERE clans='$clans';");
        $clanArray = $lvl->fetchArray(SQLITE3_ASSOC);
        return $clanArray["lvl"];
    }
	public function setlvl($clans, int $lvl){
		$stmt = $this->db->prepare("INSERT OR REPLACE INTO level (clans, lvl) VALUES (:clans, :lvl);");
		$stmt->bindValue(":clans", $clans);
		$stmt->bindValue(":lvl", $lvl);
		return $stmt->execute();
	}
	public function lvlup($clans){
		return $this->setlvl($clans, $this->getlvl($clans) + 1);
	}
	public function getEconomy(): EconomyAPI{	
 		$eco = Server::getInstance()->getPluginManager()->getPlugin("EconomyAPI");	
 		if(!$eco) return $ec;	
 		if(!$eco->isEnabled()) return null;	
 		return $eco;	
 	}
	 public function onDisable(): void {
        if (isset($this->db)) $this->db->close();
    }
}
