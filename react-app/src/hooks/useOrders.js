import { useEffect, useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { fetchMyOrders } from '../services/orderService';

export function useOrders() {
  const { isAuthenticated } = useAuth();
  const [orders, setOrders] = useState([]);
  const [status, setStatus] = useState(isAuthenticated ? 'loading' : 'idle');

  useEffect(() => {
    if (!isAuthenticated) {
      setOrders([]);
      setStatus('idle');
      return;
    }

    let cancelled = false;
    setStatus('loading');

    fetchMyOrders()
      .then((json) => {
        if (cancelled) return;
        setOrders(json.data ?? []);
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

  return { orders, status };
}
