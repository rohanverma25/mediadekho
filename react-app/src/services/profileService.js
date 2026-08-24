import { apiFetch } from './api';

export async function updateProfile(data) {
  return apiFetch('/profile', {
    method: 'PUT',
    body: JSON.stringify(data),
  });
}
