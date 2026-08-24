import { useEffect, useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { fetchMyEnquiries } from '../services/enquiryService';

export function useMyEnquiries() {
  const { isAuthenticated } = useAuth();
  const [enquiries, setEnquiries] = useState([]);
  const [status, setStatus] = useState(isAuthenticated ? 'loading' : 'idle');

  useEffect(() => {
    if (!isAuthenticated) {
      setEnquiries([]);
      setStatus('idle');
      return;
    }

    let cancelled = false;
    setStatus('loading');

    fetchMyEnquiries()
      .then((data) => {
        if (cancelled) return;
        setEnquiries(data);
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

  return { enquiries, status };
}
