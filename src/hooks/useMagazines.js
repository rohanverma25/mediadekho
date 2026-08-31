import { useEffect, useState } from 'react';
import { fetchMagazines } from '../services/magazineService';

export function useMagazines() {
  const [magazines, setMagazines] = useState([]);
  const [status, setStatus] = useState('loading'); // 'loading' | 'success' | 'error'

  useEffect(() => {
    let cancelled = false;

    fetchMagazines()
      .then((data) => {
        if (cancelled) return;
        setMagazines(data);
        setStatus('success');
      })
      .catch(() => {
        if (cancelled) return;
        setStatus('error');
      });

    return () => {
      cancelled = true;
    };
  }, []);

  return { magazines, status };
}
