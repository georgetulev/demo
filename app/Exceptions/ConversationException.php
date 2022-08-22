<?php

namespace App\Exceptions;

use Exception;
use Throwable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ConversationException extends Exception
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Report the exception.
     */
    public function report(): ?bool
    {
        Log::error('Failed processing conversation! ' . 'Exception: ' . $this->getMessage());

        return null;
    }

    /**
     * Render the exception into a Json response.
     */
    public function render(): bool|JsonResponse
    {
        if (app()->runningInConsole()) {
            return false;
        }

        return response()->json([
            'message' => 'Conversation not found!',
        ], 422);
    }
}
