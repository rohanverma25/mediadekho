import React from 'react';
import { Link, useLocation } from 'react-router-dom';
import { useCart } from '../context/CartContext';
import { useAuth } from '../context/AuthContext';

/**
 * Mobile-only bottom tab bar (hidden md and up, where the full header nav
 * already covers these actions). Fixed to the viewport, so Footer carries
 * matching extra bottom padding on mobile to keep its own content from
 * being covered by this bar.
 */
export const MobileBottomNav = () => {
  const location = useLocation();
  const { cart, setIsSearchOpen, setIsCartDrawerOpen } = useCart();
  const { isAuthenticated } = useAuth();

  const isActive = (path) => location.pathname === path;

  const itemClass = (active) =>
    `flex flex-col items-center justify-center gap-0.5 flex-1 h-full text-[10px] font-semibold transition ${
      active ? 'text-brand-red' : 'text-slate-500'
    }`;

  return (
    <nav className="md:hidden fixed bottom-0 inset-x-0 z-40 bg-white border-t border-slate-200 shadow-[0_-2px_10px_rgba(0,0,0,0.06)] flex items-stretch h-16 pb-[env(safe-area-inset-bottom)]">
      <Link to="/" className={itemClass(isActive('/'))}>
        <i className="fa-solid fa-house text-base"></i>
        <span>Home</span>
      </Link>

      <Link to="/category" className={itemClass(isActive('/category'))}>
        <i className="fa-solid fa-layer-group text-base"></i>
        <span>Browse</span>
      </Link>

      <button
        type="button"
        onClick={() => setIsSearchOpen(true)}
        className={itemClass(false)}>
        <i className="fa-solid fa-magnifying-glass text-base"></i>
        <span>Search</span>
      </button>

      <button
        type="button"
        onClick={() => setIsCartDrawerOpen(true)}
        className={`relative ${itemClass(false)}`}>
        <span className="relative">
          <i className="fa-solid fa-cart-shopping text-base"></i>
          {cart.length > 0 && (
            <span className="absolute -top-1.5 -right-2 bg-brand-red text-white text-[9px] font-black w-4 h-4 rounded-full flex items-center justify-center">
              {cart.length}
            </span>
          )}
        </span>
        <span>Cart</span>
      </button>

      <Link to={isAuthenticated ? '/dashboard' : '/login'} className={itemClass(isActive('/dashboard') || isActive('/login'))}>
        <i className={`fa-solid ${isAuthenticated ? 'fa-circle-user' : 'fa-user'} text-base`}></i>
        <span>Account</span>
      </Link>
    </nav>
  );
};
