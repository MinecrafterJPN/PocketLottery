<?php

/**
 * Pocketlottery
 * @version 1.0.0
 * @author MinecrafterJPN
 */

namespace MinecrafterJPN;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;

use PocketMoney\Error\SimpleError;
use PocketMoney\constants\PlayerType;

class PocketLottery extends PluginBase
{
    /* @var array */
    private $config;
    private $tickets;

    public function onLoad()
	{
	}

	public function onEnable()
	{
        $this->config = ["price" => 100, "pot" => 0];
        $this->tickets = array();
        $this->getServer()->getScheduler()->scheduleDelayedTask(new DrawingTask($this), 5000);
    }

	public function onDisable()
	{
	}

	public function onCommand(CommandSender $sender, Command $command, $label, array $args)
	{
		switch ($command->getName()) {
			case "lottery":
				$subCommand = strtolower(array_shift($args));
				switch ($subCommand) {
					case "":
					case "help":
						$sender->sendMessage("/lottery help");
						$sender->sendMessage("/lottery buy <num>");
						$sender->sendMessage("/lottery donate <amount>");
                        break;

					case "buy":
                        $num = array_shift($args);
                        $cost = $this->config['price'] * $num;
                        if ($this->getServer()->getPluginManager()->getPlugin("PocketMoney")->grantMoney($sender->getName(), $cost) !== true) {
                            $sender->sendMessage("Not enough money!");
                            break;
                        }
                        $this->config['pot'] += $cost;
                        for ($i = 0; $i < $num; $i++) {
                            array_push($this->tickets, $sender->getName());
                        }
                        break;

					case "donate":
                        $amount = array_shift($args);
                        if ($this->getServer()->getPluginManager()->getPlugin("PocketMoney")->grantMoney($sender->getName(), $amount) !== true) {
                            $sender->sendMessage("Not enough money!");
                            break;
                        }
                        $this->config['pot'] += $amount;
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

    public function drawing()
    {
        $winner = $this->tickets[mt_rand(0, count($this->tickets))];
        $pot = $this->config['pot'];
        $this->getServer()->broadcastMessage("$winner win! $pot PM -> $winner !!");
        $this->getServer()->getPluginManager()->getPlugin("PocketMoney")->grantMoney($winner, $pot);
    }
}
