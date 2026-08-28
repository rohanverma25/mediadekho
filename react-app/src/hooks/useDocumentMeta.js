import { useEffect } from 'react';

const SITE_NAME = 'Media Dekho';
export const DEFAULT_TITLE = "Media Dekho | India's #1 Media Buying Agency & Ad Aggregator";
export const DEFAULT_DESCRIPTION =
  'Plan, compare, and book advertising spots instantly across Offline, Digital, Sports & Gifting with 100% verified direct owner rates.';

function upsertMeta(attr, key, content) {
  const existing = document.querySelector(`meta[${attr}="${key}"]`);

  if (!content) {
    existing?.remove();
    return;
  }

  if (existing) {
    existing.setAttribute('content', content);
    return;
  }

  const tag = document.createElement('meta');
  tag.setAttribute(attr, key);
  tag.setAttribute('content', content);
  document.head.appendChild(tag);
}

/**
 * Sets document.title plus description/Open Graph/Twitter Card meta tags
 * for the current page. This is a plain client-side SPA (no SSR), so these
 * tags only ever reflect whatever the most recently mounted page set them
 * to — every route that wants correct search-result/share-preview text
 * needs to call this itself; there's no automatic per-route behavior.
 *
 * `title` is appended with " | Media Dekho" automatically — pass just the
 * page-specific part (e.g. "Contact Us"), not the full string. Omit it
 * (or any other field) to fall back to the site-wide default.
 */
export function useDocumentMeta({ title, description, image, noindex = false } = {}) {
  useEffect(() => {
    const fullTitle = title ? `${title} | ${SITE_NAME}` : DEFAULT_TITLE;
    const desc = description || DEFAULT_DESCRIPTION;

    document.title = fullTitle;

    upsertMeta('name', 'description', desc);
    upsertMeta('property', 'og:site_name', SITE_NAME);
    upsertMeta('property', 'og:type', 'website');
    upsertMeta('property', 'og:title', fullTitle);
    upsertMeta('property', 'og:description', desc);
    upsertMeta('property', 'og:image', image || null);
    upsertMeta('name', 'twitter:card', image ? 'summary_large_image' : 'summary');
    upsertMeta('name', 'twitter:title', fullTitle);
    upsertMeta('name', 'twitter:description', desc);
    upsertMeta('name', 'twitter:image', image || null);
    upsertMeta('name', 'robots', noindex ? 'noindex, nofollow' : 'index, follow');
  }, [title, description, image, noindex]);
}
