import { apiFetch } from './api';

export async function fetchMyEnquiries() {
  const json = await apiFetch('/my/contact-leads');
  return json.data ?? [];
}
