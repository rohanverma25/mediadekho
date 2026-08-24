import { useEffect, useState } from 'react';
import { fetchNews } from '../services/newsService';

export function useNews() {
  const [news, setNews] = useState([]);
  const [status, setStatus] = useState('loading'); // 'loading' | 'success' | 'error'

  useEffect(() => {
    let cancelled = false;

    fetchNews()
      .then((data) => {
        if (cancelled) return;
        setNews(data);
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

  return { news, status };
}
