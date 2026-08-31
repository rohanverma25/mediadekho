import React, { useMemo, useRef, useState } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { useCart } from '../context/CartContext';
import { useClientLogos } from '../hooks/useClientLogos';
import { useIndustries } from '../hooks/useIndustries';
import { useMediaInventory } from '../hooks/useMediaInventory';
import { normalizeInventoryItem } from '../services/mediaInventoryService';
import { useMediaCategories } from '../hooks/useMediaCategories';
import { useFaqs } from '../hooks/useFaqs';
import { useBlogs } from '../hooks/useBlogs';
import { useNews } from '../hooks/useNews';
import { useAwards } from '../hooks/useAwards';
import { useVideos } from '../hooks/useVideos';
import { Skeleton } from '../components/Skeleton';
import { YouTubeFacade } from '../components/YouTubeFacade';
import { UploadedVideoFacade } from '../components/UploadedVideoFacade';
import { ViewPricingButton } from '../components/ViewPricingButton';
import { useAuth } from '../context/AuthContext';
import { useDocumentMeta } from '../hooks/useDocumentMeta';
import { usePageMeta } from '../hooks/usePageMeta';

const DEFAULT_CATEGORY_ICON = 'bi-grid';

const FALLBACK_CLIENT_NAMES = ['SWIGGY', 'ZOMATO', 'AIR INDIA', 'NYKAA', 'TATA MOTORS', 'PAYTM', 'LENSKART', 'AMAZON'];

const FALLBACK_BLOG_IMAGE =
  'https://images.unsplash.com/photo-1499750310107-5fef28a66643?auto=format&fit=crop&w=800&q=80';

const FALLBACK_AWARD_IMAGE =
  'https://images.unsplash.com/photo-1567427017947-545c5f8d16ad?auto=format&fit=crop&w=800&q=80';

/**
 * The one card design used everywhere a media inventory item is shown as a
 * card on the homepage — the main "Explore Advertising Options" grid and
 * the "Top Media Deals" slider both render this, so the two never drift
 * apart into visually inconsistent card styles.
 */
const MediaListingCard = ({ item, isAuthenticated, isCarted, onAddToCart }) => {
  const listingHref = item.slug ? `/listing/${item.slug}` : '/listing';

  return (
    <div className="glass-card rounded-2xl overflow-hidden group flex flex-col justify-between h-full bg-white border border-slate-200 shadow-sm hover:shadow-xl transition-all duration-300">
      <div>
        <Link to={listingHref} className="relative h-48 overflow-hidden block bg-slate-100">
          <img src={item.image} alt={item.title} className="w-full h-full object-contain group-hover:scale-110 transition-transform duration-500" />
          <div className="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent"></div>
          <span className="absolute top-3 left-3 bg-white/90 backdrop-blur-md text-slate-900 text-xs px-2.5 py-1 rounded-full font-bold shadow">
            {item.category}
          </span>
          <div className="absolute bottom-3 left-3 right-3 flex justify-between items-center text-xs text-white font-medium">
            <span className="flex items-center gap-1"><i className="fa-solid fa-location-dot text-red-400"></i> {item.location}</span>
            <span className="flex items-center gap-1 bg-brand-red text-white px-2 py-0.5 rounded font-bold">
              <i className="fa-solid fa-star text-white"></i> {item.rating}
            </span>
          </div>
        </Link>

        <div className="p-5 space-y-3">
          {item.subCategory && (
            <span className="text-[10px] font-bold text-brand-red uppercase tracking-wider block">
              {item.subCategory}
            </span>
          )}
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
          onClick={onAddToCart}
          className={`px-4 py-2.5 rounded-xl font-bold text-xs flex items-center gap-1.5 transition-all duration-300 cursor-pointer ${
            isCarted
              ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg shadow-emerald-600/20'
              : 'bg-brand-red hover:bg-brand-red-dark text-white shadow-lg shadow-brand-red/25 hover:scale-105'
          }`}>
          <i className={`fa-solid ${isCarted ? 'fa-check' : 'fa-plus'}`}></i>
          {isCarted ? 'Added' : 'Add to Plan'}
        </button>
      </div>
    </div>
  );
};

export const HomePage = () => {
  const { meta: homeMeta } = usePageMeta('home');
  useDocumentMeta({ title: homeMeta?.title, description: homeMeta?.description, image: homeMeta?.og_image_url });

  const navigate = useNavigate();
  const location = useLocation();
  const { isAuthenticated } = useAuth();
  const { toggleCartItem, cart } = useCart();

  const handleAddToCart = (id, item) => {
    if (!isAuthenticated) {
      navigate('/login', { state: { from: `${location.pathname}${location.search}` } });
      return;
    }
    toggleCartItem(id, item);
  };
  const [heroQuery, setHeroQuery] = useState('');
  const [activeCategoryId, setActiveCategoryId] = useState('all');
  const { logos: clientLogos, status: clientLogosStatus } = useClientLogos();
  const { industries, status: industriesStatus } = useIndustries();
  const { videos, status: videosStatus } = useVideos();
  const videosTrackRef = useRef(null);
  const scrollVideos = (direction) => {
    if (videosTrackRef.current) {
      const scrollAmount = direction === 'left' ? -340 : 340;
      videosTrackRef.current.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    }
  };
  const industriesTrackRef = useRef(null);
  const scrollIndustries = (direction) => {
    if (industriesTrackRef.current) {
      const scrollAmount = direction === 'left' ? -320 : 320;
      industriesTrackRef.current.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    }
  };
  const { categories: mediaCategories, status: mediaCategoriesStatus } = useMediaCategories();
  const homepageCategories = useMemo(() => mediaCategories.filter((c) => c.show_on_homepage), [mediaCategories]);
  const popularCategories = useMemo(() => mediaCategories.filter((c) => c.show_on_popular), [mediaCategories]);
  const { faqs, status: faqsStatus } = useFaqs();
  const { blogs: latestBlogs, status: latestBlogsStatus } = useBlogs({ per_page: 3 });
  const { news, status: newsStatus } = useNews();
  const latestNews = news.slice(0, 3);
  const { awards, status: awardsStatus } = useAwards();
  const featuredAwards = useMemo(() => awards.filter((a) => a.show_on_homepage), [awards]);
  const awardsTrackRef = useRef(null);
  const scrollAwards = (direction) => {
    if (awardsTrackRef.current) {
      const scrollAmount = direction === 'left' ? -320 : 320;
      awardsTrackRef.current.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    }
  };
  const [activeFaq, setActiveFaq] = useState(0);
  const { items: liveItems, status: inventoryStatus } = useMediaInventory({
    per_page: 8,
    category_id: activeCategoryId !== 'all' ? activeCategoryId : undefined,
  });
  const { items: dealItems, status: dealsStatus } = useMediaInventory({ per_page: 10, show_on_deals: 1 });
  const dealsTrackRef = useRef(null);
  const scrollDeals = (direction) => {
    if (dealsTrackRef.current) {
      const scrollAmount = direction === 'left' ? -300 : 300;
      dealsTrackRef.current.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    }
  };

  const handleHeroSearch = (e) => {
    e.preventDefault();
    if (heroQuery.trim()) {
      navigate(`/category?query=${encodeURIComponent(heroQuery)}`);
    } else {
      navigate('/category');
    }
  };

  const normalizedLiveItems = useMemo(() => liveItems.map(normalizeInventoryItem), [liveItems]);
  const filteredMedia = normalizedLiveItems;
  const normalizedDeals = useMemo(() => dealItems.map(normalizeInventoryItem), [dealItems]);

  return (
    <div>

      {/* HERO SECTION */}
      <section className="relative pt-12 pb-20 px-4 sm:px-6 overflow-hidden hero-bg-solid border-b border-slate-200">
        <div className="max-w-4xl mx-auto text-center space-y-6 flex flex-col items-center">
          
          <div className="inline-flex items-center gap-2 bg-red-50 border border-red-200 text-brand-red px-3.5 py-1.5 rounded-full text-xs font-bold tracking-wide uppercase shadow-sm">
            <span className="w-2 h-2 rounded-full bg-brand-red animate-ping"></span>
            <span>India's Trusted Media Aggregator Platform</span>
          </div>

          <h1 className="font-outfit font-black text-4xl sm:text-5xl lg:text-6xl text-slate-900 tracking-tight leading-[1.1]">
            Choose MediaDekho for your 360° Brand Visibility Partner Across <span className="gradient-text-brand">15,000+ Media, PR & Advertising Options</span>
          </h1>

          <p className="text-slate-600 text-base sm:text-lg max-w-2xl mx-auto font-normal leading-relaxed">
            Plan, compare, and book advertising spots instantly across <strong>Print Online</strong>, <strong>Digital</strong>, <strong>Entertainment</strong> & <strong>Sports</strong>.
          </p>

          {/* Main Interactive Search Box Widget */}
          <form onSubmit={handleHeroSearch} className="bg-white p-3 rounded-2xl border border-slate-200 shadow-xl max-w-xl w-full mx-auto space-y-2 text-left">
            <div className="flex flex-col sm:flex-row gap-2">
              <div className="flex-1 relative">
                <input 
                  type="text" 
                  placeholder="Search Airports, Magazines, Swiggy, Metro..." 
                  value={heroQuery}
                  onChange={(e) => setHeroQuery(e.target.value)}
                  className="w-full glass-input rounded-xl px-4 py-3 text-sm font-semibold pl-10" />
                <i className="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 text-slate-400 text-sm"></i>
              </div>
              <button 
                type="submit" 
                className="bg-brand-red hover:bg-brand-red-dark text-white font-outfit font-bold text-sm px-6 py-3 rounded-xl shadow-lg shadow-brand-red/25 transition active:scale-95 flex items-center justify-center gap-2 cursor-pointer">
                <span>Explore Rates</span>
                <i className="fa-solid fa-arrow-right text-xs"></i>
              </button>
            </div>
            
            <div className="flex flex-wrap items-center justify-center gap-2 pt-1 text-[11px] text-slate-500 font-medium">
              <span className="font-bold text-slate-700">Popular:</span>
              {popularCategories.length > 0
                ? popularCategories.map((category) => (
                    <Link
                      key={category.id}
                      to={`/category/${category.slug}`}
                      className="hover:text-brand-red bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                      {category.name}
                    </Link>
                  ))
                : (
                  <>
                    <Link to="/category/magazine-advertising" className="hover:text-brand-red bg-slate-100 px-2 py-0.5 rounded border border-slate-200">Vogue & Forbes</Link>
                    <Link to="/category/airport-advertising" className="hover:text-brand-red bg-slate-100 px-2 py-0.5 rounded border border-slate-200">Mumbai T2 Airport</Link>
                    <Link to="/category/transit-metro" className="hover:text-brand-red bg-slate-100 px-2 py-0.5 rounded border border-slate-200">Delhi Metro Trains</Link>
                    <Link to="/category/app-takeover" className="hover:text-brand-red bg-slate-100 px-2 py-0.5 rounded border border-slate-200">Swiggy & Zomato</Link>
                  </>
                )}
            </div>
          </form>

        </div>
      </section>

      {/* TOP DEALS SLIDER */}
      {(dealsStatus === 'loading' || (dealsStatus === 'success' && normalizedDeals.length > 0)) && (
        <section className="py-14 px-4 sm:px-6 bg-white border-b border-slate-200">
          <div className="max-w-7xl mx-auto space-y-6">
            <div className="flex items-end justify-between gap-4">
              <div>
                <span className="text-brand-red font-outfit font-bold text-xs uppercase tracking-widest block mb-1.5">
                  Limited Time
                </span>
                <h2 className="font-outfit font-extrabold text-2xl sm:text-3xl text-slate-900 tracking-tight">
                  Top Media Deals
                </h2>
              </div>
              <Link
                to="/category"
                className="flex-shrink-0 text-brand-red font-outfit font-bold text-sm hover:underline flex items-center gap-1.5">
                View All
                <i className="fa-solid fa-arrow-right text-[11px]"></i>
              </Link>
            </div>

            <div className="relative flex items-center gap-3">
              <button
                onClick={() => scrollDeals('left')}
                aria-label="Scroll deals left"
                className="hidden sm:flex w-9 h-9 rounded-full bg-white border border-slate-200 text-slate-600 hover:bg-brand-red hover:text-white items-center justify-center flex-shrink-0 transition shadow-sm">
                <i className="fa-solid fa-chevron-left text-xs"></i>
              </button>

              <div
                ref={dealsTrackRef}
                className="flex items-stretch gap-4 overflow-x-auto scrollbar-none scroll-smooth snap-x snap-mandatory py-1 flex-1">
                {dealsStatus === 'loading'
                  ? Array.from({ length: 4 }).map((_, i) => (
                      <Skeleton key={i} className="h-[26rem] w-[85%] sm:w-[calc((100%-1rem)/2)] lg:w-[calc((100%-3rem)/4)] rounded-2xl flex-shrink-0 snap-start" />
                    ))
                  : normalizedDeals.map((item) => (
                      <div key={item.id} className="flex-shrink-0 snap-start w-[85%] sm:w-[calc((100%-1rem)/2)] lg:w-[calc((100%-3rem)/4)]">
                        <MediaListingCard
                          item={item}
                          isAuthenticated={isAuthenticated}
                          isCarted={cart.some((c) => c.id === item.id)}
                          onAddToCart={() => handleAddToCart(item.id, item)}
                        />
                      </div>
                    ))}
              </div>

              <button
                onClick={() => scrollDeals('right')}
                aria-label="Scroll deals right"
                className="hidden sm:flex w-9 h-9 rounded-full bg-white border border-slate-200 text-slate-600 hover:bg-brand-red hover:text-white items-center justify-center flex-shrink-0 transition shadow-sm">
                <i className="fa-solid fa-chevron-right text-xs"></i>
              </button>
            </div>
          </div>
        </section>
      )}

      {/* CLIENT LOGOS MARQUEE */}
      <section className="py-6 bg-white border-b border-slate-200 overflow-hidden">
        <div className="max-w-7xl mx-auto px-4 mb-3 text-center">
          <span className="text-[11px] font-extrabold uppercase tracking-widest text-slate-400">Trusted By 5,000+ Global Brands & Fast-Growing Startups</span>
        </div>
        
        <div className="relative w-full overflow-hidden">
          <div className="animate-marquee flex items-center gap-12 whitespace-nowrap">
            {clientLogosStatus === 'loading'
              ? Array.from({ length: 8 }).map((_, i) => (
                  <Skeleton key={i} className="h-8 w-28 flex-shrink-0 rounded" />
                ))
              : clientLogosStatus === 'success' && clientLogos.length > 0
                ? [...clientLogos, ...clientLogos].map((logo, i) => (
                    <a
                      key={`${logo.id}-${i}`}
                      href={logo.website_url || undefined}
                      target={logo.website_url ? '_blank' : undefined}
                      rel={logo.website_url ? 'noopener noreferrer' : undefined}
                      className="inline-flex items-center flex-shrink-0"
                      aria-label={logo.name}>
                      <img
                        src={logo.logo_url}
                        alt={logo.name}
                        title={logo.name}
                        className="h-8 w-auto max-w-[140px] object-contain"
                      />
                    </a>
                  ))
                : FALLBACK_CLIENT_NAMES.concat(FALLBACK_CLIENT_NAMES).map((name, i) => (
                    <span
                      key={`${name}-${i}`}
                      className="text-slate-400 font-outfit font-black text-xl tracking-wider uppercase">
                      {name}
                    </span>
                  ))}
          </div>
        </div>
      </section>

      {/* 4 CORE VERTICALS TAB EXPLORER */}
      <section className="py-16 px-4 sm:px-6">
        <div className="max-w-7xl mx-auto space-y-10">
          
          <div className="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
            <div>
              <span className="text-brand-red font-outfit font-bold text-xs uppercase tracking-widest block mb-2">Omnichannel Coverage</span>
              <h2 className="font-outfit font-extrabold text-3xl sm:text-4xl text-slate-900 tracking-tight">
                Explore Advertising Options Across 4 Verticals
              </h2>
            </div>

            <div className="flex flex-wrap gap-2 bg-slate-200/80 p-1.5 rounded-2xl border border-slate-300 max-w-full">
              <button
                onClick={() => setActiveCategoryId('all')}
                className={`font-semibold text-xs px-4 py-2 rounded-xl transition cursor-pointer ${
                  activeCategoryId === 'all' ? 'bg-brand-red text-white' : 'bg-white text-slate-700'
                }`}>
                All
              </button>

              {mediaCategoriesStatus === 'loading' &&
                Array.from({ length: 4 }).map((_, i) => (
                  <Skeleton key={i} className="h-[30px] w-24 rounded-xl" />
                ))}

              {mediaCategoriesStatus === 'success' &&
                mediaCategories.map((category) => (
                  <button
                    key={category.id}
                    onClick={() => setActiveCategoryId(category.id)}
                    className={`font-semibold text-xs px-4 py-2 rounded-xl transition cursor-pointer ${
                      activeCategoryId === category.id ? 'bg-brand-red text-white' : 'bg-white text-slate-700'
                    }`}>
                    {category.name}
                  </button>
                ))}
            </div>
          </div>

          {/* Media Cards Grid */}
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            {inventoryStatus === 'loading' &&
              Array.from({ length: 8 }).map((_, i) => (
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

            {inventoryStatus === 'success' && filteredMedia.length === 0 && (
              <div className="col-span-full text-center py-16 text-slate-500">
                <i className="fa-solid fa-photo-film text-3xl mb-3 text-slate-300"></i>
                <p className="text-sm font-medium">No listings available in this category yet. Check back soon.</p>
              </div>
            )}

            {inventoryStatus !== 'loading' && filteredMedia.map((item) => (
              <MediaListingCard
                key={item.id}
                item={item}
                isAuthenticated={isAuthenticated}
                isCarted={cart.some((c) => c.id === item.id)}
                onAddToCart={() => handleAddToCart(item.id, item)}
              />
            ))}
          </div>

        </div>
      </section>

      {/* WHAT MEDIA ARE YOU LOOKING FOR — CATEGORY GRID */}
      {(mediaCategoriesStatus === 'loading' || (mediaCategoriesStatus === 'success' && homepageCategories.length > 0)) && (
        <section className="py-16 px-4 sm:px-6 bg-slate-50 border-y border-slate-200">
          <div className="max-w-7xl mx-auto space-y-10">
            <div className="text-center max-w-2xl mx-auto space-y-3">
              <h2 className="font-outfit font-extrabold text-3xl sm:text-4xl text-slate-900 tracking-tight">
                What Media are you looking for?
              </h2>
              {mediaCategoriesStatus === 'loading' ? (
                <Skeleton className="h-4 w-80 mx-auto" />
              ) : (
                <p className="text-slate-600 text-sm leading-relaxed">
                  {(() => {
                    const total = homepageCategories.reduce((sum, c) => sum + (c.inventory_count || 0), 0);
                    return total > 0
                      ? `Browse ${total.toLocaleString('en-IN')}+ verified media options across every category below.`
                      : 'Click on any category below to explore verified media options and direct owner rates.';
                  })()}
                </p>
              )}
            </div>

            <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
              {mediaCategoriesStatus === 'loading'
                ? Array.from({ length: 6 }).map((_, i) => (
                    <div key={i} className="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col items-center gap-2">
                      <Skeleton className="w-14 h-14 rounded-2xl" />
                      <Skeleton className="h-4 w-16" />
                      <Skeleton className="h-3 w-12" />
                    </div>
                  ))
                : homepageCategories.map((category) => (
                    <Link
                      key={category.id}
                      to={`/category/${category.slug}`}
                      className="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-lg hover:border-brand-red/30 transition-all duration-300 flex flex-col items-center text-center gap-2">
                      <div className="w-14 h-14 rounded-2xl bg-red-50 text-brand-red flex items-center justify-center text-2xl group-hover:bg-brand-red group-hover:text-white transition-colors">
                        <i className={category.icon || DEFAULT_CATEGORY_ICON}></i>
                      </div>
                      <span className="font-outfit font-bold text-sm text-slate-900">{category.name}</span>
                      <span className="text-[11px] text-slate-500 font-medium">
                        {(category.inventory_count || 0).toLocaleString('en-IN')} {category.inventory_count === 1 ? 'Option' : 'Options'}
                      </span>
                    </Link>
                  ))}
            </div>
          </div>
        </section>
      )}

      {/* INDUSTRIES WE SERVE */}
      {(industriesStatus === 'loading' || (industriesStatus === 'success' && industries.length > 0)) && (
        <section className="py-16 px-4 sm:px-6 bg-white border-b border-slate-200">
          <div className="max-w-7xl mx-auto space-y-10">
            <div className="text-center max-w-2xl mx-auto space-y-3">
              <span className="text-brand-red font-outfit font-bold text-xs uppercase tracking-widest block mb-2">Industries We Serve</span>
              <h2 className="font-outfit font-extrabold text-3xl sm:text-4xl text-slate-900 tracking-tight">
                Trusted Across Every Industry
              </h2>
            </div>

            <div className="relative flex items-center gap-3">
              <button
                onClick={() => scrollIndustries('left')}
                aria-label="Scroll industries left"
                className="hidden sm:flex w-9 h-9 rounded-full bg-white border border-slate-200 text-slate-600 hover:bg-brand-red hover:text-white items-center justify-center flex-shrink-0 transition shadow-sm">
                <i className="fa-solid fa-chevron-left text-xs"></i>
              </button>

              <div
                ref={industriesTrackRef}
                className="flex items-stretch gap-5 overflow-x-auto scrollbar-none scroll-smooth snap-x snap-mandatory py-1 flex-1">
                {industriesStatus === 'loading'
                  ? Array.from({ length: 6 }).map((_, i) => (
                      <Skeleton key={i} className="h-44 w-56 rounded-2xl flex-shrink-0 snap-start" />
                    ))
                  : industries.map((industry) => (
                      <Link
                        key={industry.id}
                        to={`/clients?industry=${industry.id}`}
                        className="relative h-44 w-56 flex-shrink-0 snap-start rounded-2xl overflow-hidden group shadow-sm border border-slate-200 block">
                        <img
                          src={industry.image_url}
                          alt={industry.title}
                          className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                        />
                        <div className="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/10 to-transparent"></div>
                        <span className="absolute bottom-3 left-3 right-3 text-white font-outfit font-bold text-sm drop-shadow">
                          {industry.title}
                        </span>
                      </Link>
                    ))}
              </div>

              <button
                onClick={() => scrollIndustries('right')}
                aria-label="Scroll industries right"
                className="hidden sm:flex w-9 h-9 rounded-full bg-white border border-slate-200 text-slate-600 hover:bg-brand-red hover:text-white items-center justify-center flex-shrink-0 transition shadow-sm">
                <i className="fa-solid fa-chevron-right text-xs"></i>
              </button>
            </div>
          </div>
        </section>
      )}

      {/* WHY MEDIA DEKHO SECTION */}
      <section className="py-16 px-4 sm:px-6 bg-white border-b border-slate-200">
        <div className="max-w-7xl mx-auto space-y-12">
          
          <div className="text-center max-w-3xl mx-auto">
            <span className="text-brand-red font-outfit font-bold text-xs uppercase tracking-widest block mb-2">The Media Dekho Advantage</span>
            <h2 className="font-outfit font-extrabold text-3xl sm:text-4xl text-slate-900">
              Why 5,000+ Advertisers Choose Media Dekho
            </h2>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div className="glass-card p-6 rounded-3xl border border-slate-200 space-y-4">
              <div className="w-12 h-12 rounded-2xl bg-red-50 text-brand-red flex items-center justify-center text-xl font-bold">
                <i className="fa-solid fa-tags"></i>
              </div>
              <h3 className="font-outfit font-bold text-xl text-slate-900">Transparent Direct Owner Rates</h3>
              <p className="text-slate-600 text-xs leading-relaxed">
                Eliminate middleman margins. Access official card rates directly from 300,000+ media owners with zero commission markups.
              </p>
            </div>

            <div className="glass-card p-6 rounded-3xl border border-slate-200 space-y-4">
              <div className="w-12 h-12 rounded-2xl bg-red-50 text-brand-red flex items-center justify-center text-xl font-bold">
                <i className="fa-solid fa-stopwatch"></i>
              </div>
              <h3 className="font-outfit font-bold text-xl text-slate-900">15-Minute Turnaround</h3>
              <p className="text-slate-600 text-xs leading-relaxed">
                No more waiting days for media plans. Select options into your basket and receive an official customized media plan within 15 minutes.
              </p>
            </div>

            <div className="glass-card p-6 rounded-3xl border border-slate-200 space-y-4">
              <div className="w-12 h-12 rounded-2xl bg-red-50 text-brand-red flex items-center justify-center text-xl font-bold">
                <i className="fa-solid fa-camera font-bold"></i>
              </div>
              <h3 className="font-outfit font-bold text-xl text-slate-900">100% Geotagged Proof Audit</h3>
              <p className="text-slate-600 text-xs leading-relaxed">
                Every billboard, transit wrap, or magazine insertion is verified with high-resolution geotagged photos and physical proof of execution.
              </p>
            </div>
          </div>

        </div>
      </section>

      {/* AGENCY PARTNER (B2B) CTA */}
      <section className="py-6 px-4 sm:px-6">
        <div className="max-w-7xl mx-auto">
          <div className="relative overflow-hidden rounded-3xl bg-gradient-to-br from-red-950 via-brand-red-dark to-brand-red px-6 sm:px-10 py-6 sm:py-7">
            {/* Dot-grid texture — purely decorative, sits behind everything */}
            <div
              className="absolute inset-0 opacity-[0.08]"
              style={{ backgroundImage: 'radial-gradient(circle, #ffffff 1px, transparent 1px)', backgroundSize: '24px 24px' }}>
            </div>

            {/* Decorative glow blobs */}
            <div className="pointer-events-none absolute -top-16 -right-10 w-56 h-56 rounded-full bg-white/10 blur-3xl"></div>
            <div className="pointer-events-none absolute -bottom-16 -left-8 w-48 h-48 rounded-full bg-black/30 blur-3xl"></div>

            <div className="relative flex flex-col lg:flex-row items-center justify-between gap-6">
              <div className="max-w-xl text-center lg:text-left">
                <span className="inline-flex items-center gap-2 bg-white/10 border border-white/20 backdrop-blur-sm text-white text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-2.5">
                  <i className="fa-solid fa-handshake text-[10px]"></i>
                  For Agencies &amp; Media Partners
                </span>

                <h2 className="font-outfit font-black text-xl sm:text-2xl text-white tracking-tight mb-1.5 leading-tight">
                  Become an Agency Partner
                </h2>

                <p className="text-white/85 text-xs sm:text-sm leading-relaxed mb-3 max-w-lg mx-auto lg:mx-0">
                  Get exclusive access to Advantage 360 — plan, compare &amp; sell media smarter with real-time rates and tools.
                </p>

                <div className="flex flex-wrap items-center justify-center lg:justify-start gap-x-4 gap-y-1.5 mb-4 text-white/90 text-[11px] font-semibold">
                  <span className="flex items-center gap-1.5"><i className="fa-solid fa-circle-check text-[10px]"></i> Real-Time Rates</span>
                  <span className="flex items-center gap-1.5"><i className="fa-solid fa-circle-check text-[10px]"></i> Instant Comparisons</span>
                  <span className="flex items-center gap-1.5"><i className="fa-solid fa-circle-check text-[10px]"></i> Zero Commission</span>
                </div>

                <Link
                  to="/signup?type=b2b"
                  className="group inline-flex items-center gap-2 bg-white hover:bg-slate-100 text-brand-red font-outfit font-extrabold text-xs px-5 py-2.5 rounded-xl shadow-2xl transition active:scale-95">
                  Join Now
                  <i className="fa-solid fa-arrow-right text-[10px] transition-transform group-hover:translate-x-1"></i>
                </Link>
              </div>

              {/* Partnership photo, framed with the same decorative ring */}
              <div className="relative hidden lg:flex items-center justify-center w-48 h-48 flex-shrink-0">
                <div className="absolute inset-0 rounded-full border-2 border-dashed border-white/25 animate-[spin_20s_linear_infinite]"></div>
                <div className="absolute inset-4 rounded-full overflow-hidden border-4 border-white/20 shadow-2xl">
                  <img
                    src="https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=600&q=80"
                    alt="Agency partnership"
                    loading="lazy"
                    className="w-full h-full object-cover"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* AWARDS & ACHIEVEMENTS */}
      {(awardsStatus === 'loading' || (awardsStatus === 'success' && featuredAwards.length > 0)) && (
        <section className="py-16 px-4 sm:px-6 bg-slate-50 border-b border-slate-200">
          <div className="max-w-7xl mx-auto space-y-10">
            <div className="text-center">
              <span className="text-brand-red font-outfit font-bold text-xs uppercase tracking-widest block mb-2">Recognition</span>
              <h2 className="font-outfit font-extrabold text-3xl sm:text-4xl text-slate-900 tracking-tight">
                Awards & Achievements
              </h2>
            </div>

            <div className="relative flex items-center gap-3">
              <button
                onClick={() => scrollAwards('left')}
                aria-label="Scroll awards left"
                className="hidden sm:flex w-9 h-9 rounded-full bg-white border border-slate-200 text-slate-600 hover:bg-brand-red hover:text-white items-center justify-center flex-shrink-0 transition shadow-sm">
                <i className="fa-solid fa-chevron-left text-xs"></i>
              </button>

              <div
                ref={awardsTrackRef}
                className="flex items-stretch gap-5 overflow-x-auto scrollbar-none scroll-smooth snap-x snap-mandatory py-1 flex-1">
                {awardsStatus === 'loading'
                  ? Array.from({ length: 4 }).map((_, i) => (
                      <Skeleton key={i} className="h-64 w-[92%] sm:w-[calc((100%-1.25rem)/2)] lg:w-[calc((100%-2.5rem)/3)] rounded-2xl flex-shrink-0 snap-start" />
                    ))
                  : featuredAwards.map((award) => (
                      <div
                        key={award.id}
                        className="h-64 w-[92%] sm:w-[calc((100%-1.25rem)/2)] lg:w-[calc((100%-2.5rem)/3)] flex-shrink-0 snap-start flex items-center justify-center">
                        <img
                          src={award.image_url || FALLBACK_AWARD_IMAGE}
                          alt={award.title}
                          className="max-w-full max-h-full object-contain"
                        />
                      </div>
                    ))}
              </div>

              <button
                onClick={() => scrollAwards('right')}
                aria-label="Scroll awards right"
                className="hidden sm:flex w-9 h-9 rounded-full bg-white border border-slate-200 text-slate-600 hover:bg-brand-red hover:text-white items-center justify-center flex-shrink-0 transition shadow-sm">
                <i className="fa-solid fa-chevron-right text-xs"></i>
              </button>
            </div>
          </div>
        </section>
      )}

      {/* AWARD NOMINATION CTA */}
      <section className="py-14 px-4 sm:px-6 bg-brand-red">
        <div className="max-w-5xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6 text-center md:text-left">
          <div className="flex items-center gap-5 flex-col md:flex-row">
            <div className="w-16 h-16 rounded-2xl bg-white/15 flex items-center justify-center text-white text-2xl flex-shrink-0">
              <i className="fa-solid fa-trophy"></i>
            </div>
            <div>
              <h2 className="font-outfit font-extrabold text-2xl sm:text-3xl text-white tracking-tight">
                Think Your Campaign Deserves Recognition?
              </h2>
              <p className="text-white/90 text-sm mt-1.5 max-w-xl">
                Nominate your brand or campaign for our upcoming awards and get recognized among 5,000+ industry leaders.
              </p>
            </div>
          </div>

          <Link
            to="/awards"
            className="flex-shrink-0 inline-flex items-center gap-2 bg-white hover:bg-slate-100 text-brand-red font-outfit font-bold text-sm px-6 py-3.5 rounded-xl shadow-lg transition active:scale-95">
            <i className="fa-solid fa-trophy text-xs"></i>
            <span>Nominate Now</span>
          </Link>
        </div>
      </section>

      {/* LATEST BLOGS */}
      {(latestBlogsStatus === 'loading' || (latestBlogsStatus === 'success' && latestBlogs.length > 0)) && (
        <section className="py-16 px-4 sm:px-6 bg-slate-50 border-b border-slate-200">
          <div className="max-w-7xl mx-auto space-y-10">
            <div className="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
              <div>
                <span className="text-brand-red font-outfit font-bold text-xs uppercase tracking-widest block mb-2">From The Blog</span>
                <h2 className="font-outfit font-extrabold text-3xl sm:text-4xl text-slate-900 tracking-tight">
                  Latest Insights & Industry News
                </h2>
              </div>
              <Link to="/blogs" className="text-xs font-bold text-brand-red hover:underline inline-flex items-center gap-1.5 flex-shrink-0">
                View All Posts <i className="fa-solid fa-arrow-right text-[10px]"></i>
              </Link>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {latestBlogsStatus === 'loading'
                ? Array.from({ length: 3 }).map((_, i) => (
                    <div key={i} className="rounded-2xl overflow-hidden bg-white border border-slate-200 shadow-sm">
                      <Skeleton className="w-full h-48 rounded-none" />
                      <div className="p-5 space-y-3">
                        <Skeleton className="h-3 w-24" />
                        <Skeleton className="h-5 w-full" />
                        <Skeleton className="h-3 w-full" />
                        <Skeleton className="h-3 w-2/3" />
                      </div>
                    </div>
                  ))
                : latestBlogs.map((blog) => (
                    <Link
                      key={blog.id}
                      to={`/blog?slug=${blog.slug}`}
                      className="group rounded-2xl overflow-hidden bg-white border border-slate-200 shadow-sm hover:shadow-lg transition-shadow flex flex-col">
                      <div className="h-48 overflow-hidden">
                        <img
                          src={blog.featured_image_url || FALLBACK_BLOG_IMAGE}
                          alt={blog.title}
                          className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        />
                      </div>
                      <div className="p-5 space-y-2.5 flex-1 flex flex-col">
                        {blog.published_at && (
                          <span className="text-[10px] uppercase tracking-wider text-slate-400 font-bold">
                            {new Date(blog.published_at).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' })}
                            {blog.author_name && ` · ${blog.author_name}`}
                          </span>
                        )}
                        <h3 className="font-outfit font-bold text-lg text-slate-900 group-hover:text-brand-red transition line-clamp-2 leading-snug">
                          {blog.title}
                        </h3>
                        {blog.excerpt && (
                          <p className="text-xs text-slate-500 leading-relaxed line-clamp-3">{blog.excerpt}</p>
                        )}
                        <span className="text-xs font-bold text-brand-red mt-auto pt-2 flex items-center gap-1.5">
                          Read More <i className="fa-solid fa-arrow-right text-[10px]"></i>
                        </span>
                      </div>
                    </Link>
                  ))}
            </div>
          </div>
        </section>
      )}

      {/* MEDIA IN THE NEWS */}
      {(newsStatus === 'loading' || (newsStatus === 'success' && latestNews.length > 0)) && (
        <section className="py-16 px-4 sm:px-6 bg-white border-b border-slate-200">
          <div className="max-w-7xl mx-auto space-y-10">
            <div className="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
              <div>
                <span className="text-brand-red font-outfit font-bold text-xs uppercase tracking-widest block mb-2">In The Press</span>
                <h2 className="font-outfit font-extrabold text-3xl sm:text-4xl text-slate-900 tracking-tight">
                  Media Dekho In The News
                </h2>
              </div>
              <Link to="/news" className="text-xs font-bold text-brand-red hover:underline inline-flex items-center gap-1.5 flex-shrink-0">
                View All Coverage <i className="fa-solid fa-arrow-right text-[10px]"></i>
              </Link>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {newsStatus === 'loading'
                ? Array.from({ length: 3 }).map((_, i) => (
                    <div key={i} className="rounded-2xl overflow-hidden bg-white border border-slate-200 shadow-sm">
                      <Skeleton className="w-full h-48 rounded-none" />
                      <div className="p-5 space-y-3">
                        <Skeleton className="h-4 w-full" />
                        <Skeleton className="h-4 w-2/3" />
                      </div>
                    </div>
                  ))
                : latestNews.map((item) => (
                    <a
                      key={item.id}
                      href={item.link}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="group rounded-2xl overflow-hidden bg-white border border-slate-200 shadow-sm hover:shadow-lg transition-shadow flex flex-col">
                      <div className="h-48 overflow-hidden bg-slate-100">
                        <img
                          src={item.image_url}
                          alt={item.title}
                          className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        />
                      </div>
                      <div className="p-5 flex-1 flex flex-col">
                        <h3 className="font-outfit font-bold text-base text-slate-900 group-hover:text-brand-red transition line-clamp-2 leading-snug">
                          {item.title}
                        </h3>
                        <span className="text-xs font-bold text-brand-red mt-auto pt-3 flex items-center gap-1.5">
                          Read Full Story <i className="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                        </span>
                      </div>
                    </a>
                  ))}
            </div>
          </div>
        </section>
      )}

      {/* VIDEOS */}
      {(videosStatus === 'loading' || (videosStatus === 'success' && videos.length > 0)) && (
        <section className="py-16 px-4 sm:px-6 bg-slate-50 border-b border-slate-200">
          <div className="max-w-7xl mx-auto space-y-10">
            <div className="text-center">
              <span className="text-brand-red font-outfit font-bold text-xs uppercase tracking-widest block mb-2">Watch & Learn</span>
              <h2 className="font-outfit font-extrabold text-3xl sm:text-4xl text-slate-900 tracking-tight">
                Videos
              </h2>
            </div>

            <div className="relative flex items-center gap-3">
              <button
                onClick={() => scrollVideos('left')}
                aria-label="Scroll videos left"
                className="hidden sm:flex w-9 h-9 rounded-full bg-white border border-slate-200 text-slate-600 hover:bg-brand-red hover:text-white items-center justify-center flex-shrink-0 transition shadow-sm">
                <i className="fa-solid fa-chevron-left text-xs"></i>
              </button>

              <div
                ref={videosTrackRef}
                className="flex items-stretch gap-5 overflow-x-auto scrollbar-none scroll-smooth snap-x snap-mandatory py-1 flex-1">
                {videosStatus === 'loading'
                  ? Array.from({ length: 3 }).map((_, i) => (
                      <Skeleton key={i} className="h-52 w-[80%] sm:w-[calc((100%-1.25rem)/2)] lg:w-[calc((100%-2.5rem)/3)] rounded-2xl flex-shrink-0 snap-start" />
                    ))
                  : videos.map((video) => (
                      <div
                        key={video.id}
                        className="w-[80%] sm:w-[calc((100%-1.25rem)/2)] lg:w-[calc((100%-2.5rem)/3)] flex-shrink-0 snap-start rounded-2xl overflow-hidden bg-white border border-slate-200 shadow-sm hover:shadow-lg transition-shadow">
                        {video.source_type === 'upload' ? (
                          <UploadedVideoFacade
                            videoUrl={video.video_url}
                            title={video.title}
                            thumbnailUrl={video.thumbnail_url}
                            className="w-full aspect-video"
                          />
                        ) : (
                          <YouTubeFacade
                            videoId={video.video_id}
                            title={video.title}
                            thumbnailUrl={video.thumbnail_url}
                            className="w-full aspect-video"
                          />
                        )}
                        <div className="p-4">
                          <h3 className="font-outfit font-bold text-sm text-slate-900 leading-snug line-clamp-2">{video.title}</h3>
                        </div>
                      </div>
                    ))}
              </div>

              <button
                onClick={() => scrollVideos('right')}
                aria-label="Scroll videos right"
                className="hidden sm:flex w-9 h-9 rounded-full bg-white border border-slate-200 text-slate-600 hover:bg-brand-red hover:text-white items-center justify-center flex-shrink-0 transition shadow-sm">
                <i className="fa-solid fa-chevron-right text-xs"></i>
              </button>
            </div>
          </div>
        </section>
      )}

      {/* FREQUENTLY ASKED QUESTIONS (FAQ) */}
      {(faqsStatus === 'loading' || (faqsStatus === 'success' && faqs.length > 0)) && (
        <section className="py-16 px-4 sm:px-6 bg-slate-100 border-t border-slate-200">
          <div className="max-w-4xl mx-auto space-y-10">

            <div className="text-center">
              <span className="text-brand-red font-outfit font-bold text-xs uppercase tracking-widest block mb-2">Got Questions?</span>
              <h2 className="font-outfit font-extrabold text-3xl sm:text-4xl text-slate-900 mb-3">
                Frequently Asked Questions
              </h2>
              <Link to="/faq" className="text-xs font-bold text-brand-red hover:underline inline-flex items-center gap-1.5">
                View all FAQs <i className="fa-solid fa-arrow-right text-[10px]"></i>
              </Link>
            </div>

            <div className="space-y-4">
              {faqsStatus === 'loading' &&
                Array.from({ length: 4 }).map((_, i) => (
                  <Skeleton key={i} className="h-16 w-full rounded-2xl" />
                ))}

              {faqsStatus === 'success' &&
                faqs.map((faq, index) => (
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
