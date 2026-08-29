import { apiFetch } from './api';

export async function fetchPageMeta(pageKey) {
  const json = await apiFetch(`/page-meta/${pageKey}`);
  return json.data ?? null;
}
