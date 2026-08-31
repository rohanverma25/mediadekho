import { AUTH_TOKEN_STORAGE_KEY, ApiError } from './api';

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api';

/**
 * Multipart submission (photo + media kit upload) — bypasses apiFetch's JSON
 * Content-Type default since the browser needs to set its own
 * multipart/form-data boundary for FormData bodies.
 */
export async function submitMediaListingRequest({
  companyName,
  contactName,
  email,
  phone,
  mediaTitle,
  mediaType,
  location,
  approximateRate,
  description,
  image,
  mediaKit,
}) {
  const formData = new FormData();
  formData.append('company_name', companyName);
  formData.append('contact_name', contactName);
  formData.append('email', email);
  formData.append('phone', phone);
  formData.append('media_title', mediaTitle);
  if (mediaType) formData.append('media_type', mediaType);
  if (location) formData.append('location', location);
  if (approximateRate) formData.append('approximate_rate', approximateRate);
  if (description) formData.append('description', description);
  if (image) formData.append('image', image);
  if (mediaKit) formData.append('media_kit', mediaKit);

  const token = localStorage.getItem(AUTH_TOKEN_STORAGE_KEY);

  const response = await fetch(`${API_BASE_URL}/media-listing-requests`, {
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
