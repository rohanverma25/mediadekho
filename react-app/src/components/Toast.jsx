import React from 'react';
import { useCart } from '../context/CartContext';

export const Toast = () => {
  const { toasts } = useCart();

  if (toasts.length === 0) return null;

  return (
    <div className="fixed top-20 right-5 z-50 flex flex-col gap-2 pointer-events-none">
      {toasts.map((toast) => {
        const bgColor = toast.type === 'success' ? 'bg-emerald-600' : 'bg-brand-red';
        const icon = toast.type === 'success' ? 'fa-circle-check' : 'fa-circle-info';

        return (
          <div
            key={toast.id}
            className={`${bgColor} text-white text-xs md:text-sm font-semibold px-4 py-3 rounded-xl shadow-2xl flex items-center gap-2.5 transition-all duration-300 animate-in slide-in-from-top-5 duration-300`}>
            <i className={`fa-solid ${icon} text-base`}></i>
            <span>{toast.message}</span>
          </div>
        );
      })}
    </div>
  );
};
