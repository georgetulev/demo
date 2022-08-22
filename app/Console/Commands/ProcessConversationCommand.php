<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ConversationProcessor;

class ProcessConversationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:conversation {conversation : The ID of the conversation.}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processing conversation files. {conversation : The ID of the conversation.}';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $result = (new ConversationProcessor())->handle($this->argument('conversation'));
        } catch (\Throwable $exception) {
            $this->info("Conversation processing failed for ID: {$this->argument('conversation')}!"
                . ' Reason: ' . $exception->getMessage());

            return 0;
        }

        $this->info(json_encode($result));

        return 1;
    }
}
