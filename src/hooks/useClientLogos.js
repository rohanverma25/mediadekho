import { useCallback, useEffect, useState } from 'react';
import { fetchClientLogos } from '../services/clientLogoService';

export function useClientLogos() {
  const [logos, setLogos] = useState([]);
  const [status, setStatus] = useState('loading'); // 'loading' | 'success' | 'error'
  const [error, setError] = useState(null);

  const load = useCallback(async () => {
    setStatus('loading');
    setError(null);
    try {
      const data = await fetchClientLogos();
      setLogos(data);
      setStatus('success');
    } catch (err) {
      setError(err);
      setStatus('error');
    }
  }, []);

  useEffect(() => {
    load();
  }, [load]);

  return { logos, status, error, reload: load };
}
