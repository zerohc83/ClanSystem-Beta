<?php

namespace Wassi;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;

use onebone\economyapi\EconomyAPI;

class ClanCommands {
    public $plugin;

	
    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
		$this->economy = EconomyAPI::getInstance();
    }
	##################################################commands##################################################
	#####################################################################################################
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
        if ($sender instanceof Player) {
            $playerName = $sender->getPlayer()->getName();
            if (strtolower($command->getName()) === "clan") {
                if (empty($args)) {
                    $sender->sendMessage(" Please use /clan help for a list of commands");
                    return true;
                }
				                                                   // CREATE //
                    if(strtolower($args[0]) == "create" or strtolower($args[0]) == "make"){
			          if($this->plugin->isInClan($playerName)){
							$sender->sendMessage($this->plugin->Messages("§cYou must leave your clan before you create one", false));
							return true;
						}
						if ($this->plugin->isInClan($playerName) == false) {
                            $this->createform($sender);
                            return true;
                        } 
                    }
										// del/leave //
                    if(strtolower($args[0]) == "delete" or strtolower($args[0]) == "del"){
                        if ($this->plugin->isInClan($playerName)) {
                            if ($this->plugin->isPresident($playerName)) {
                        $this->detconfirmm($sender);
                              return true;
                            } else {
                                $sender->sendMessage($this->plugin->Messages("§cYou are not leader!"));
								return true;
                            }  
                        } else {
                                $sender->sendMessage($this->plugin->Messages("§cYou are not in a clan silly!"));
								return true;
                            }  
                    }
					if(strtolower($args[0] == "leave")) {
                        if ($this->plugin->isPresident($playerName) == false) {
                            $clan = $this->plugin->getPlayerClan($playerName);
                            $name = $sender->getName();
                            $this->plugin->db->query("DELETE FROM master WHERE player='$name';");
			                $this->plugin->getServer()->broadcastMessage("§2$name §bhas left §2$clan");
                            $sender->sendMessage("§bYou successfully left §a$clan", true);
                        } else {
                            $sender->sendMessage($this->plugin->Messages("§cYou must delete the clan or give\nleader to someone else first"));
			    return true;
                        }
                    } 
					//levels//
					if(strtolower($args[0] == "level")) {
                        if ($this->plugin->isInClan($playerName)) {
                            $this->lvls($sender);
                            return true;
                        }
    
                    }
					       //info/about//got info to do//
					if(strtolower($args[0] == "about")) {
                        if ($this->plugin->isInClan($playerName)) {
                            $this->claninfo($sender);
                            return true;
                        }
    
                    }
 						 if(strtolower($args[0] == "bal")) {
                        if ($this->plugin->isInClan($playerName)) {
                            $clan = $this->plugin->getPlayerClan($playerName);
							$cb = $this->plugin->getBal($clan);
							$sender->sendMessage($this->plugin->Messages("Your clan bal $cb"));
                            return true;
                        }
    
                    }
					if(strtolower($args[0]) == "invite" or strtolower($args[0]) == "inv"){
                        if ($this->plugin->isclanFull($this->plugin->getPlayerFaction($playerName))) {
                            $sender->sendMessage($this->plugin->formatMessage("This clan is full, please kick players to make room"));
                            return true;
                        }
                       
                            if (!($this->plugin->isVicePresident($playerName) || $this->plugin->isPresident($playerName))) {
                                $sender->sendMessage($this->plugin->formatMessage("§cOnly your clan President/vice President can invite"));
                                return true;
                            }
                                  $this->inviteform($sender);
                     }
										
										}
					return true;
										}
										return true;
										}
										
										
/////////////////////////////////////////FORMS////////////////////////////////////////////////
function createform($player)
    {
        $form = new CustomForm(function (Player $player, $data = null) {

            if ($data === null) {
                return true;
				}
				$mybal = EconomyAPI::getInstance()->myMoney($player);
				$ctm = $this->plugin->prefs->get("Costtomakeclan");
            $clan = $data[1];
			$name = $player->getName();
			if( (($mybal) - ($ctm) ) < 0 ){
							$player->sendMessage($this->plugin->Messages("You dont have enough money silly!"));
							return true;
						}
				if ($this->plugin->clanExists($data[1])) {
                            $player->sendMessage($this->plugin->Messages("The Clan named ".$clan." already exist"));
                            return true;
                        } else {						
							$sb = $this->plugin->prefs->get("Startingbalance");
						    $sl = $this->plugin->prefs->get("Startinglevel");
							$clan = $data[1];
                            $rank = "President";
                            $stmt = $this->plugin->db->prepare("INSERT OR REPLACE INTO master (player, clans, rank) VALUES (:player, :clans, :rank);");
                            $stmt->bindValue(":player", $name);
                            $stmt->bindValue(":clans", $clan);
                            $stmt->bindValue(":rank", $rank);
							$this->plugin->setBal($clan, $sb);
							$this->plugin->setlvl($clan, $sl);
							$player->sendMessage($this->plugin->Messages("You made a Clan named ". $clan ." do /clan level to level up"));
                            $result = $stmt->execute();		
							$this->plugin->getServer()->broadcastMessage("$name has created a clan named ". $clan . "for ");
							EconomyAPI::getInstance()->reduceMoney($player, $ctm);
							return true;
						}
        });
		$ct = $this->plugin->prefs->get("CreateFormTitle");
$cl = $this->plugin->prefs->get("CreateFormLabel");
$ctm = $this->plugin->prefs->get("Costtomakeclan");
        $form->setTitle("$ct");
        $form->addLabel("$cl");
		$form->addInput("Clan Name");
        $form->sendToPlayer($player);
        return $form;
			}
			public function claninfo($player)
    {
        $form = new SimpleForm(function (Player $player, int $data = null) {

            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
									 
										
                    break;
            }
        });
		$name = $player->getName();
        $clan = $this->plugin->getPlayerClan($name);
        $form->setTitle("$clan info");
        $form->setContent("your clan name $clan \n your memebe count");
        $form->addButton("Close");
        $form->sendToPlayer($player);
        return $form;
			}
			public function detconfirmm($player)
    {
        $form = new SimpleForm(function (Player $player, int $data = null) {

            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
									 
										
                    break;
					case 1:
					$name = $player->getName();
							$clan = $this->plugin->getPlayerClan($name);
                                $this->plugin->db->query("DELETE FROM master WHERE clans='$clan';");
					           $player->sendMessage($this->plugin->Messages("§aClan deleted!"));
		                        $this->plugin->getServer()->broadcastMessage("The player: $name who owned $clan has been Deleted!");		 
										
                    break;
            }
        });
$dt = $this->plugin->prefs->get("DeleteFormTitle");
$dl = $this->plugin->prefs->get("DeleteFormLabel");
        $form->setTitle("$dt");
        $form->setContent("$dl" );
		$form->addButton("cancel");
        $form->addButton("DELETE");
        $form->sendToPlayer($player);
        return $form;
			}
			
			public function lvls($player)
    {
        $form = new SimpleForm(function (Player $player, int $data = null) {

            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    break;
					case 1:
					$name = $player->getName();
							$clans = $this->plugin->getPlayerClan($name);
       $cb = $this->plugin->getBal($clans);
		       if( (($cb) - (15000) ) < 0 ){
							$player->sendMessage($this->plugin->Messages("Your clan doesn't have enough money silly! \nclan bal: §a". $cb));
							return true;
						} else {
						$this->plugin->reduceBal($clans, 15000);
						$this->plugin->lvlup($clans);
						$player->sendMessage("(?) you have level up your clan to ");
						} 
                    break;
            }
        });
							
        $clans = $this->plugin->getPlayerClan($player->getName());
		$lvl = $this->plugin->getlvl($clans);
        $form->setTitle("$clans test");
        $form->setContent("§cYour level:$lvl" );
		$form->addButton("cancel");
        $form->addButton("Level UP");
        $form->sendToPlayer($player);
        return $form;
			}
			public $playerList = [];
			function inviteform($player)
    {
        $form = new CustomForm(function (Player $player, $data = null) {
             $list = [];
			 foreach($this->getServer->getOnlinePlayers as $p){ 
			 $list[] = $p->getName();
			 }
			 $this->playerList[$player->getName()] = $list;
            if ($data === null) {
                return true;
				}
				$index = $data[1];
				$playerinvited = $this->playerList[$player->getName()][$index];
				
				if ($this->plugin->isInClan($playerinvited->getName()) == true) {
                            $sender->sendMessage($this->plugin->Messages("(?)§cThe player named $playerinvited is already in a clan"));
                            return true;
                        } 
						if ($playerinvited->getName() == $playerName) {
                            $sender->sendMessage($this->plugin->Messages("(?)§cYou can't invite yourself to your own clan"));
                            return true;
                        } else {						

						}
        });
		$pl = $this->playerList[$player->getName()];
$mti = $this->plugin->prefs->get("MaxPlayersPerClan");
$it = $this->plugin->prefs->get("InviteFormTitle");
$il = $this->plugin->prefs->get("InviteFormLabel");
        $form->setTitle("$it");
        $form->addLabel("$il");
		$form->addDropdown("invite a player", $pl);
        $form->sendToPlayer($player);
        return $form;
			}
    }
