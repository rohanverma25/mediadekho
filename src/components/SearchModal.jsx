import React, { useEffect, useState } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { useCart } from '../context/CartContext';
import { useAuth } from '../context/AuthContext';
import { useMediaInventory } from '../hooks/useMediaInventory';
import { normalizeInventoryItem } from '../services/mediaInventoryService';
import { Skeleton } from './Skeleton';
import { ViewPricingButton } from './ViewPricingButton';

const MIN_QUERY_LENGTH = 2;
const DEBOUNCE_MS = 350;

export const SearchModal = () => {
  const { isSearchOpen, setIsSearchOpen, toggleCartItem } = useCart();
  const { isAuthenticated } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const [query, setQuery] = useState('');
  const [debouncedQuery, setDebouncedQuery] = useState('');

  useEffect(() => {
    const handleKeyDown = (e) => {
      if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        setIsSearchOpen(true);
      }
      if (e.key === 'Escape' && isSearchOpen) {
        setIsSearchOpen(false);
      }
    };
    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, [isSearchOpen, setIsSearchOpen]);

  // Debounced so every keystroke doesn't fire its own API request — only
  // the query the user actually pauses on gets searched.
  useEffect(() => {
    const timer = setTimeout(() => setDebouncedQuery(query.trim()), DEBOUNCE_MS);
    return () => clearTimeout(timer);
  }, [query]);

  // Reset so re-opening the modal doesn't briefly flash stale results from
  // a previous search before the debounce catches up.
  useEffect(() => {
    if (!isSearchOpen) {
      setQuery('');
      setDebouncedQuery('');
    }
  }, [isSearchOpen]);

  const searchReady = debouncedQuery.length >= MIN_QUERY_LENGTH;
  const { items, status } = useMediaInventory(searchReady ? { search: debouncedQuery, per_page: 10 } : {});
  const matches = searchReady ? items.map(normalizeInventoryItem) : [];

  if (!isSearchOpen) return null;

  const handleSelect = (item) => {
    if (!isAuthenticated) {
      setIsSearchOpen(false);
      navigate('/login', { state: { from: `${location.pathname}${location.search}` } });
      return;
    }
    toggleCartItem(item.id, item);
    setIsSearchOpen(false);
  };

  return (
    <div className="fixed inset-0 z-50 modal-backdrop flex items-center justify-center p-4">
      <div className="bg-white w-full max-w-2xl rounded-3xl border border-slate-200 shadow-2xl overflow-hidden flex flex-col max-h-[85vh] animate-in fade-in zoom-in duration-200">

        {/* Search Bar */}
        <div className="p-4 border-b border-slate-200 flex items-center gap-3 bg-slate-50">
          <i className="fa-solid fa-magnifying-glass text-brand-red text-lg"></i>
          <input
            type="text"
            autoFocus
            placeholder="Search by media type, city, location (e.g. Vogue, Airport, Swiggy)..."
            value={query}
            onChange={(e) => setQuery(e.target.value)}
            className="w-full bg-transparent text-slate-900 placeholder-slate-400 outline-none text-sm font-semibold" />
          <button
            onClick={() => setIsSearchOpen(false)}
            className="text-slate-400 hover:text-slate-900 text-lg px-2 cursor-pointer">
            <i className="fa-solid fa-xmark"></i>
          </button>
        </div>

        {/* Search Results List */}
        <div className="p-4 overflow-y-auto space-y-2 flex-1">
          {!searchReady && (
            <div className="py-8 text-center text-slate-400">
              <i className="fa-solid fa-magnifying-glass text-3xl mb-2 text-slate-300"></i>
              <p className="font-medium text-slate-500">
                {query.length > 0 ? 'Keep typing to search...' : 'Search across all live media listings'}
              </p>
            </div>
          )}

          {searchReady && status === 'loading' && (
            <div className="space-y-2">
              {Array.from({ length: 4 }).map((_, i) => (
                <div key={i} className="p-3 rounded-xl border border-slate-100 flex items-center gap-3">
                  <Skeleton className="w-12 h-12 rounded-lg flex-shrink-0" />
                  <div className="flex-1 space-y-1.5">
                    <Skeleton className="h-4 w-3/4" />
                    <Skeleton className="h-3 w-1/2" />
                  </div>
                </div>
              ))}
            </div>
          )}

          {searchReady && status === 'success' && matches.length === 0 && (
            <div className="py-8 text-center text-slate-400">
              <i className="fa-solid fa-magnifying-glass text-3xl mb-2 text-brand-red"></i>
              <p className="font-medium text-slate-600">No media matching "{debouncedQuery}"</p>
            </div>
          )}

          {searchReady && status === 'success' && matches.map((item) => (
            <div
              key={item.id}
              onClick={() => handleSelect(item)}
              className="p-3 hover:bg-slate-100 rounded-xl transition flex items-center justify-between cursor-pointer border border-slate-100">
              <div className="flex items-center gap-3">
                <img src={item.image} alt={item.title} className="w-12 h-12 rounded-lg object-cover" />
                <div>
                  <h4 className="font-bold text-slate-900 text-sm line-clamp-1">{item.title}</h4>
                  <p className="text-xs text-slate-500 font-medium">{item.category}</p>
                </div>
              </div>
              <div className="text-right" onClick={(e) => item.priceLocked && e.stopPropagation()}>
                {item.priceLocked ? (
                  <ViewPricingButton className="inline-flex items-center gap-1 text-[10px] font-bold text-brand-red bg-red-50 hover:bg-brand-red hover:text-white border border-red-100 transition rounded-lg px-2 py-1 cursor-pointer" />
                ) : (
                  <>
                    <span className="text-sm font-extrabold text-brand-red font-outfit">₹{item.price.toLocaleString('en-IN')}</span>
                    <span className="text-[10px] text-slate-400 block">{item.priceUnit}</span>
                  </>
                )}
              </div>
            </div>
          ))}
        </div>

      </div>
    </div>
  );
};
