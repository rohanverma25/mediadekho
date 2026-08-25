import { useCallback, useEffect, useState } from 'react';
import { fetchIndustries } from '../services/industryService';

export function useIndustries() {
  const [industries, setIndustries] = useState([]);
  const [status, setStatus] = useState('loading'); // 'loading' | 'success' | 'error'
  const [error, setError] = useState(null);

  const load = useCallback(async () => {
    setStatus('loading');
    setError(null);
    try {
      const data = await fetchIndustries();
      setIndustries(data);
      setStatus('success');
    } catch (err) {
      setError(err);
      setStatus('error');
    }
  }, []);

  useEffect(() => {
    load();
  }, [load]);

  return { industries, status, error, reload: load };
}
