import { useEffect, useState } from 'react';
import { fetchPageMeta } from '../services/pageMetaService';

/**
 * Fetches admin-configured SEO overrides (title/description/og image) for
 * one of the fixed static pages. A missing/unreachable config isn't
 * fatal — every caller already has a sensible hardcoded default to fall
 * back to, so this just resolves to `meta: null` on failure rather than
 * surfacing an error state.
 */
export function usePageMeta(pageKey) {
  const [meta, setMeta] = useState(null);
  const [status, setStatus] = useState('loading');

  useEffect(() => {
    let cancelled = false;
    setStatus('loading');

    fetchPageMeta(pageKey)
      .then((data) => {
        if (cancelled) return;
        setMeta(data);
        setStatus('success');
      })
      .catch(() => {
        if (cancelled) return;
        setMeta(null);
        setStatus('error');
      });

    return () => {
      cancelled = true;
    };
  }, [pageKey]);

  return { meta, status };
}
