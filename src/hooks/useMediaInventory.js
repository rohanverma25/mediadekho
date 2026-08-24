import { useEffect, useState } from 'react';
import { fetchMediaInventory } from '../services/mediaInventoryService';

/**
 * `filters` is expected to be a plain object of query params (search,
 * category_id, subcategory_id, per_page, ...). It's re-serialized on every
 * render, so we key the effect off its JSON string rather than the object
 * reference to avoid refetching in a loop.
 */
export function useMediaInventory(filters = {}) {
  const [items, setItems] = useState([]);
  const [status, setStatus] = useState('loading'); // 'loading' | 'success' | 'error'
  const [error, setError] = useState(null);

  const filtersKey = JSON.stringify(filters);

  useEffect(() => {
    let cancelled = false;

    setStatus('loading');
    setError(null);

    fetchMediaInventory(JSON.parse(filtersKey))
      .then((data) => {
        if (cancelled) return;
        setItems(data);
        setStatus('success');
      })
      .catch((err) => {
        if (cancelled) return;
        setError(err);
        setStatus('error');
      });

    return () => {
      cancelled = true;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [filtersKey]);

  return { items, status, error };
}
