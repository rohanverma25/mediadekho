import { apiFetch } from './api';

export async function fetchVideos() {
  const json = await apiFetch('/videos');
  return json.data ?? [];
}
