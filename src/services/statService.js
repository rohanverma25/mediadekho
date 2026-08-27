import { apiFetch } from './api';

export async function fetchStats() {
  const json = await apiFetch('/stats');
  return json.data ?? [];
}
