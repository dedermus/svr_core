<?php

namespace Svr\Core\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Svr\Core\Extensions\Handler\SystemEmail;

class ProcessSendingEmail implements ShouldQueue
{
    use Queueable;
    protected string $email;        // email
    protected string $title;        // заголовок
    protected string $message;      // сообщение

    /**
     * Create a new job instance.
     */
    public function __construct($email, $title, $message)
    {
        $this->email = $email;
        $this->title = $title;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        SystemEmail::sendEmailCustom($this->email, $this->title, $this->message);
    }
}
