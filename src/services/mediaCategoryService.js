import { apiFetch } from './api';

export async function fetchMediaCategories() {
  const json = await apiFetch('/media-categories');
  return json.data ?? [];
}

export async function fetchMediaCategoryBySlug(slug) {
  const json = await apiFetch(`/media-categories/${slug}`);
  return json.data ?? null;
}
