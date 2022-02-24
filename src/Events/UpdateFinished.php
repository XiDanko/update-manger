<?php

namespace XiDanko\UpdateManager\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UpdateFinished
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $currentVersion, $newVersion;

    public function __construct(string $currentVersion, string $newVersion)
    {
        $this->currentVersion = $currentVersion;
        $this->newVersion = $newVersion;
    }
}
