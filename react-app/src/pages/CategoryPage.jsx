import React, { useEffect, useMemo, useState } from 'react';
import { Link, useLocation, useNavigate, useParams, useSearchParams } from 'react-router-dom';
import { useCart } from '../context/CartContext';
import { useMediaCategories } from '../hooks/useMediaCategories';
import { useMediaInventory } from '../hooks/useMediaInventory';
import { fetchMediaCategoryBySlug } from '../services/mediaCategoryService';
import { normalizeInventoryItem } from '../services/mediaInventoryService';
import { Skeleton } from '../components/Skeleton';
import { ViewPricingButton } from '../components/ViewPricingButton';
import { useAuth } from '../context/AuthContext';
import { useDocumentMeta } from '../hooks/useDocumentMeta';

// Accepts any common YouTube URL shape (watch?v=, youtu.be/, shorts/,
// already-an-embed link) and normalizes it to an embeddable iframe src.
const getYoutubeEmbedUrl = (url) => {
  if (!url) return null;
  const match = url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([a-zA-Z0-9_-]{11})/);
  return match ? `https://www.youtube.com/embed/${match[1]}` : null;
};

// The category description is rich admin-authored HTML — strip tags down
// to plain text for the meta description, which can't contain markup.
const stripHtml = (html) => (html ? html.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim() : '');

export const CategoryPage = () => {
  const { slug: slugParam } = useParams();
  const [searchParams] = useSearchParams();
  const catParam = slugParam || 'all';
  const queryParam = searchParams.get('query') || '';

  const navigate = useNavigate();
  const location = useLocation();
  const { cart, toggleCartItem } = useCart();
  const { isAuthenticated } = useAuth();
  const { categories: mediaCategories, status: mediaCategoriesStatus } = useMediaCategories();

  const handleAddToCart = (id, item) => {
    if (!isAuthenticated) {
      navigate('/login', { state: { from: `${location.pathname}${location.search}` } });
      return;
    }
    toggleCartItem(id, item);
  };

  const [activeCategory, setActiveCategory] = useState(catParam);
  const [searchTitle, setSearchTitle] = useState(queryParam);
  const [activeSubcategoryId, setActiveSubcategoryId] = useState(null);
  const [activeFrequencyId, setActiveFrequencyId] = useState(null);
  const [sortBy, setSortBy] = useState('popular');
  const [viewMode, setViewMode] = useState('grid');
  const [activeFaq, setActiveFaq] = useState(0);

  // Debounce the title search before it hits the live API — no need to
  // refetch on every keystroke.
  const [debouncedSearch, setDebouncedSearch] = useState(searchTitle);
  useEffect(() => {
    const timer = setTimeout(() => setDebouncedSearch(searchTitle), 300);
    return () => clearTimeout(timer);
  }, [searchTitle]);

  // The category pill nav now lives globally in App.jsx and drives filtering
  // purely via the `/category/:slug` route param, so this page needs to
  // react to that param changing (not just read it once on mount).
  useEffect(() => {
    setActiveCategory(slugParam || 'all');
  }, [slugParam]);

  // Media Inventory is categorized via the (hierarchical) Media Category
  // tree — resolve the active pill (a slug) to a real Media Category so it
  // can drive a genuine `category_id` query against the API.
  const matchedMediaCategory = mediaCategories.find((c) => c.slug === activeCategory);
  const categoryYoutubeEmbedUrl = getYoutubeEmbedUrl(matchedMediaCategory?.youtube_video_link);
  const hasCategoryMedia = Boolean(matchedMediaCategory?.image_url || categoryYoutubeEmbedUrl);
  const hasCategoryContent = Boolean(matchedMediaCategory?.description || hasCategoryMedia);

  useDocumentMeta(
    matchedMediaCategory
      ? {
          title: matchedMediaCategory.meta_title || `${matchedMediaCategory.name} Advertising Rates & Media Options`,
          description: matchedMediaCategory.meta_description || stripHtml(matchedMediaCategory.description).slice(0, 160) || undefined,
          image: matchedMediaCategory.meta_image_url || matchedMediaCategory.image_url,
        }
      : { title: 'Browse Media Categories' },
  );

  // FAQs are scoped per page, never shared with the homepage/FAQ page's
  // general list — a category only ever shows its own linked FAQs (fetched
  // fresh from the category detail endpoint, which is the only place they're
  // embedded), and shows nothing at all if it has none configured, rather
  // than falling back to the unrelated general FAQ set.
  const [categoryFaqs, setCategoryFaqs] = useState([]);
  const [categoryFaqsStatus, setCategoryFaqsStatus] = useState('loading');

  useEffect(() => {
    if (activeCategory === 'all') {
      setCategoryFaqs([]);
      setCategoryFaqsStatus('success');
      return;
    }

    let cancelled = false;
    setCategoryFaqsStatus('loading');

    fetchMediaCategoryBySlug(activeCategory)
      .then((category) => {
        if (cancelled) return;
        setCategoryFaqs(category?.faqs ?? []);
        setCategoryFaqsStatus('success');
      })
      .catch(() => {
        if (cancelled) return;
        setCategoryFaqs([]);
        setCategoryFaqsStatus('error');
      });

    return () => {
      cancelled = true;
    };
  }, [activeCategory]);
  const availableSubcategories = matchedMediaCategory?.children ?? [];

  // A subcategory/frequency picked under one category is meaningless once
  // the category itself changes — drop it rather than silently filtering
  // the new category's results by a stale, unrelated id.
  useEffect(() => {
    setActiveSubcategoryId(null);
    setActiveFrequencyId(null);
  }, [activeCategory]);

  const { items: liveItems, status: inventoryStatus } = useMediaInventory({
    search: debouncedSearch || undefined,
    category_id: matchedMediaCategory?.id,
    subcategory_id: activeSubcategoryId || undefined,
    per_page: 60,
  });

  const sourceItems = useMemo(() => liveItems.map(normalizeInventoryItem), [liveItems]);

  // The Frequency filter's options are whatever frequencies actually show up
  // in the current listing. Filtered client-side (like price) rather than
  // via the API, so picking one frequency doesn't shrink the result set the
  // options themselves are computed from — otherwise the other options
  // would vanish the moment one got selected.
  const availableFrequencies = useMemo(() => {
    const seen = new Map();
    sourceItems.forEach((item) => {
      if (item.frequency && !seen.has(item.frequency.id)) {
        seen.set(item.frequency.id, item.frequency);
      }
    });
    return [...seen.values()];
  }, [sourceItems]);

  const resetFilters = () => {
    setSearchTitle('');
    setActiveSubcategoryId(null);
    setActiveFrequencyId(null);
  };

  // Filtering Logic — live results already came back category/subcategory/
  // search-filtered from the API; frequency is client-side only.
  let filtered = sourceItems.filter(
    (item) => !activeFrequencyId || item.frequency?.id === activeFrequencyId
  );

  // Sorting
  if (sortBy === 'price-low') {
    filtered.sort((a, b) => a.price - b.price);
  } else if (sortBy === 'price-high') {
    filtered.sort((a, b) => b.price - a.price);
  } else if (sortBy === 'rating') {
    filtered.sort((a, b) => b.rating - a.rating);
  }

  return (
    <div>
      {/* CATEGORY PAGE HEADER BANNER */}
      <section className="bg-gradient-to-b from-slate-100 to-slate-50 border-b border-slate-200 py-10 px-4 sm:px-6">
        <div className="max-w-7xl mx-auto">
          
          <nav className="flex items-center gap-2 text-xs text-slate-500 mb-4 font-medium">
            <Link to="/" className="hover:text-brand-red">Home</Link>
            <i className="fa-solid fa-chevron-right text-[9px]"></i>
            <span className="text-slate-900 font-bold">{activeCategory === 'all' ? 'All Categories' : matchedMediaCategory?.name || activeCategory}</span>
          </nav>

          <div className="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <div>
              {mediaCategoriesStatus === 'loading' && activeCategory !== 'all' ? (
                <Skeleton className="h-10 w-64" />
              ) : (
                <h1 className="font-outfit font-black text-3xl sm:text-4xl lg:text-5xl text-slate-900 tracking-tight">
                  {activeCategory === 'all' ? 'All Media Advertising Options' : matchedMediaCategory?.name || activeCategory}
                </h1>
              )}
            </div>

            <div className="flex items-center gap-4 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex-shrink-0">
              <div className="text-center px-3">
                {inventoryStatus === 'loading' ? (
                  <Skeleton className="h-6 w-8 mx-auto mb-1" />
                ) : (
                  <span className="font-outfit font-black text-xl text-brand-red block">{filtered.length}</span>
                )}
                <span className="text-[10px] text-slate-500 uppercase font-bold">Media Spots</span>
              </div>
            </div>
          </div>

        </div>
      </section>

      {/* CATEGORY PAGE MAIN CATALOG SECTION */}
      <section className="py-10 px-4 sm:px-6">
        <div className="max-w-7xl mx-auto">
          
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            {/* LEFT FILTER SIDEBAR (3 COLS) */}
            <aside className="lg:col-span-3 space-y-6">
              <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-5">
                
                <div className="flex justify-between items-center pb-4 border-b border-slate-200">
                  <h3 className="font-outfit font-bold text-base text-slate-900 flex items-center gap-2">
                    <i className="fa-solid fa-filter text-brand-red"></i>
                    <span>Filter Options</span>
                  </h3>
                  <button onClick={resetFilters} className="text-xs text-brand-red font-semibold hover:underline cursor-pointer">
                    Reset All
                  </button>
                </div>

                {/* Search Title */}
                <div>
                  <label className="block text-xs font-bold uppercase text-slate-500 mb-2">Search Title</label>
                  <div className="relative">
                    <input 
                      type="text" 
                      placeholder="Filter by title..." 
                      value={searchTitle}
                      onChange={(e) => setSearchTitle(e.target.value)}
                      className="w-full glass-input rounded-xl px-3 py-2 text-xs pl-8" />
                    <i className="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                  </div>
                </div>

                {/* Subcategory */}
                {mediaCategoriesStatus === 'loading' && activeCategory !== 'all' && (
                  <div>
                    <label className="block text-xs font-bold uppercase text-slate-500 mb-2">Subcategory</label>
                    <div className="flex flex-wrap gap-2">
                      {Array.from({ length: 3 }).map((_, i) => (
                        <Skeleton key={i} className="h-7 w-20 rounded-full" />
                      ))}
                    </div>
                  </div>
                )}
                {availableSubcategories.length > 0 && (
                  <div>
                    <label className="block text-xs font-bold uppercase text-slate-500 mb-2">Subcategory</label>
                    <div className="flex flex-wrap gap-2">
                      <button
                        onClick={() => setActiveSubcategoryId(null)}
                        className={`text-xs font-semibold px-3 py-1.5 rounded-full border transition cursor-pointer ${
                          activeSubcategoryId === null ? 'bg-brand-red text-white border-brand-red' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300'
                        }`}>
                        All
                      </button>
                      {availableSubcategories.map((sub) => (
                        <button
                          key={sub.id}
                          onClick={() => setActiveSubcategoryId(sub.id)}
                          className={`text-xs font-semibold px-3 py-1.5 rounded-full border transition cursor-pointer ${
                            activeSubcategoryId === sub.id ? 'bg-brand-red text-white border-brand-red' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300'
                          }`}>
                          {sub.name}
                        </button>
                      ))}
                    </div>
                  </div>
                )}

                {/* Frequency — options are whatever's actually in the current listing */}
                {inventoryStatus === 'loading' && (
                  <div>
                    <label className="block text-xs font-bold uppercase text-slate-500 mb-2">Frequency</label>
                    <div className="flex flex-wrap gap-2">
                      {Array.from({ length: 3 }).map((_, i) => (
                        <Skeleton key={i} className="h-7 w-16 rounded-full" />
                      ))}
                    </div>
                  </div>
                )}
                {inventoryStatus === 'success' && availableFrequencies.length > 0 && (
                  <div>
                    <label className="block text-xs font-bold uppercase text-slate-500 mb-2">Frequency</label>
                    <div className="flex flex-wrap gap-2">
                      <button
                        onClick={() => setActiveFrequencyId(null)}
                        className={`text-xs font-semibold px-3 py-1.5 rounded-full border transition cursor-pointer ${
                          activeFrequencyId === null ? 'bg-brand-red text-white border-brand-red' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300'
                        }`}>
                        All
                      </button>
                      {availableFrequencies.map((freq) => (
                        <button
                          key={freq.id}
                          onClick={() => setActiveFrequencyId(freq.id)}
                          className={`text-xs font-semibold px-3 py-1.5 rounded-full border transition cursor-pointer ${
                            activeFrequencyId === freq.id ? 'bg-brand-red text-white border-brand-red' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300'
                          }`}>
                          {freq.name}
                        </button>
                      ))}
                    </div>
                  </div>
                )}

              </div>
            </aside>

            {/* RIGHT MEDIA LISTING GRID (9 COLS) */}
            <main className="lg:col-span-9 space-y-6">
              
              <div className="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row justify-between items-center gap-4">
                
                <div className="text-xs text-slate-600 font-medium">
                  {inventoryStatus === 'loading' ? (
                    <Skeleton className="h-4 w-32 inline-block align-middle" />
                  ) : (
                    <>Showing <strong className="text-slate-900 font-extrabold">{filtered.length}</strong> Media Options</>
                  )}
                </div>

                <div className="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-end">
                  <div className="flex items-center gap-2 text-xs">
                    <span className="text-slate-500 font-semibold">Sort By:</span>
                    <select 
                      value={sortBy}
                      onChange={(e) => setSortBy(e.target.value)}
                      className="glass-input rounded-xl px-3 py-1.5 text-xs font-semibold text-slate-800 cursor-pointer">
                      <option value="popular">Popularity (Recommended)</option>
                      <option value="price-low">Price: Low to High</option>
                      <option value="price-high">Price: High to Low</option>
                      <option value="rating">Highest Rated</option>
                    </select>
                  </div>

                  <div className="flex items-center gap-1 bg-slate-100 p-1 rounded-lg border border-slate-200">
                    <button 
                      onClick={() => setViewMode('grid')}
                      className={`p-1.5 rounded text-xs transition cursor-pointer ${viewMode === 'grid' ? 'text-brand-red bg-white shadow-sm' : 'text-slate-500'}`}>
                      <i className="fa-solid fa-grip"></i>
                    </button>
                    <button 
                      onClick={() => setViewMode('list')}
                      className={`p-1.5 rounded text-xs transition cursor-pointer ${viewMode === 'list' ? 'text-brand-red bg-white shadow-sm' : 'text-slate-500'}`}>
                      <i className="fa-solid fa-list"></i>
                    </button>
                  </div>
                </div>

              </div>

              {/* Media Cards List */}
              {inventoryStatus === 'loading' ? (
                <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                  {Array.from({ length: 6 }).map((_, i) => (
                    <div key={i} className="rounded-2xl overflow-hidden bg-white border border-slate-200 shadow-sm">
                      <Skeleton className="h-48 w-full rounded-none" />
                      <div className="p-5 space-y-3">
                        <Skeleton className="h-5 w-3/4" />
                        <div className="flex gap-1.5">
                          <Skeleton className="h-5 w-20 rounded-full" />
                          <Skeleton className="h-5 w-24 rounded-full" />
                        </div>
                      </div>
                      <div className="px-5 pb-5 pt-3 border-t border-slate-100 flex justify-between items-center">
                        <Skeleton className="h-6 w-20" />
                        <Skeleton className="h-9 w-24 rounded-xl" />
                      </div>
                    </div>
                  ))}
                </div>
              ) : filtered.length === 0 ? (
                <div className="bg-white p-12 rounded-3xl border border-slate-200 shadow-sm text-center space-y-4">
                  <i className="fa-solid fa-magnifying-glass text-4xl text-brand-red"></i>
                  <h3 className="font-outfit font-bold text-xl text-slate-900">No media options match your active filters</h3>
                  <button onClick={resetFilters} className="bg-brand-red text-white font-bold text-xs px-5 py-2.5 rounded-xl cursor-pointer">
                    Reset All Filters
                  </button>
                </div>
              ) : viewMode === 'grid' ? (
                <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                  {filtered.map((item) => {
                    const isCarted = cart.some((c) => c.id === item.id);
                    const listingHref = item.slug ? `/listing/${item.slug}` : '/listing';
                    return (
                      <div key={item.id} className="glass-card rounded-2xl overflow-hidden group flex flex-col justify-between h-full bg-white border border-slate-200 shadow-sm hover:shadow-xl transition-all duration-300">
                        <div>
                          <Link to={listingHref} className="relative h-48 overflow-hidden block">
                            <img src={item.image} alt={item.title} className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
                            <span className="absolute top-3 left-3 bg-white/90 backdrop-blur-md text-slate-900 text-xs px-2.5 py-1 rounded-full font-bold shadow">
                              {item.category}
                            </span>
                          </Link>

                          <div className="p-5 space-y-3">
                            <Link to={listingHref} className="font-outfit font-bold text-lg text-slate-900 group-hover:text-brand-red transition line-clamp-2 leading-snug">
                              {item.title}
                            </Link>

                            <div className="flex flex-wrap gap-1.5 mb-4">
                              {item.specs.map((spec, i) => (
                                <span key={i} className="bg-slate-100 border border-slate-200 text-slate-600 text-[11px] px-2 py-0.5 rounded font-medium">
                                  <i className="fa-solid fa-circle-check text-brand-red text-[9px] mr-1"></i>{spec}
                                </span>
                              ))}
                            </div>
                          </div>
                        </div>

                        <div className="px-5 pb-5 pt-3 border-t border-slate-100 flex justify-between items-center mt-auto bg-slate-50/50">
                          <div>
                            {isAuthenticated ? (
                              <div className="text-xl font-extrabold text-slate-900 font-outfit">
                                ₹{item.price.toLocaleString('en-IN')}
                                {item.priceUnit && <span className="text-xs text-slate-500 font-normal font-inter"> / {item.priceUnit}</span>}
                              </div>
                            ) : (
                              <ViewPricingButton />
                            )}
                          </div>

                          <button
                            onClick={() => handleAddToCart(item.id, item)}
                            className={`px-4 py-2.5 rounded-xl font-bold text-xs flex items-center gap-1.5 transition-all duration-300 cursor-pointer ${
                              isCarted
                                ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg'
                                : 'bg-brand-red hover:bg-brand-red-dark text-white shadow-lg'
                            }`}>
                            <i className={`fa-solid ${isCarted ? 'fa-check' : 'fa-plus'}`}></i>
                            {isCarted ? 'Added' : 'Add to Plan'}
                          </button>
                        </div>
                      </div>
                    );
                  })}
                </div>
              ) : (
                <div className="space-y-4">
                  {filtered.map((item) => {
                    const isCarted = cart.some((c) => c.id === item.id);
                    const listingHref = item.slug ? `/listing/${item.slug}` : '/listing';
                    return (
                      <div key={item.id} className="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                        <img src={item.image} alt={item.title} className="w-full sm:w-36 h-28 rounded-xl object-cover" />
                        <div className="flex-1 min-w-0">
                          <span className="bg-red-50 text-brand-red border border-red-100 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">
                            {item.category}
                          </span>
                          <Link to={listingHref} className="font-outfit font-bold text-base text-slate-900 hover:text-brand-red transition block mt-1">
                            {item.title}
                          </Link>
                          <span className="text-xs text-slate-500 font-medium block">{item.location} • {item.impressions}</span>
                        </div>
                        <div className="text-right sm:border-l sm:border-slate-100 sm:pl-4">
                          {isAuthenticated ? (
                            <div className="text-lg font-black text-slate-900 font-outfit mb-2">₹{item.price.toLocaleString('en-IN')}</div>
                          ) : (
                            <div className="mb-2 flex justify-end">
                              <ViewPricingButton />
                            </div>
                          )}
                          <button
                            onClick={() => handleAddToCart(item.id, item)}
                            className={`px-4 py-2 rounded-xl text-xs font-bold cursor-pointer ${isCarted ? 'bg-emerald-600 text-white' : 'bg-brand-red text-white'}`}>
                            {isCarted ? 'Added' : 'Add to Plan'}
                          </button>
                        </div>
                      </div>
                    );
                  })}
                </div>
              )}

            </main>

          </div>

        </div>
      </section>

      {/* CATEGORY DETAILS (image + video side by side, description below) */}
      {mediaCategoriesStatus === 'loading' && activeCategory !== 'all' && (
        <section className="py-16 px-4 sm:px-6 bg-white border-t border-slate-200">
          <div className="max-w-6xl mx-auto space-y-10">
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <Skeleton className="h-72 w-full rounded-3xl" />
              <Skeleton className="h-72 w-full rounded-3xl" />
            </div>
            <div className="space-y-3">
              <Skeleton className="h-4 w-full" />
              <Skeleton className="h-4 w-full" />
              <Skeleton className="h-4 w-3/4" />
            </div>
          </div>
        </section>
      )}

      {mediaCategoriesStatus === 'success' && matchedMediaCategory && hasCategoryContent && (
        <section className="py-16 px-4 sm:px-6 bg-white border-t border-slate-200">
          <div className="max-w-6xl mx-auto space-y-10">
            {hasCategoryMedia && (
              <div className={`grid grid-cols-1 gap-6 items-stretch ${matchedMediaCategory.image_url && categoryYoutubeEmbedUrl ? 'sm:grid-cols-2' : ''}`}>
                {matchedMediaCategory.image_url && (
                  <div className="aspect-video rounded-3xl overflow-hidden border border-slate-200 shadow-sm">
                    <img
                      src={matchedMediaCategory.image_url}
                      alt={matchedMediaCategory.name}
                      className="w-full h-full object-cover"
                    />
                  </div>
                )}
                {categoryYoutubeEmbedUrl && (
                  <div className="aspect-video rounded-3xl overflow-hidden border border-slate-200 shadow-sm">
                    <iframe
                      title={`${matchedMediaCategory.name} video`}
                      src={categoryYoutubeEmbedUrl}
                      className="w-full h-full"
                      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                      allowFullScreen
                    />
                  </div>
                )}
              </div>
            )}
            <div>
              <span className="text-brand-red font-outfit font-bold text-xs uppercase tracking-widest block mb-2">About This Category</span>
              <h2 className="font-outfit font-extrabold text-2xl sm:text-3xl text-slate-900 mb-4">{matchedMediaCategory.name}</h2>
              {matchedMediaCategory.description && (
                <div className="blog-content text-sm" dangerouslySetInnerHTML={{ __html: matchedMediaCategory.description }} />
              )}
            </div>
          </div>
        </section>
      )}

      {/* FREQUENTLY ASKED QUESTIONS (FAQ) — this category's own, never the general set */}
      {activeCategory !== 'all' && (categoryFaqsStatus === 'loading' || (categoryFaqsStatus === 'success' && categoryFaqs.length > 0)) && (
        <section className="py-16 px-4 sm:px-6 bg-slate-100 border-t border-slate-200">
          <div className="max-w-4xl mx-auto space-y-10">

            <div className="text-center">
              <span className="text-brand-red font-outfit font-bold text-xs uppercase tracking-widest block mb-2">Got Questions?</span>
              <h2 className="font-outfit font-extrabold text-3xl sm:text-4xl text-slate-900 mb-3">
                Frequently Asked Questions
              </h2>
            </div>

            <div className="space-y-4">
              {categoryFaqsStatus === 'loading' &&
                Array.from({ length: 4 }).map((_, i) => (
                  <Skeleton key={i} className="h-16 w-full rounded-2xl" />
                ))}

              {categoryFaqsStatus === 'success' &&
                categoryFaqs.map((faq, index) => (
                  <div
                    key={faq.id}
                    className={`faq-item ${activeFaq === index ? 'active' : ''}`}>
                    <div
                      onClick={() => setActiveFaq(activeFaq === index ? null : index)}
                      className="faq-header">
                      <span>{faq.question}</span>
                      <i className="fa-solid fa-chevron-down text-xs"></i>
                    </div>
                    <div className="faq-content" dangerouslySetInnerHTML={{ __html: faq.answer }} />
                  </div>
                ))}
            </div>

          </div>
        </section>
      )}
    </div>
  );
};
