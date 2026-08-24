import { apiFetch } from './api';

export async function fetchNews() {
  const json = await apiFetch('/news');
  return json.data ?? [];
}
