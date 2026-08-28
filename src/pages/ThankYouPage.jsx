import React, { useEffect, useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { fetchOrder } from '../services/orderService';
import { Skeleton } from '../components/Skeleton';
import { useDocumentMeta } from '../hooks/useDocumentMeta';

const STATUS_BADGE = {
  pending: 'bg-amber-100 text-amber-700',
  paid: 'bg-emerald-100 text-emerald-700',
  failed: 'bg-red-100 text-red-700',
  cancelled: 'bg-slate-200 text-slate-600',
  refunded: 'bg-slate-200 text-slate-600',
};

export const ThankYouPage = () => {
  useDocumentMeta({ title: 'Order Confirmed', noindex: true });

  const [searchParams] = useSearchParams();
  const orderNumber = searchParams.get('order');

  const [order, setOrder] = useState(null);
  const [status, setStatus] = useState(orderNumber ? 'loading' : 'error');

  useEffect(() => {
    if (!orderNumber) {
      setStatus('error');
      return;
    }

    let cancelled = false;
    setStatus('loading');

    fetchOrder(orderNumber)
      .then((json) => {
        if (cancelled) return;
        setOrder(json.data ?? null);
        setStatus('success');
      })
      .catch(() => {
        if (cancelled) return;
        setStatus('error');
      });

    return () => {
      cancelled = true;
    };
  }, [orderNumber]);

  if (status === 'loading') {
    return (
      <div className="py-16 px-4 sm:px-6 max-w-2xl mx-auto space-y-4">
        <Skeleton className="h-24 w-full rounded-3xl" />
        <Skeleton className="h-40 w-full rounded-3xl" />
      </div>
    );
  }

  if (status === 'error' || !order) {
    return (
      <div className="py-24 px-4 sm:px-6">
        <div className="max-w-lg mx-auto bg-white p-12 rounded-3xl border border-slate-200 shadow-sm text-center space-y-4">
          <i className="fa-solid fa-circle-exclamation text-4xl text-brand-red"></i>
          <h1 className="font-outfit font-bold text-xl text-slate-900">We couldn't find that order</h1>
          <p className="text-sm text-slate-500">The order may not exist, or it doesn't belong to your account.</p>
          <Link to="/orders" className="inline-block bg-brand-red text-white font-bold text-xs px-5 py-2.5 rounded-xl cursor-pointer">
            View My Orders
          </Link>
        </div>
      </div>
    );
  }

  const isPaid = order.status === 'paid';

  return (
    <div className="py-16 px-4 sm:px-6">
      <div className="max-w-2xl mx-auto space-y-6">

        <div className="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm text-center space-y-3">
          <div className={`w-16 h-16 rounded-full flex items-center justify-center text-2xl mx-auto ${isPaid ? 'bg-emerald-50 border border-emerald-100 text-emerald-600' : 'bg-amber-50 border border-amber-100 text-amber-600'}`}>
            <i className={`fa-solid ${isPaid ? 'fa-circle-check' : 'fa-triangle-exclamation'}`}></i>
          </div>
          <h1 className="font-outfit font-black text-2xl text-slate-900">
            {isPaid ? 'Payment Successful!' : 'Payment Not Confirmed'}
          </h1>
          <p className="text-sm text-slate-500">
            {isPaid
              ? `Thank you for your booking. Your order ${order.order_number} has been confirmed.`
              : `We couldn't confirm payment for order ${order.order_number}. If you were charged, please contact support with your payment details.`}
          </p>
        </div>

        <div className="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
          <div className="flex items-center justify-between border-b border-slate-100 pb-3">
            <span className="text-xs font-bold text-slate-500 uppercase">Order {order.order_number}</span>
            <span className={`text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase ${STATUS_BADGE[order.status] || 'bg-slate-200 text-slate-600'}`}>
              {order.status}
            </span>
          </div>

          <div className="space-y-2">
            {order.items?.map((item) => (
              <div key={item.id} className="flex justify-between text-xs">
                <span className="text-slate-700 font-medium">{item.title} × {item.quantity}</span>
                <span className="font-bold text-slate-900">₹{item.line_total.toLocaleString('en-IN')}</span>
              </div>
            ))}
          </div>

          <div className="border-t border-slate-200 pt-3 flex justify-between items-baseline">
            <span className="text-xs font-bold text-slate-700">Total Paid:</span>
            <span className="font-outfit font-black text-xl text-brand-red">₹{order.grand_total.toLocaleString('en-IN')}</span>
          </div>
        </div>

        <Link
          to="/orders"
          className="w-full inline-flex items-center justify-center gap-2 bg-brand-red hover:bg-brand-red-dark text-white font-outfit font-bold text-sm py-3.5 rounded-2xl shadow-lg shadow-brand-red/25 transition">
          <i className="fa-solid fa-box-archive text-xs"></i>
          <span>View All Orders</span>
        </Link>

      </div>
    </div>
  );
};
