import React from 'react';
import { Link, useLocation } from 'react-router-dom';
import { useCart } from '../context/CartContext';
import { useAuth } from '../context/AuthContext';
import { useSettings } from '../context/SettingsContext';
import { Skeleton } from './Skeleton';

const DEFAULT_PHONE = '+91 89800 04451';
const DEFAULT_EMAIL = 'inquiry@mediadekho.com';
const DEFAULT_ADDRESS = 'Venus Atlantis Corporate Park, Prahlad Nagar, Ahmedabad, Gujarat 380054';

// The contact bar sits on a solid brand-red background, so the shared
// (light-gray) Skeleton isn't visible there — this is a translucent-white
// variant sized for a single line of text.
const BarSkeleton = ({ className = '' }) => (
  <span className={`inline-block h-3 rounded bg-white/25 animate-pulse ${className}`} />
);

export const Header = () => {
  const location = useLocation();
  const { cart, setIsSearchOpen, setIsInquiryOpen, setIsCartDrawerOpen } = useCart();
  const { isAuthenticated, user } = useAuth();
  const { settings, status: settingsStatus } = useSettings();

  const isActive = (path) => location.pathname === path;

  const settingsLoading = settingsStatus === 'loading';
  const phone = settings?.contact_phone || DEFAULT_PHONE;
  const email = settings?.contact_email || DEFAULT_EMAIL;
  const address = settings?.contact_address || DEFAULT_ADDRESS;

  return (
    <>
      {/* Top Contact Bar */}
      <div className="bg-brand-red text-white text-xs py-2">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 flex flex-wrap items-center justify-center sm:justify-start gap-x-4 gap-y-1 sm:gap-6">
          {settingsLoading ? (
            <span className="flex items-center gap-1.5">
              <i className="fa-solid fa-phone opacity-40"></i>
              <BarSkeleton className="w-28" />
            </span>
          ) : (
            <a href={`tel:${phone.replace(/\s+/g, '')}`} className="hover:text-red-100 transition flex items-center gap-1.5 font-medium">
              <i className="fa-solid fa-phone"></i>
              <span>{phone}</span>
            </a>
          )}
          {settingsLoading ? (
            <span className="hidden sm:flex items-center gap-1.5">
              <i className="fa-solid fa-envelope opacity-40"></i>
              <BarSkeleton className="w-40" />
            </span>
          ) : (
            <a href={`mailto:${email}`} className="hover:text-red-100 transition hidden sm:flex items-center gap-1.5 font-medium">
              <i className="fa-solid fa-envelope"></i>
              <span>{email}</span>
            </a>
          )}
          <span className="hidden md:inline text-red-300">|</span>
          {settingsLoading ? (
            <span className="hidden md:flex items-center gap-1.5">
              <i className="fa-solid fa-location-dot opacity-40"></i>
              <BarSkeleton className="w-64" />
            </span>
          ) : (
            <span className="hidden md:flex items-center gap-1.5 text-red-100 font-medium">
              <i className="fa-solid fa-location-dot"></i>
              <span>{address}</span>
            </span>
          )}
        </div>
      </div>

      {/* Main Header / Navigation */}
      <header className="sticky top-0 z-40 bg-white/95 border-b border-slate-200 shadow-sm backdrop-blur-xl">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between gap-3">
          
          {/* Brand Logo */}
          <Link to="/" className="flex items-center gap-2.5 group flex-shrink-0">
            {settingsLoading ? (
              <Skeleton className="h-10 w-32 rounded-xl" />
            ) : settings?.logo_url ? (
              <img
                src={settings.logo_url}
                alt="Media Dekho"
                className="h-10 w-auto max-w-[160px] object-contain group-hover:scale-105 transition-transform"
              />
            ) : (
              <>
                <div className="w-10 h-10 rounded-xl bg-brand-red flex items-center justify-center text-white font-outfit font-black text-xl shadow-lg shadow-brand-red/30 group-hover:scale-105 transition-transform">
                  MD
                </div>
                <div>
                  <span className="font-outfit font-black text-xl tracking-tight text-slate-900 block leading-none">MEDIA</span>
                  <span className="font-outfit font-bold text-xs tracking-widest text-brand-red uppercase block">DEKHO</span>
                </div>
              </>
            )}
          </Link>

          {/* Quick Search Trigger Input */}
          <div className="hidden lg:flex items-center flex-1 max-w-xs xl:max-w-sm mx-2">
            <button 
              onClick={() => setIsSearchOpen(true)}
              className="open-search-modal w-full glass-input rounded-2xl px-3.5 py-2 text-left text-xs text-slate-500 flex items-center justify-between hover:border-slate-400 transition group shadow-sm bg-slate-50">
              <span className="flex items-center gap-2">
                <i className="fa-solid fa-magnifying-glass text-slate-400 group-hover:text-brand-red transition text-xs"></i>
                <span className="truncate">Search 300,000+ media options...</span>
              </span>
              <kbd className="bg-slate-200/80 text-slate-600 px-1.5 py-0.5 rounded text-[10px] font-mono border border-slate-300">ctrl K</kbd>
            </button>
          </div>

          {/* Header CTAs Right */}
          <div className="flex items-center gap-2.5">
            {/* Real Shopping Cart Drawer Button */}
            <button 
              onClick={() => setIsCartDrawerOpen(true)}
              title="Cart"
              className="relative bg-slate-100 p-2.5 rounded-2xl hover:bg-slate-200 border border-slate-200 text-slate-700 hover:text-brand-red transition flex items-center justify-center cursor-pointer">
              <i className="fa-solid fa-cart-shopping text-base"></i>
              {cart.length > 0 && (
                <span className="absolute -top-1.5 -right-1.5 bg-brand-red text-white text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center shadow-md">
                  {cart.length}
                </span>
              )}
            </button>

            {/* Login Account Icon Button */}
            <Link
              to={isAuthenticated ? '/dashboard' : '/login'}
              title={isAuthenticated ? `${user?.name || 'My Account'}` : 'Log In / Account'}
              className={`bg-slate-100 p-2.5 rounded-2xl hover:bg-slate-200 border border-slate-200 transition flex items-center justify-center ${
                isActive('/login') || isActive('/dashboard') ? 'text-brand-red border-brand-red bg-red-50' : 'text-slate-700 hover:text-brand-red'
              }`}>
              <i className={`fa-solid ${isAuthenticated ? 'fa-circle-user' : 'fa-user'} text-base`}></i>
            </Link>

            {/* Get Proposal CTA */}
            <button 
              onClick={() => setIsInquiryOpen(true)}
              className="btn-shimmer bg-brand-red hover:bg-brand-red-dark text-white font-outfit font-bold text-xs sm:text-sm px-4 sm:px-5 py-2.5 rounded-full shadow-lg shadow-brand-red/25 transition-transform active:scale-95 flex items-center gap-2 cursor-pointer">
              <span>Enquire Now</span>
              <i className="fa-solid fa-arrow-right text-xs"></i>
            </button>
          </div>

        </div>
      </header>
    </>
  );
};
