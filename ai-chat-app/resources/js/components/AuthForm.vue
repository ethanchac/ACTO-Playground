<template>
  <div class="auth-container">
    <el-card class="auth-card">
      <template #header>
        <div class="card-header">
          <h2>{{ isLogin ? 'Login' : 'Register' }}</h2>
        </div>
      </template>

      <el-form :model="form" :rules="rules" ref="formRef" label-position="top">
        <el-form-item v-if="!isLogin" label="Name" prop="name">
          <el-input v-model="form.name" placeholder="Enter your name" />
        </el-form-item>

        <el-form-item label="Email" prop="email">
          <el-input v-model="form.email" placeholder="Enter your email" type="email" />
        </el-form-item>

        <el-form-item label="Password" prop="password">
          <el-input
            v-model="form.password"
            placeholder="Enter your password"
            type="password"
            show-password
          />
        </el-form-item>

        <el-form-item>
          <el-button
            type="primary"
            @click="handleSubmit"
            :loading="loading"
            class="submit-button"
          >
            {{ isLogin ? 'Login' : 'Register' }}
          </el-button>
        </el-form-item>
      </el-form>

      <div class="switch-mode">
        <el-button text @click="toggleMode">
          {{ isLogin ? 'Need an account? Register' : 'Have an account? Login' }}
        </el-button>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { ElMessage } from 'element-plus';
import api from '../api.js';
import { setAuth } from '../auth.js';

const isLogin = ref(true);
const loading = ref(false);
const formRef = ref(null);

const form = reactive({
  name: '',
  email: '',
  password: ''
});

const rules = reactive({
  name: [
    { required: true, message: 'Please enter your name', trigger: 'blur' }
  ],
  email: [
    { required: true, message: 'Please enter your email', trigger: 'blur' },
    { type: 'email', message: 'Please enter a valid email', trigger: 'blur' }
  ],
  password: [
    { required: true, message: 'Please enter your password', trigger: 'blur' },
    { min: 8, message: 'Password must be at least 8 characters', trigger: 'blur' }
  ]
});

function toggleMode() {
  isLogin.value = !isLogin.value;
  // Clear form when switching
  form.name = '';
  form.email = '';
  form.password = '';
}

async function handleSubmit() {
  // Validate form
  const valid = await formRef.value.validate().catch(() => false);
  if (!valid) return;

  loading.value = true;

  try {
    const endpoint = isLogin.value ? '/login' : '/register';
    const data = isLogin.value
      ? { email: form.email, password: form.password }
      : { name: form.name, email: form.email, password: form.password };

    const response = await api.post(endpoint, data);

    // Save auth
    setAuth(response.data.user, response.data.token);

    // Show success message
    ElMessage.success(isLogin.value ? 'Logged in successfully!' : 'Registered successfully!');

  } catch (error) {
    // Show error message
    const message = error.response?.data?.message || 'An error occurred';
    ElMessage.error(message);
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
.auth-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 500px;
  padding: 20px;
}

.auth-card {
  width: 100%;
  max-width: 400px;
}

.card-header {
  text-align: center;
}

.card-header h2 {
  margin: 0;
  color: #303133;
}

.submit-button {
  width: 100%;
}

.switch-mode {
  text-align: center;
  margin-top: 16px;
}
</style>
