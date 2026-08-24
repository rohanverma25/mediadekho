import { apiFetch } from './api';

export async function fetchBlogs(filters = {}) {
  const params = new URLSearchParams();
  Object.entries(filters).forEach(([key, value]) => {
    if (value !== null && value !== undefined && value !== '') {
      params.set(key, value);
    }
  });

  const query = params.toString();
  const json = await apiFetch(`/blogs${query ? `?${query}` : ''}`);
  return json.data ?? [];
}

export async function fetchBlogBySlug(slug) {
  const json = await apiFetch(`/blogs/${slug}`);
  return json.data ?? null;
}
