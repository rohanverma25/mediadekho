import { useEffect, useState } from 'react';
import { fetchVideos } from '../services/videoService';

export function useVideos() {
  const [videos, setVideos] = useState([]);
  const [status, setStatus] = useState('loading'); // 'loading' | 'success' | 'error'

  useEffect(() => {
    let cancelled = false;

    fetchVideos()
      .then((data) => {
        if (cancelled) return;
        setVideos(data);
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

  return { videos, status };
}
