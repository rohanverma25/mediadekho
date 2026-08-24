const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api';

// Read directly from localStorage (rather than importing AuthContext, which
// would create a circular dependency) so every API call automatically
// carries the logged-in user's token — this is what lets PricingService
// resolve their real tier instead of falling back to Retail.
export const AUTH_TOKEN_STORAGE_KEY = 'ep_auth_token';

export class ApiError extends Error {
  constructor(message, status, body) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.body = body;
  }
}

export async function apiFetch(path, options = {}) {
  const token = localStorage.getItem(AUTH_TOKEN_STORAGE_KEY);

  const response = await fetch(`${API_BASE_URL}${path}`, {
    ...options,
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...options.headers,
    },
  });

  if (!response.ok) {
    let body = null;
    try {
      body = await response.json();
    } catch {
      // Response had no JSON body (e.g. a 500 HTML error page).
    }
    throw new ApiError(body?.message || `Request failed with status ${response.status}`, response.status, body);
  }

  if (response.status === 204) {
    return null;
  }

  return response.json();
}
