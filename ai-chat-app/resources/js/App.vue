<template>
  <div class="app-container">
    <h1>AI Chat Application</h1>
    <ChatWindow />
  </div>
</template>

<script setup>
import ChatWindow from './components/ChatWindow.vue';

console.log('Vue app is running!');
</script>

<style scoped>
.app-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

h1 {
  color: #2c3e50;
  font-size: 2rem;
  margin-bottom: 1rem;
}

p {
  color: #666;
}
</style>
