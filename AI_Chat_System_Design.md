# AI Chat Interface — System Design Document
**Laravel · Vue 3 · Element Plus · Groq API**
*Version 1.0 | May 2026*

---

## 1. Project Overview

A full-stack AI chat application that allows users to have real-time conversations with a large language model. Built on Laravel (backend API), Vue 3 (frontend SPA), Element Plus (UI framework), and Groq (free LLM inference API). Designed as a learning project to gain hands-on experience with all three technologies used at Applied Optimal Inc.

### 1.1 Goals

- Build a working chat UI that streams AI responses in real time
- Practice Laravel routing, controllers, and API design
- Use Vue 3 Composition API and reactivity system
- Use Element Plus components (`ElInput`, `ElButton`, `ElScrollbar`, `ElCard`)
- Integrate a free external AI API (Groq) from a secure backend
- Persist conversation history in a database

### 1.2 Tech Stack

| Layer | Technology | Purpose |
|---|---|---|
| Backend | Laravel 11 (PHP) | REST API, business logic, DB access |
| Frontend | Vue 3 + Vite | Single Page Application |
| UI Library | Element Plus | Ready-made Vue 3 components |
| AI Provider | Groq API (free) | LLM inference (Llama 3) |
| Database | MySQL / SQLite | Store users & conversations |
| Auth | Laravel Sanctum | API token authentication |
| HTTP Client | Axios | Frontend API calls |

---

## 2. System Architecture

The application follows a standard SPA + REST API pattern. Vue handles all rendering in the browser; Laravel exposes JSON endpoints. The Groq API key is stored server-side only and never exposed to the client.

### 2.1 High-Level Flow

```
Browser (Vue 3 + Element Plus)
      |
      |  HTTP POST /api/chat  (Axios)
      |
Laravel API (routes/api.php)
      |
      |-- ChatController@send
      |       |-- Save message to DB
      |       |-- Call Groq API (server-side, key hidden)
      |       |-- Save AI response to DB
      |       └-- Return JSON response
      |
Vue receives response → renders in chat window
```

### 2.2 Component Breakdown

| Component | Location | Responsibility |
|---|---|---|
| `ChatView.vue` | `resources/js/views/` | Main chat page layout |
| `MessageList.vue` | `resources/js/components/` | Renders all messages in a thread |
| `MessageBubble.vue` | `resources/js/components/` | Single message bubble (user or AI) |
| `ChatInput.vue` | `resources/js/components/` | Text input + send button |
| `ConversationSidebar.vue` | `resources/js/components/` | List of past conversations |
| `ChatController.php` | `app/Http/Controllers/` | Handles /api/chat POST endpoint |
| `GroqService.php` | `app/Services/` | Wraps Groq API HTTP calls |
| `Conversation.php` | `app/Models/` | Eloquent model for conversations |
| `Message.php` | `app/Models/` | Eloquent model for messages |

---

## 3. Database Schema

### 3.1 users

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | Auto increment |
| name | varchar(255) | |
| email | varchar(255) | Unique |
| password | varchar(255) | Hashed |
| created_at / updated_at | timestamp | Laravel default |

### 3.2 conversations

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | Auto increment |
| user_id | bigint FK | References users.id |
| title | varchar(255) | Auto-generated from first message |
| created_at / updated_at | timestamp | |

### 3.3 messages

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | Auto increment |
| conversation_id | bigint FK | References conversations.id |
| role | enum('user','assistant') | Who sent the message |
| content | text | Message body |
| created_at / updated_at | timestamp | |

---

## 4. API Endpoints

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| POST | `/api/register` | Register new user | None |
| POST | `/api/login` | Login, returns token | None |
| POST | `/api/logout` | Invalidate token | Required |
| GET | `/api/conversations` | List all conversations | Required |
| POST | `/api/conversations` | Create new conversation | Required |
| GET | `/api/conversations/{id}/messages` | Get messages in a conversation | Required |
| POST | `/api/chat` | Send a message, get AI reply | Required |
| DELETE | `/api/conversations/{id}` | Delete a conversation | Required |

### 4.1 POST /api/chat — Request & Response

**Request Body**
```json
{
  "conversation_id": 3,
  "message": "Explain what a closure is in JavaScript"
}
```

**Response**
```json
{
  "conversation_id": 3,
  "user_message": {
    "id": 12, "role": "user", "content": "Explain what a closure is..."
  },
  "ai_message": {
    "id": 13, "role": "assistant", "content": "A closure is a function that..."
  }
}
```

---

## 5. Groq API Integration

### 5.1 Why Groq

- Completely free tier with no credit card required
- Extremely fast inference (low latency responses)
- Supports Llama 3 and Mixtral models
- Simple REST API, easy to call from Laravel's HTTP client
- Generous rate limits suitable for a personal project

### 5.2 GroqService.php

```php
class GroqService {
  public function chat(array $messages): string {
    $response = Http::withHeaders([
      'Authorization' => 'Bearer ' . config('services.groq.key'),
      'Content-Type'  => 'application/json',
    ])->post('https://api.groq.com/openai/v1/chat/completions', [
      'model'    => 'llama3-8b-8192',
      'messages' => $messages,
    ]);
    return $response->json('choices.0.message.content');
  }
}
```

### 5.3 Environment Config

```bash
# .env
GROQ_API_KEY=gsk_xxxxxxxxxxxxxxxxxxxx

# config/services.php
'groq' => ['key' => env('GROQ_API_KEY')]
```

> The API key is stored in `.env` and accessed via Laravel's config system. It is never sent to the frontend or exposed in any API response.

---

## 6. Frontend Design (Vue 3 + Element Plus)

### 6.1 Vue 3 Patterns Used

- **State:** Composition API
- **Reactivity:** `ref()` and `reactive()` for chat state, loading flags, input values
- **API Calls:** `axios.post('/api/chat', ...)` inside an async function
- **Templates:** `v-for` to render messages, `v-model` on `ElInput`
- **Element Plus:** `ElScrollbar`, `ElCard`, `ElButton`, `ElInput`, `ElAvatar`

### 6.2 ChatView.vue Structure

```vue
<template>
  <div class="chat-layout">
    <ConversationSidebar />
    <div class="chat-main">
      <MessageList :messages="messages" />
      <ChatInput @send="sendMessage" :loading="loading" />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const messages = ref([])
const loading  = ref(false)

async function sendMessage(text) {
  loading.value = true
  messages.value.push({ role: 'user', content: text })
  const res = await axios.post('/api/chat', { message: text, conversation_id: currentId.value })
  messages.value.push(res.data.ai_message)
  loading.value = false
}
</script>
```

---

## 7. Project File Structure

```
ai-chat/
├── app/
│   ├── Http/Controllers/
│   │   ├── AuthController.php
│   │   └── ChatController.php
│   ├── Models/
│   │   ├── Conversation.php
│   │   └── Message.php
│   └── Services/
│       └── GroqService.php
├── database/migrations/
│   ├── create_conversations_table.php
│   └── create_messages_table.php
├── routes/
│   └── api.php
└── resources/js/
    ├── views/
    │   ├── ChatView.vue
    │   └── LoginView.vue
    ├── components/
    │   ├── MessageList.vue
    │   ├── MessageBubble.vue
    │   ├── ChatInput.vue
    │   └── ConversationSidebar.vue
    ├── router/index.js
    └── app.js
```

---

## 8. Build Plan

| Phase | Task | Est. Time |
|---|---|---|
| 1 | Laravel setup: auth (Sanctum), migrations, basic routes | 2–3 hrs |
| 2 | GroqService + ChatController: send a message, get a reply | 1–2 hrs |
| 3 | Vue 3 + Vite setup, Vue Router, Axios config | 1 hr |
| 4 | ChatView + MessageList + ChatInput components | 2–3 hrs |
| 5 | ConversationSidebar + load history from DB | 2 hrs |
| 6 | Polish: loading states, error handling, Element Plus theming | 1–2 hrs |

### 8.1 Recommended Build Order

Start with Phase 1 and 2 first — get the backend working and test it with a tool like Postman or Hoppscotch before touching the frontend. This way you isolate problems and understand what Laravel is doing before Vue gets involved.

---

## 9. Getting Your Free Groq API Key

1. Go to [console.groq.com](https://console.groq.com) and sign in with a Google account
2. Navigate to **API Keys** in the sidebar
3. Click **Create API Key** and copy the value
4. Paste it into your `.env` file as `GROQ_API_KEY=gsk_...`
5. The free tier allows approximately 14,400 requests per day — more than enough

**No credit card required. Rate limits reset every 24 hours.**
