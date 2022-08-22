<?php

use App\Services\ConversationProcessor;
use Illuminate\Support\Facades\Route;

Route::get('/conversations/{conversationId}', function ($conversationId) {

    return (new ConversationProcessor())->handle($conversationId);
})->where('conversationId', '[0-9]+');
