import { useCallback, useEffect, useState } from 'react';
import { fetchClientLogos } from '../services/clientLogoService';

/**
 * `filters` is expected to be a plain object (currently just `industry_id`).
 * Re-serialized on every render, so the effect is keyed off its JSON string
 * rather than the object reference to avoid refetching in a loop.
 */
export function useClientLogos(filters = {}) {
  const [logos, setLogos] = useState([]);
  const [status, setStatus] = useState('loading'); // 'loading' | 'success' | 'error'
  const [error, setError] = useState(null);

  const filtersKey = JSON.stringify(filters);

  const load = useCallback(async () => {
    setStatus('loading');
    setError(null);
    try {
      const data = await fetchClientLogos(JSON.parse(filtersKey));
      setLogos(data);
      setStatus('success');
    } catch (err) {
      setError(err);
      setStatus('error');
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [filtersKey]);

  useEffect(() => {
    load();
  }, [load]);

  return { logos, status, error, reload: load };
}
