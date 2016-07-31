<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Notification;
use App\Poll;
use App\Push\GCMPushMessage;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

class Push extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $message;
    private $users_id;
    private $serverApiKey;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($message, $serverApiKey, $users_id = null)
    {
        $this->message = $message;
        $this->serverApiKey = $serverApiKey;
        $this->users_id = $users_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $gcms = new GCMPushMessage($this->serverApiKey);
        $gcms->sendFromDB($this->message, $this->users_id);
    }
}
