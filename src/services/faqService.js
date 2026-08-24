import { apiFetch } from './api';

export async function fetchFaqs() {
  const json = await apiFetch('/faqs');
  return json.data ?? [];
}
