import React, { useState, useEffect } from 'react';
import { useCart } from '../context/CartContext';
import { MEDIA_DATABASE } from '../data/mediaData';

export const SearchModal = () => {
  const { isSearchOpen, setIsSearchOpen, toggleCartItem } = useCart();
  const [query, setQuery] = useState('');

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

  if (!isSearchOpen) return null;

  const matches = MEDIA_DATABASE.filter(
    (item) =>
      item.title.toLowerCase().includes(query.toLowerCase()) ||
      item.location.toLowerCase().includes(query.toLowerCase()) ||
      item.category.toLowerCase().includes(query.toLowerCase())
  );

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
          {matches.length === 0 ? (
            <div className="py-8 text-center text-slate-400">
              <i className="fa-solid fa-magnifying-glass text-3xl mb-2 text-brand-red"></i>
              <p className="font-medium text-slate-600">No media matching "{query}"</p>
            </div>
          ) : (
            matches.map((item) => (
              <div 
                key={item.id}
                onClick={() => {
                  toggleCartItem(item.id);
                  setIsSearchOpen(false);
                }}
                className="p-3 hover:bg-slate-100 rounded-xl transition flex items-center justify-between cursor-pointer border border-slate-100">
                <div className="flex items-center gap-3">
                  <img src={item.image} alt={item.title} className="w-12 h-12 rounded-lg object-cover" />
                  <div>
                    <h4 className="font-bold text-slate-900 text-sm line-clamp-1">{item.title}</h4>
                    <p className="text-xs text-slate-500 font-medium">{item.category} • {item.location}</p>
                  </div>
                </div>
                <div className="text-right">
                  <span className="text-sm font-extrabold text-brand-red font-outfit">₹{item.price.toLocaleString('en-IN')}</span>
                  <span className="text-[10px] text-slate-400 block">{item.priceUnit}</span>
                </div>
              </div>
            ))
          )}
        </div>

      </div>
    </div>
  );
};
