import { apiFetch } from './api';

export async function registerAccount({ name, email, phone, company, password, userType }) {
  return apiFetch('/register', {
    method: 'POST',
    body: JSON.stringify({
      name,
      email,
      phone: phone || undefined,
      company: company || undefined,
      password,
      user_type: userType,
    }),
  });
}

export async function login({ email, password }) {
  return apiFetch('/login', {
    method: 'POST',
    body: JSON.stringify({ email, password }),
  });
}

export async function logout() {
  return apiFetch('/logout', { method: 'POST' });
}
