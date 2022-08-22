<?php

namespace App\Services;

use Illuminate\Support\LazyCollection;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\ConversationException;

class ConversationProcessor
{
    public function handle(int $conversationId): array
    {
        $convStoragePath = "/conversations/{$conversationId}/";

        $userChannelFile = $convStoragePath . 'user-channel.txt';
        $customerChannelFile = $convStoragePath . 'customer-channel.txt';

        throw_if(!Storage::disk('local')->exists($userChannelFile)
            || !Storage::disk('local')->exists($customerChannelFile),
            new ConversationException('Conversation files missing', 422)
        );

        $userConversationData = $this->process(Storage::disk('local')->path($userChannelFile));
        $customerConversationData = $this->process(Storage::disk('local')->path($customerChannelFile));

        $totalConversationDuration = max(
            $userConversationData['conversationEndTime'],
            $customerConversationData['conversationEndTime']
        );

        $userTalkPercentage = ($userConversationData['totalTalkDuration'] / $totalConversationDuration) * 100;

        return [
            "longest_user_monologue" => $userConversationData['longestMonologue'],
            "longest_customer_monologue" => $customerConversationData['longestMonologue'],
            "user_talk_percentage" => round($userTalkPercentage, 2),
            "user" => $userConversationData['data']->map(fn($item) => [$item['startTalk'], $item['endTalk']])->toArray(),
            "customer" => $customerConversationData['data']->map(fn($item) => [$item['startTalk'], $item['endTalk']])->toArray()
        ];
    }

    private function process($fileName): array
    {
        $fileDataCollection = $this->toCollection($fileName);

        $firstRow = $fileDataCollection->first();
        $start = (float)explode('silence_start: ', $firstRow)[1];
        $firstRowData = [['startTalk' => 0, 'endTalk' => $start, 'duration' => $start]];

        $lastRow = $fileDataCollection->last();
        $lastRowData = explode('|', $lastRow)[0];
        $conversationEndTime = (float)explode('silence_end: ', $lastRowData)[1];

        $totalRows = $fileDataCollection->count();

        $data = $fileDataCollection
            ->skip(1)
            ->take($totalRows - 2)
            ->chunk(2)
            ->map(function ($lines) {
                $firstLine = explode('|', $lines->first());
                $startTalk = (float)explode('silence_end: ', $firstLine[0])[1];
                $secondLine = $lines->skip(1)->first();
                $endTalk = (float)explode('silence_start: ', $secondLine)[1];

                return ['startTalk' => $startTalk, 'endTalk' => $endTalk, 'duration' => (float)number_format($endTalk - $startTalk, 3)];
            })->filter(function ($period) {
                return $period['duration'] > 0;
            });

        $parsedData = collect($firstRowData)->merge($data);

        return [
            'longestMonologue' => $parsedData->max('duration'),
            'totalTalkDuration' => $parsedData->sum('duration'),
            'conversationEndTime' => $conversationEndTime,
            'data' => $parsedData
        ];
    }

    private function toCollection($fileName): LazyCollection
    {
        return LazyCollection::make(function () use ($fileName) {
            $handle = fopen($fileName, 'r');

            while (($line = fgets($handle)) !== false) {
                yield $line;
            }
        });
    }

}
