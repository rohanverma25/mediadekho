import { useEffect, useState } from 'react';
import { fetchStats } from '../services/statService';

export function useStats() {
  const [stats, setStats] = useState([]);
  const [status, setStatus] = useState('loading'); // 'loading' | 'success' | 'error'

  useEffect(() => {
    let cancelled = false;

    fetchStats()
      .then((data) => {
        if (cancelled) return;
        setStats(data);
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

  return { stats, status };
}
