import { useEffect, useState } from 'react';
import { fetchAnnouncements } from '../services/announcementService';

export function useAnnouncements() {
  const [announcements, setAnnouncements] = useState([]);
  const [status, setStatus] = useState('loading'); // 'loading' | 'success' | 'error'

  useEffect(() => {
    let cancelled = false;

    fetchAnnouncements()
      .then((data) => {
        if (cancelled) return;
        setAnnouncements(data);
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

  return { announcements, status };
}
