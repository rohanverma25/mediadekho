import React from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { useSettings } from '../context/SettingsContext';
import { useOrders } from '../hooks/useOrders';

const DEFAULT_PHONE = '+91 89800 04451';

const getInitials = (name) => {
  if (!name) return 'MD';
  const parts = name.trim().split(/\s+/);
  return parts
    .slice(0, 2)
    .map((p) => p[0]?.toUpperCase())
    .join('');
};

const NAV_ITEMS = [
  { key: 'dashboard', to: '/dashboard', icon: 'fa-chart-pie', label: 'Dashboard Overview' },
  { key: 'orders', to: '/orders', icon: 'fa-box-archive', label: 'My Orders' },
  { key: 'enquiries', to: '/enquiries', icon: 'fa-comments', label: 'My Enquiries' },
  { key: 'profile', to: '/profile', icon: 'fa-user-pen', label: 'Update Profile' },
];

export const AccountLayout = ({ active, children }) => {
  const { user, logout } = useAuth();
  const { settings, status: settingsStatus } = useSettings();
  const { orders } = useOrders();
  const navigate = useNavigate();
  const phone = settings?.contact_phone || DEFAULT_PHONE;

  const handleLogout = async (e) => {
    e.preventDefault();
    await logout();
    navigate('/login');
  };

  return (
    <div>
      {/* ACCOUNT HEADER BANNER */}
      <section className="bg-gradient-to-b from-slate-100 to-slate-50 border-b border-slate-200 py-8 px-4 sm:px-6">
        <div className="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-6">

          <div className="flex items-center gap-4">
            <div className="w-16 h-16 rounded-2xl bg-brand-red text-white flex items-center justify-center font-outfit font-black text-2xl shadow-xl shadow-brand-red/20">
              {getInitials(user?.name)}
            </div>
            <div>
              <div className="flex items-center gap-2">
                <h1 className="font-outfit font-black text-2xl sm:text-3xl text-slate-900">Welcome Back, {user?.name?.split(' ')[0] || 'Guest'}!</h1>
                <span className="bg-emerald-100 text-emerald-700 text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase">
                  {user?.roles?.[0] || 'Verified Account'}
                </span>
              </div>
              <p className="text-xs text-slate-500 mt-1">{user?.email || 'Account ID unavailable'}</p>
            </div>
          </div>

          <div className="flex items-center gap-3">
            <Link to="/orders" className="bg-slate-900 hover:bg-slate-800 text-white font-outfit font-bold text-xs px-4 py-2.5 rounded-xl transition flex items-center gap-1.5 shadow-md">
              <i className="fa-solid fa-box-archive text-xs"></i>
              <span>View All Orders</span>
            </Link>
            <Link to="/category" className="bg-brand-red hover:bg-brand-red-dark text-white font-outfit font-bold text-xs px-5 py-2.5 rounded-xl shadow-lg shadow-brand-red/25 transition flex items-center gap-1.5">
              <i className="fa-solid fa-plus text-xs"></i>
              <span>Book New Media</span>
            </Link>
          </div>

        </div>
      </section>

      {/* ACCOUNT MAIN CONTENT GRID */}
      <section className="py-10 px-4 sm:px-6">
        <div className="max-w-7xl mx-auto">

          <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            {/* SIDEBAR NAVIGATION (3 COLS) */}
            <aside className="lg:col-span-3 space-y-4">
              <div className="bg-white p-4 rounded-3xl border border-slate-200 shadow-sm space-y-1">
                {NAV_ITEMS.map((item) => (
                  <Link
                    key={item.key}
                    to={item.to}
                    className={`flex items-center justify-between px-4 py-3 rounded-2xl text-xs font-bold transition ${
                      active === item.key
                        ? 'text-brand-red bg-red-50 border border-red-100'
                        : 'text-slate-700 font-semibold hover:bg-slate-100'
                    }`}>
                    <span className="flex items-center gap-3">
                      <i className={`fa-solid ${item.icon} ${active === item.key ? '' : 'text-slate-400'} text-sm`}></i>
                      <span>{item.label}</span>
                    </span>
                    {item.key === 'orders' && orders.length > 0 && (
                      <span className="bg-brand-red text-white text-[10px] font-black px-2 py-0.5 rounded-full">{orders.length}</span>
                    )}
                  </Link>
                ))}
                <button onClick={handleLogout} className="w-full flex items-center gap-3 px-4 py-2.5 rounded-2xl text-xs font-bold text-slate-500 hover:text-brand-red transition border-t border-slate-100 pt-2 mt-2 text-left cursor-pointer">
                  <i className="fa-solid fa-right-from-bracket text-xs"></i>
                  <span>Log Out Account</span>
                </button>
              </div>

              {/* HELP CARD */}
              <div className="bg-slate-900 text-white p-5 rounded-3xl space-y-3 shadow-xl">
                <span className="text-[10px] text-brand-red font-bold uppercase tracking-wider block">Need Help?</span>
                <p className="text-xs text-slate-300">Our team is here to help with bookings, pricing, or anything else.</p>
                {settingsStatus === 'loading' ? (
                  <div className="w-full bg-slate-800 py-2 rounded-xl flex items-center justify-center">
                    <span className="h-3 w-24 rounded bg-white/15 animate-pulse" />
                  </div>
                ) : (
                  <a href={`tel:${phone.replace(/\s+/g, '')}`} className="w-full bg-brand-red hover:bg-brand-red-dark text-white font-outfit font-bold text-xs py-2 rounded-xl transition flex items-center justify-center gap-1.5">
                    <i className="fa-solid fa-phone text-[10px]"></i>
                    <span>Call Helpline</span>
                  </a>
                )}
              </div>
            </aside>

            {/* MAIN CONTENT (9 COLS) */}
            <main className="lg:col-span-9 space-y-6">
              {children}
            </main>

          </div>

        </div>
      </section>
    </div>
  );
};
