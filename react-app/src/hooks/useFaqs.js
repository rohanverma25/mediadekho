import { useEffect, useState } from 'react';
import { fetchFaqs } from '../services/faqService';

export function useFaqs() {
  const [faqs, setFaqs] = useState([]);
  const [status, setStatus] = useState('loading'); // 'loading' | 'success' | 'error'

  useEffect(() => {
    let cancelled = false;

    fetchFaqs()
      .then((data) => {
        if (cancelled) return;
        setFaqs(data);
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

  return { faqs, status };
}
