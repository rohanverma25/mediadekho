import React from 'react';
import { Link } from 'react-router-dom';
import { useAnnouncements } from '../hooks/useAnnouncements';
import { useMyAwardNominations } from '../hooks/useMyAwardNominations';
import { useOrders } from '../hooks/useOrders';
import { AccountLayout } from '../components/AccountLayout';
import { Skeleton } from '../components/Skeleton';
import { useDocumentMeta } from '../hooks/useDocumentMeta';

const STATUS_BADGE = {
  new: 'bg-amber-100 text-amber-700',
  shortlisted: 'bg-emerald-100 text-emerald-700',
  rejected: 'bg-slate-200 text-slate-600',
};

const ORDER_STATUS_BADGE = {
  pending: 'bg-amber-100 text-amber-700',
  paid: 'bg-emerald-100 text-emerald-700',
  failed: 'bg-red-100 text-red-700',
  cancelled: 'bg-slate-200 text-slate-600',
  refunded: 'bg-slate-200 text-slate-600',
};

const formatEventDate = (dateStr) => {
  if (!dateStr) return null;
  return new Date(`${dateStr}T00:00:00`).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
};

export const DashboardPage = () => {
  useDocumentMeta({ title: 'Dashboard', noindex: true });

  const { announcements, status: announcementsStatus } = useAnnouncements();
  const { nominations, status: nominationsStatus } = useMyAwardNominations();
  const { orders, status: ordersStatus } = useOrders();

  const paidOrders = orders.filter((o) => o.status === 'paid');
  const totalSpend = paidOrders.reduce((sum, o) => sum + o.grand_total, 0);

  return (
    <AccountLayout active="dashboard">

      {/* ANNOUNCEMENTS & EVENTS */}
      {announcementsStatus === 'loading' && (
        <div className="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
          <div className="flex items-center gap-2 px-6 pt-5 pb-3">
            <i className="fa-solid fa-bullhorn text-brand-red"></i>
            <h3 className="font-outfit font-bold text-base text-slate-900">Announcements & Events</h3>
          </div>
          <div className="divide-y divide-slate-100">
            {Array.from({ length: 2 }).map((_, i) => (
              <div key={i} className="px-6 py-4 flex items-start gap-4">
                <Skeleton className="flex-shrink-0 w-14 h-12 rounded-xl" />
                <div className="min-w-0 flex-1 space-y-2">
                  <Skeleton className="h-4 w-1/3" />
                  <Skeleton className="h-3 w-full" />
                  <Skeleton className="h-3 w-2/3" />
                </div>
              </div>
            ))}
          </div>
        </div>
      )}
      {announcementsStatus === 'success' && announcements.length > 0 && (
        <div className="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
          <div className="flex items-center gap-2 px-6 pt-5 pb-3">
            <i className="fa-solid fa-bullhorn text-brand-red"></i>
            <h3 className="font-outfit font-bold text-base text-slate-900">Announcements & Events</h3>
          </div>
          <div className="divide-y divide-slate-100">
            {announcements.map((announcement) => (
              <div key={announcement.id} className="px-6 py-4 flex items-start gap-4">
                {announcement.event_date && (
                  <div className="flex-shrink-0 w-14 text-center bg-red-50 border border-red-100 rounded-xl py-1.5">
                    <span className="block text-[10px] font-bold text-brand-red uppercase">
                      {new Date(`${announcement.event_date}T00:00:00`).toLocaleDateString('en-IN', { month: 'short' })}
                    </span>
                    <span className="block text-base font-black text-slate-900 font-outfit leading-none">
                      {new Date(`${announcement.event_date}T00:00:00`).getDate()}
                    </span>
                  </div>
                )}
                <div className="min-w-0">
                  <h4 className="font-outfit font-bold text-sm text-slate-900">{announcement.title}</h4>
                  <div
                    className="text-xs text-slate-500 mt-0.5 leading-relaxed [&_a]:text-brand-red [&_a]:underline [&_p]:mb-1 [&_p:last-child]:mb-0"
                    dangerouslySetInnerHTML={{ __html: announcement.message }}
                  />
                  {announcement.event_date && (
                    <span className="text-[10px] text-slate-400 font-semibold uppercase mt-1 block">
                      {formatEventDate(announcement.event_date)}
                    </span>
                  )}
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* MY AWARD NOMINATIONS */}
      {nominationsStatus === 'loading' && (
        <div className="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
          <div className="flex items-center gap-2 px-6 pt-5 pb-3">
            <i className="fa-solid fa-trophy text-brand-red"></i>
            <h3 className="font-outfit font-bold text-base text-slate-900">My Award Nominations</h3>
          </div>
          <div className="divide-y divide-slate-100">
            {Array.from({ length: 2 }).map((_, i) => (
              <div key={i} className="px-6 py-4 flex items-start gap-4">
                <Skeleton className="flex-shrink-0 w-12 h-12 rounded-xl" />
                <div className="min-w-0 flex-1 space-y-2">
                  <Skeleton className="h-4 w-1/3" />
                  <Skeleton className="h-3 w-2/3" />
                </div>
              </div>
            ))}
          </div>
        </div>
      )}
      {nominationsStatus === 'success' && nominations.length > 0 && (
        <div className="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
          <div className="flex items-center justify-between px-6 pt-5 pb-3">
            <div className="flex items-center gap-2">
              <i className="fa-solid fa-trophy text-brand-red"></i>
              <h3 className="font-outfit font-bold text-base text-slate-900">My Award Nominations</h3>
            </div>
            <Link to="/awards" className="text-xs font-bold text-brand-red hover:underline">Browse Awards</Link>
          </div>
          <div className="divide-y divide-slate-100">
            {nominations.map((nomination) => (
              <div key={nomination.id} className="px-6 py-4 flex items-start gap-4">
                <img
                  src={nomination.award.image_url || 'https://images.unsplash.com/photo-1567427017947-545c5f8d16ad?auto=format&fit=crop&w=200&q=80'}
                  alt={nomination.award.title}
                  className="flex-shrink-0 w-12 h-12 rounded-xl object-cover border border-slate-200" />
                <div className="min-w-0 flex-1">
                  <div className="flex items-center gap-2 flex-wrap">
                    <h4 className="font-outfit font-bold text-sm text-slate-900">{nomination.award.title}</h4>
                    <span className={`text-[10px] font-extrabold px-2 py-0.5 rounded-full uppercase ${STATUS_BADGE[nomination.status] || 'bg-slate-200 text-slate-600'}`}>
                      {nomination.status}
                    </span>
                  </div>
                  <p className="text-xs text-slate-500 mt-0.5 line-clamp-2">{nomination.subject}</p>
                  <span className="text-[10px] text-slate-400 font-semibold uppercase mt-1 block">
                    Submitted {formatEventDate(nomination.created_at)}
                  </span>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* STATS */}
      <div className="grid grid-cols-2 gap-4">
        <div className="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
          <span className="text-[10px] text-slate-400 font-bold uppercase block">Total Orders</span>
          {ordersStatus === 'loading' ? (
            <Skeleton className="h-7 w-16 mt-1" />
          ) : (
            <div className="text-2xl font-black text-slate-900 font-outfit mt-1">{orders.length}</div>
          )}
        </div>

        <div className="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
          <span className="text-[10px] text-slate-400 font-bold uppercase block">Total Spend</span>
          {ordersStatus === 'loading' ? (
            <Skeleton className="h-7 w-24 mt-1" />
          ) : (
            <div className="text-2xl font-black text-brand-red font-outfit mt-1">₹{totalSpend.toLocaleString('en-IN')}</div>
          )}
          <span className="text-[10px] text-slate-500 font-medium mt-1 inline-block">Across paid orders</span>
        </div>
      </div>

      {/* RECENT ORDERS TABLE */}
      <div className="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
        <div className="flex justify-between items-center border-b border-slate-100 pb-3">
          <h3 className="font-outfit font-extrabold text-lg text-slate-900">Recent Order History</h3>
          <Link to="/orders" className="text-xs font-bold text-brand-red hover:underline">View All ({orders.length})</Link>
        </div>

        {ordersStatus === 'loading' && (
          <div className="space-y-3">
            {Array.from({ length: 3 }).map((_, i) => (
              <Skeleton key={i} className="h-10 w-full rounded-xl" />
            ))}
          </div>
        )}

        {ordersStatus === 'success' && orders.length === 0 && (
          <div className="text-center py-10">
            <i className="fa-solid fa-box-open text-3xl text-slate-300 mb-3 block"></i>
            <p className="text-sm text-slate-500 mb-3">No orders yet.</p>
            <Link to="/category" className="text-xs font-bold text-brand-red hover:underline">Browse Media Inventory</Link>
          </div>
        )}

        {ordersStatus === 'success' && orders.length > 0 && (
          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs">
              <thead>
                <tr className="border-b border-slate-200 text-slate-400 font-bold uppercase text-[10px]">
                  <th className="py-3 px-2">Order #</th>
                  <th className="py-3 px-2">Date</th>
                  <th className="py-3 px-2">Items</th>
                  <th className="py-3 px-2">Amount</th>
                  <th className="py-3 px-2">Status</th>
                  <th className="py-3 px-2 text-right">Action</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 text-slate-700 font-medium">
                {orders.slice(0, 3).map((order) => (
                  <tr key={order.id}>
                    <td className="py-3.5 px-2 font-bold text-slate-900">{order.order_number}</td>
                    <td className="py-3.5 px-2 text-slate-500">{formatDate(order.created_at)}</td>
                    <td className="py-3.5 px-2">
                      {order.items?.[0]?.title}
                      {order.items?.length > 1 ? ` +${order.items.length - 1} more` : ''}
                    </td>
                    <td className="py-3.5 px-2 font-bold font-outfit text-slate-900">₹{order.grand_total.toLocaleString('en-IN')}</td>
                    <td className="py-3.5 px-2">
                      <span className={`text-[10px] font-bold px-2 py-0.5 rounded-full uppercase ${ORDER_STATUS_BADGE[order.status] || 'bg-slate-200 text-slate-600'}`}>
                        {order.status}
                      </span>
                    </td>
                    <td className="py-3.5 px-2 text-right">
                      <Link to="/orders" className="text-brand-red font-bold hover:underline">View</Link>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

    </AccountLayout>
  );
};
