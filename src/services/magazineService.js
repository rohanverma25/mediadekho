import { apiFetch } from './api';

export async function fetchMagazines() {
  const json = await apiFetch('/magazines');
  return json.data ?? [];
}

export async function fetchMagazineBySlug(slug) {
  const json = await apiFetch(`/magazines/${slug}`);
  return json.data ?? null;
}
