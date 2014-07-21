<?php

namespace MinecrafterJPN;

use pocketmine\scheduler\PluginTask;

class DrawingTask extends PluginTask
{
    public function __construct(PocketLottery $owner){
        $this->owner = $owner;
    }

    public function onRun($currentTick)
    {
        $this->owner->draw();
    }
}