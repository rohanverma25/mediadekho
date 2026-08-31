import { apiFetch } from './api';

export async function fetchClientLogos(filters = {}) {
  const params = new URLSearchParams();
  Object.entries(filters).forEach(([key, value]) => {
    if (value !== null && value !== undefined && value !== '') {
      params.set(key, value);
    }
  });

  const query = params.toString();
  const json = await apiFetch(`/client-logos${query ? `?${query}` : ''}`);
  return json.data ?? [];
}
