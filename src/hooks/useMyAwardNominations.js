import { useEffect, useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { fetchMyAwardNominations } from '../services/awardService';

export function useMyAwardNominations() {
  const { isAuthenticated } = useAuth();
  const [nominations, setNominations] = useState([]);
  const [status, setStatus] = useState(isAuthenticated ? 'loading' : 'idle');

  useEffect(() => {
    if (!isAuthenticated) {
      setNominations([]);
      setStatus('idle');
      return;
    }

    let cancelled = false;
    setStatus('loading');

    fetchMyAwardNominations()
      .then((data) => {
        if (cancelled) return;
        setNominations(data);
        setStatus('success');
      })
      .catch(() => {
        if (cancelled) return;
        setStatus('error');
      });

    return () => {
      cancelled = true;
    };
  }, [isAuthenticated]);

  return { nominations, status };
}
