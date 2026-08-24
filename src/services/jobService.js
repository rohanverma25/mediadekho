import { apiFetch, AUTH_TOKEN_STORAGE_KEY, ApiError } from './api';

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api';

export async function fetchJobs() {
  const json = await apiFetch('/jobs');
  return json.data ?? [];
}

/**
 * Multipart submission (resume upload) — bypasses apiFetch's JSON
 * Content-Type default since the browser needs to set its own
 * multipart/form-data boundary for FormData bodies.
 */
export async function submitJobApplication({ jobId, name, email, phone, resume, coverLetter }) {
  const formData = new FormData();
  formData.append('job_id', jobId);
  formData.append('name', name);
  formData.append('email', email);
  if (phone) formData.append('phone', phone);
  if (coverLetter) formData.append('cover_letter', coverLetter);
  if (resume) formData.append('resume', resume);

  const token = localStorage.getItem(AUTH_TOKEN_STORAGE_KEY);

  const response = await fetch(`${API_BASE_URL}/job-applications`, {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
    body: formData,
  });

  const body = await response.json().catch(() => null);

  if (!response.ok) {
    throw new ApiError(body?.message || `Request failed with status ${response.status}`, response.status, body);
  }

  return body;
}
