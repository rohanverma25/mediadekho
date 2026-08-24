import { apiFetch } from './api';

export async function fetchAnnouncements() {
  const json = await apiFetch('/announcements');
  return json.data ?? [];
}
