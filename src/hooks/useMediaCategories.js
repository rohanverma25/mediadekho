import { useCallback, useEffect, useState } from 'react';
import { fetchMediaCategories } from '../services/mediaCategoryService';

export function useMediaCategories() {
  const [categories, setCategories] = useState([]);
  const [status, setStatus] = useState('loading'); // 'loading' | 'success' | 'error'
  const [error, setError] = useState(null);

  const load = useCallback(async () => {
    setStatus('loading');
    setError(null);
    try {
      const data = await fetchMediaCategories();
      setCategories(data);
      setStatus('success');
    } catch (err) {
      setError(err);
      setStatus('error');
    }
  }, []);

  useEffect(() => {
    load();
  }, [load]);

  return { categories, status, error, reload: load };
}
