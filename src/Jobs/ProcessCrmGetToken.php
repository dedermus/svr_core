<?php

namespace Svr\Core\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Svr\Core\Extensions\Email\SystemEmail;

class ProcessCrmGetToken implements ShouldQueue
{
    use Queueable;
    protected string $email;        // email
    protected string $password;     // пароль

    /**
     * Create a new job instance.
     */
    public function __construct($email, $password)
    {
        $this->email = $email;
        $this->password = $password;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        SystemEmail::sendEmailCustom($this->email, $this->password);
    }
}
