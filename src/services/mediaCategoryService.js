import { apiFetch } from './api';

export async function fetchMediaCategories() {
  const json = await apiFetch('/media-categories');
  return json.data ?? [];
}
