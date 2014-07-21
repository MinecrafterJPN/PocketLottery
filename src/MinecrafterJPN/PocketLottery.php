<?php

/**
 * Pocketlottery
 * @version 1.0.0
 * @author MinecrafterJPN
 */

namespace MinecrafterJPN;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class PocketLottery extends PluginBase
{
    /* @var array */
    private $tickets;

    public function onLoad()
	{
	}

	public function onEnable()
	{
        $this->saveDefaultConfig();
        $this->reloadConfig();
        $this->tickets = array();
        $this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new DrawingTask($this), $this->getConfig()->get("interval"), $this->getConfig()->get("interval"));
    }

	public function onDisable()
	{
        $this->getConfig()->save();
	}

	public function onCommand(CommandSender $sender, Command $command, $label, array $args)
	{
		switch ($command->getName()) {
			case "lottery":
				$subCommand = strtolower(array_shift($args));
				switch ($subCommand) {
					case "":
                        $price = $this->getConfig()->get("ticket_price");
                        $pot = $this->getConfig()->get("pot");
                        $numOfTickets = count($this->tickets);
                        $sender->sendMessage("Ticket: $price PM per one");
                        $sender->sendMessage("Pot: $pot PM");
                        $sender->sendMessage("$numOfTickets tickets have been bought");
                        break;

					case "help":
                        $sender->sendMessage("/lottery");
						$sender->sendMessage("/lottery help");
						$sender->sendMessage("/lottery buy <num>");
						$sender->sendMessage("/lottery donate <amount>");
                        break;

					case "buy":
                        $num = array_shift($args);
                        if ($num === null or !is_numeric($num)) {
                            $sender->sendMessage("/lottery buy <num>");
                            break;
                        }
                        $total = $this->getConfig()->get("ticket_price") * $num;
                        if ($this->getServer()->getPluginManager()->getPlugin("PocketMoney")->grantMoney($sender->getName(), -$total) !== true) {
                            $sender->sendMessage("Not enough balance!");
                            break;
                        }
                        $this->getConfig()->set("pot", $this->getConfig()->get("pot") + $total);
                        $i = 0;
                        while ($i++ < $num) {
                            array_push($this->tickets, $sender->getName());
                        }
                        $sender->sendMessage("Completed buying $num tickets, $total PM!");
                        break;

					case "donate":
                        $amount = array_shift($args);
                        if ($amount === null or !is_numeric($amount)) {
                            $sender->sendMessage("/lottery donate <amount>");
                            break;
                        }
                        if ($this->getServer()->getPluginManager()->getPlugin("PocketMoney")->grantMoney($sender->getName(), $amount) !== true) {
                            $sender->sendMessage("Not enough balance!");
                            break;
                        }
                        $this->getConfig()->set("pot", $this->getConfig()->get("pot") + $amount);
                        $sender->sendMessage("Completed donating $amount PM!");
                        break;

                    case "config":
                        break;

					default:
                        $sender->sendMessage("\"/money $subCommand\" does not exist");
						break;
				}
				return true;

            default:
                return false;
		}
	}

    public function draw()
    {
        if (count($this->tickets) < 1) return;
        $winner = $this->tickets[mt_rand(0, count($this->tickets) - 1)];
        $pot = $this->getConfig()->get("pot");
        $this->getServer()->broadcastMessage("$winner win! $pot PM -> $winner !!");
        $this->getServer()->getPluginManager()->getPlugin("PocketMoney")->grantMoney($winner, $pot);
        $this->getConfig()->set("pot", 0);
        $this->tickets = [];
    }
}
