<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GroqService
{
    public function chat(array $messages): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.groq.key'),
            'Content-Type' => 'application/json',
        ])->post('https://api.groq.com/openai/v1/chat/completions', [
            'model' => config('services.groq.model'),
            'messages' => $messages,
        ]);

        // Check if the request was successful
        if ($response->failed()) {
            throw new \Exception('Groq API request failed: ' . $response->body());
        }

        $content = $response->json('choices.0.message.content');

        // Ensure we got content back
        if (empty($content)) {
            throw new \Exception('No content received from Groq API');
        }

        return $content;
    }
}
