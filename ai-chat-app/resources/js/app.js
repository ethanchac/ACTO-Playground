import { createApp } from 'vue';
import ElementPlus from 'element-plus';
import 'element-plus/dist/index.css';
import App from './App.vue';
import { initAuth } from './auth.js';

// Initialize auth from localStorage
initAuth();

const app = createApp(App);

// Use Element Plus
app.use(ElementPlus);

app.mount('#app');
