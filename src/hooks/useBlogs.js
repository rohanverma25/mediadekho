import { useEffect, useState } from 'react';
import { fetchBlogs } from '../services/blogService';

export function useBlogs(filters = {}) {
  const [blogs, setBlogs] = useState([]);
  const [status, setStatus] = useState('loading'); // 'loading' | 'success' | 'error'
  const [error, setError] = useState(null);

  const filtersKey = JSON.stringify(filters);

  useEffect(() => {
    let cancelled = false;

    setStatus('loading');
    setError(null);

    fetchBlogs(JSON.parse(filtersKey))
      .then((data) => {
        if (cancelled) return;
        setBlogs(data);
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

  return { blogs, status, error };
}
