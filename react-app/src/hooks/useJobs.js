import { useEffect, useState } from 'react';
import { fetchJobs } from '../services/jobService';

export function useJobs() {
  const [jobs, setJobs] = useState([]);
  const [status, setStatus] = useState('loading'); // 'loading' | 'success' | 'error'

  useEffect(() => {
    let cancelled = false;

    fetchJobs()
      .then((data) => {
        if (cancelled) return;
        setJobs(data);
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

  return { jobs, status };
}
