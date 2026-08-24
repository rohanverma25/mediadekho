import { apiFetch } from './api';

export async function fetchClientLogos() {
  const json = await apiFetch('/client-logos');
  return json.data ?? [];
}
