import { useEffect, useState } from 'react';
import { fetchBlogBySlug } from '../services/blogService';

export function useBlog(slug) {
  const [blog, setBlog] = useState(null);
  const [status, setStatus] = useState(slug ? 'loading' : 'idle');

  useEffect(() => {
    if (!slug) {
      setBlog(null);
      setStatus('idle');
      return;
    }

    let cancelled = false;
    setStatus('loading');

    fetchBlogBySlug(slug)
      .then((data) => {
        if (cancelled) return;
        setBlog(data);
        setStatus('success');
      })
      .catch(() => {
        if (cancelled) return;
        setBlog(null);
        setStatus('error');
      });

    return () => {
      cancelled = true;
    };
  }, [slug]);

  return { blog, status };
}
