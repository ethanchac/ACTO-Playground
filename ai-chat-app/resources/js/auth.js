import { ref } from 'vue';

// Reactive auth state
export const isAuthenticated = ref(false);
export const user = ref(null);
export const token = ref(null);

// Initialize from localStorage on page load
export function initAuth() {
  const savedToken = localStorage.getItem('auth_token');
  const savedUser = localStorage.getItem('auth_user');

  if (savedToken && savedUser) {
    token.value = savedToken;
    user.value = JSON.parse(savedUser);
    isAuthenticated.value = true;
  }
}

// Save user and token after login/register
export function setAuth(userData, authToken) {
  user.value = userData;
  token.value = authToken;
  isAuthenticated.value = true;

  // Save to localStorage
  localStorage.setItem('auth_token', authToken);
  localStorage.setItem('auth_user', JSON.stringify(userData));
}

// Clear auth on logout
export function clearAuth() {
  user.value = null;
  token.value = null;
  isAuthenticated.value = false;

  // Remove from localStorage
  localStorage.removeItem('auth_token');
  localStorage.removeItem('auth_user');
}
