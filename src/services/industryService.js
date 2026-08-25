import { apiFetch } from './api';

export async function fetchIndustries() {
  const json = await apiFetch('/industries');
  return json.data ?? [];
}
