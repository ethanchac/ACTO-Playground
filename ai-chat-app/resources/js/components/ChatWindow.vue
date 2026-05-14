<template>
  <div class="chat-container">
    <div class="messages-area">
      <div
        v-for="(message, index) in messages"
        :key="index"
        :class="['message', message.role === 'user' ? 'user-message' : 'ai-message']"
      >
        <strong>{{ message.role === 'user' ? 'You' : 'AI' }}:</strong>
        {{ message.content }}
      </div>
    </div>

    <div class="input-area">
      <input
        v-model="userInput"
        type="text"
        placeholder="Type your message..."
        class="message-input"
        @keyup.enter="sendMessage"
      />
      <button class="send-button" @click="sendMessage">Send</button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import api from '../api.js';

// Reactive state
const messages = ref([]);
const userInput = ref('');
const isLoading = ref(false);

// Function to send message
async function sendMessage() {
  // Don't send empty messages
  if (!userInput.value.trim()) {
    return;
  }

  // Store the message text
  const messageText = userInput.value;

  // Clear the input immediately
  userInput.value = '';

  // Add user message to the list
  messages.value.push({
    role: 'user',
    content: messageText
  });

  // Set loading state
  isLoading.value = true;

  try {
    // Call the Laravel API
    const response = await api.post('/chat', {
      message: messageText
    });

    // Add AI response to messages
    messages.value.push({
      role: 'assistant',
      content: response.data.ai_message.content
    });

  } catch (error) {
    console.error('Error calling API:', error);

    // Show error message to user
    messages.value.push({
      role: 'assistant',
      content: 'Sorry, there was an error getting a response. Please try again.'
    });
  } finally {
    // Always turn off loading state
    isLoading.value = false;
  }
}

console.log('ChatWindow component loaded!');
</script>

<style scoped>
.chat-container {
  max-width: 800px;
  margin: 0 auto;
  border: 1px solid #ddd;
  border-radius: 8px;
  overflow: hidden;
  background: white;
}

.messages-area {
  height: 500px;
  overflow-y: auto;
  padding: 20px;
  background: #f9f9f9;
}

.message {
  margin-bottom: 15px;
  padding: 12px;
  border-radius: 8px;
  max-width: 80%;
}

.user-message {
  background: #007bff;
  color: white;
  margin-left: auto;
  text-align: right;
}

.ai-message {
  background: #e9ecef;
  color: #333;
}

.input-area {
  display: flex;
  padding: 15px;
  border-top: 1px solid #ddd;
  background: white;
}

.message-input {
  flex: 1;
  padding: 10px 15px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
  outline: none;
}

.message-input:focus {
  border-color: #007bff;
}

.send-button {
  margin-left: 10px;
  padding: 10px 25px;
  background: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
}

.send-button:hover {
  background: #0056b3;
}

.send-button:active {
  background: #004085;
}
</style>
