import React, { useRef } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import { useMediaCategories } from '../hooks/useMediaCategories';

/**
 * Rendered once, globally, in App.jsx (right below the main Header) so it
 * appears on every page — not just Category — which is why it takes no
 * props and derives its own "active" pill purely from the current route:
 * on /category/:slug it reflects the slug segment; everywhere else it just
 * highlights "All Verticals". Clicking a pill always navigates to /category
 * (or /category/:slug). Parsed from the raw pathname (not useParams()) since
 * this component sits outside the <Route> tree that owns the :slug param.
 */
export const CategorySlider = () => {
  const trackRef = useRef(null);
  const navigate = useNavigate();
  const location = useLocation();
  const { categories, status } = useMediaCategories();

  const activeCategorySlug = location.pathname.startsWith('/category')
    ? location.pathname.slice('/category'.length).replace(/^\//, '') || 'all'
    : 'all';

  const scroll = (direction) => {
    if (trackRef.current) {
      const scrollAmount = direction === 'left' ? -250 : 250;
      trackRef.current.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    }
  };

  const handleCategoryClick = (slug) => {
    navigate(slug === 'all' ? '/category' : `/category/${slug}`);
  };

  return (
    <div className="bg-white border-b border-slate-200 shadow-sm sticky top-[62px] z-30 py-2.5 px-4 backdrop-blur-md">
      <div className="max-w-7xl mx-auto flex items-center gap-2 relative">

        <button
          onClick={() => scroll('left')}
          className="w-7 h-7 rounded-full bg-slate-100 border border-slate-200 text-slate-600 hover:bg-brand-red hover:text-white flex items-center justify-center flex-shrink-0 transition shadow-sm">
          <i className="fa-solid fa-chevron-left text-[10px]"></i>
        </button>

        <div
          ref={trackRef}
          className="flex items-center gap-2 overflow-x-auto scrollbar-none py-0.5 scroll-smooth flex-1 whitespace-nowrap">
          {status === 'loading' &&
            Array.from({ length: 6 }).map((_, i) => (
              <span
                key={i}
                className="h-7 w-24 rounded-full bg-slate-100 animate-pulse flex-shrink-0"
              />
            ))}

          {status !== 'loading' && (
            <button
              onClick={() => handleCategoryClick('all')}
              className={`cat-menu-item font-semibold text-xs px-3.5 py-1.5 rounded-full border transition cursor-pointer ${
                activeCategorySlug === 'all' ? 'active' : ''
              }`}>
              All Verticals
            </button>
          )}

          {status !== 'loading' &&
            categories.map((c) => (
              <button
                key={c.id}
                onClick={() => handleCategoryClick(c.slug)}
                className={`cat-menu-item font-semibold text-xs px-3.5 py-1.5 rounded-full border transition cursor-pointer ${
                  activeCategorySlug === c.slug ? 'active' : ''
                }`}>
                {c.name}
              </button>
            ))}
        </div>

        <button 
          onClick={() => scroll('right')}
          className="w-7 h-7 rounded-full bg-slate-100 border border-slate-200 text-slate-600 hover:bg-brand-red hover:text-white flex items-center justify-center flex-shrink-0 transition shadow-sm">
          <i className="fa-solid fa-chevron-right text-[10px]"></i>
        </button>

      </div>
    </div>
  );
};
