import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useOrders } from '../hooks/useOrders';
import { AccountLayout } from '../components/AccountLayout';
import { Skeleton } from '../components/Skeleton';

const STATUSES = ['all', 'pending', 'paid', 'failed', 'cancelled', 'refunded'];

const STATUS_BADGE = {
  pending: 'bg-amber-100 text-amber-700',
  paid: 'bg-emerald-100 text-emerald-700',
  failed: 'bg-red-100 text-red-700',
  cancelled: 'bg-slate-200 text-slate-600',
  refunded: 'bg-slate-200 text-slate-600',
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
};

export const OrdersPage = () => {
  const { orders, status } = useOrders();
  const [activeTab, setActiveTab] = useState('all');
  const [searchQuery, setSearchQuery] = useState('');

  const filteredOrders = orders.filter((order) => {
    if (activeTab !== 'all' && order.status !== activeTab) return false;
    if (searchQuery) {
      const q = searchQuery.toLowerCase();
      const matchesOrder = order.order_number.toLowerCase().includes(q);
      const matchesItem = order.items?.some((item) => item.title.toLowerCase().includes(q));
      if (!matchesOrder && !matchesItem) return false;
    }
    return true;
  });

  return (
    <AccountLayout active="orders">

      {/* FILTER TABS & SEARCH */}
      <div className="bg-white p-4 rounded-3xl border border-slate-200 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4">
        <div className="flex flex-wrap items-center gap-2 w-full md:w-auto">
          {STATUSES.map((s) => (
            <button
              key={s}
              onClick={() => setActiveTab(s)}
              className={`text-xs font-bold px-4 py-2 rounded-xl transition cursor-pointer capitalize ${
                activeTab === s ? 'bg-brand-red text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
              }`}>
              {s === 'all' ? `All Orders (${orders.length})` : s}
            </button>
          ))}
        </div>

        <div className="relative w-full md:w-64">
          <input
            type="text"
            placeholder="Search order # or media..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="glass-input w-full rounded-xl px-3.5 py-2 text-xs pl-9" />
          <i className="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
        </div>
      </div>

      {/* ORDERS LIST */}
      {status === 'loading' && (
        <div className="space-y-4">
          {Array.from({ length: 2 }).map((_, i) => (
            <Skeleton key={i} className="h-40 w-full rounded-3xl" />
          ))}
        </div>
      )}

      {status === 'success' && filteredOrders.length === 0 && (
        <div className="bg-white p-12 rounded-3xl border border-slate-200 shadow-sm text-center space-y-4">
          <div className="w-16 h-16 rounded-full bg-red-50 border border-red-100 flex items-center justify-center text-brand-red text-2xl mx-auto">
            <i className="fa-solid fa-box-open"></i>
          </div>
          <h3 className="font-outfit font-extrabold text-2xl text-slate-900">
            {orders.length === 0 ? 'No orders yet' : 'No orders match your filters'}
          </h3>
          <p className="text-xs text-slate-500 max-w-md mx-auto leading-relaxed">
            Browse our verified media inventory to start your first booking.
          </p>
          <Link to="/category" className="inline-flex items-center gap-2 bg-brand-red hover:bg-brand-red-dark text-white font-outfit font-bold text-xs px-6 py-3 rounded-xl shadow-lg shadow-brand-red/25 transition">
            <span>Browse All Media Options</span>
            <i className="fa-solid fa-arrow-right text-xs"></i>
          </Link>
        </div>
      )}

      {status === 'success' && filteredOrders.length > 0 && (
        <div className="space-y-6">
          {filteredOrders.map((order) => (
            <div key={order.id} className="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden space-y-6 p-6 sm:p-8">

              <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-slate-100 pb-4">
                <div>
                  <div className="flex items-center gap-2 mb-1">
                    <span className={`text-xs font-black px-3 py-1 rounded-full uppercase ${STATUS_BADGE[order.status] || 'bg-slate-200 text-slate-600'}`}>
                      ● {order.status}
                    </span>
                    <span className="text-xs text-slate-400 font-medium">Placed: {formatDate(order.created_at)}</span>
                  </div>
                  <h3 className="font-outfit font-black text-xl text-slate-900">
                    Order: {order.order_number}
                  </h3>
                </div>

                <div className="text-right">
                  <span className="text-[10px] text-slate-400 font-bold uppercase block">Total</span>
                  <span className="font-outfit font-black text-2xl text-brand-red">₹{order.grand_total.toLocaleString('en-IN')}</span>
                </div>
              </div>

              {/* ITEMS */}
              <div className="space-y-3">
                <h4 className="text-xs font-bold text-slate-500 uppercase tracking-wider">Items in this Order:</h4>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  {order.items?.map((item) => (
                    <div key={item.id} className="p-4 bg-slate-50 rounded-2xl border border-slate-200 flex items-center justify-between gap-4">
                      <div>
                        <h5 className="font-bold text-xs text-slate-900">{item.title}</h5>
                        <span className="text-[11px] text-slate-500 block">{item.category} • Qty {item.quantity}</span>
                        <span className="text-xs font-extrabold text-slate-900 font-outfit">₹{item.line_total.toLocaleString('en-IN')}</span>
                      </div>
                    </div>
                  ))}
                </div>
              </div>

            </div>
          ))}
        </div>
      )}

    </AccountLayout>
  );
};
