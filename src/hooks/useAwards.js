import { useEffect, useState } from 'react';
import { fetchAwards } from '../services/awardService';

export function useAwards() {
  const [awards, setAwards] = useState([]);
  const [status, setStatus] = useState('loading'); // 'loading' | 'success' | 'error'

  useEffect(() => {
    let cancelled = false;

    fetchAwards()
      .then((data) => {
        if (cancelled) return;
        setAwards(data);
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

  return { awards, status };
}
