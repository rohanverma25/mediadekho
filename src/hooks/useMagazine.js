import { useEffect, useState } from 'react';
import { fetchMagazineBySlug } from '../services/magazineService';

export function useMagazine(slug) {
  const [magazine, setMagazine] = useState(null);
  const [status, setStatus] = useState(slug ? 'loading' : 'idle');

  useEffect(() => {
    if (!slug) {
      setMagazine(null);
      setStatus('idle');
      return;
    }

    let cancelled = false;
    setStatus('loading');

    fetchMagazineBySlug(slug)
      .then((data) => {
        if (cancelled) return;
        setMagazine(data);
        setStatus('success');
      })
      .catch(() => {
        if (cancelled) return;
        setMagazine(null);
        setStatus('error');
      });

    return () => {
      cancelled = true;
    };
  }, [slug]);

  return { magazine, status };
}
