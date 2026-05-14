<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\GroqService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    protected $groqService;

    public function __construct(GroqService $groqService)
    {
        $this->groqService = $groqService;
    }
    public function send(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'conversation_id' => 'nullable|exists:conversations,id',
            'message' => 'required|string',
        ]);

        // Get or create a conversation
        if (isset($validated['conversation_id'])) {
            $conversation = Conversation::find($validated['conversation_id']);
        } else {
            $conversation = Conversation::create([
                'user_id' => 1, // TODO: Get from authenticated user
                'title' => substr($validated['message'], 0, 50),
            ]);
        }

        // Save the user's message
        $userMessage = $conversation->messages()->create([
            'role' => 'user',
            'content' => $validated['message'],
        ]);

        // Get all messages in this conversation for context
        $allMessages = $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                return [
                    'role' => $message->role,
                    'content' => $message->content,
                ];
            })
            ->toArray();

        // Call Groq AI API
        $aiResponse = $this->groqService->chat($allMessages);

        // Save the AI's response
        $aiMessage = $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $aiResponse,
        ]);

        return response()->json([
            'conversation_id' => $conversation->id,
            'user_message' => $userMessage,
            'ai_message' => $aiMessage,
        ]);
    }
}
