import { apiFetch } from './api';

export async function fetchSettings() {
  const json = await apiFetch('/settings');
  return json.data ?? null;
}
