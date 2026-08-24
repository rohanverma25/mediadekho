import React, { useMemo, useState } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { MEDIA_DATABASE } from '../data/mediaData';
import { useCart } from '../context/CartContext';
import { useClientLogos } from '../hooks/useClientLogos';
import { useMediaInventory } from '../hooks/useMediaInventory';
import { normalizeInventoryItem } from '../services/mediaInventoryService';
import { useMediaCategories } from '../hooks/useMediaCategories';
import { useFaqs } from '../hooks/useFaqs';
import { Skeleton } from '../components/Skeleton';
import { ViewPricingButton } from '../components/ViewPricingButton';
import { useAuth } from '../context/AuthContext';

const DEFAULT_CATEGORY_ICON = 'bi-grid';

const FALLBACK_CLIENT_NAMES = ['SWIGGY', 'ZOMATO', 'AIR INDIA', 'NYKAA', 'TATA MOTORS', 'PAYTM', 'LENSKART', 'AMAZON'];

export const HomePage = () => {
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
  const { categories: mediaCategories, status: mediaCategoriesStatus } = useMediaCategories();
  const { faqs, status: faqsStatus } = useFaqs();
  const [activeFaq, setActiveFaq] = useState(0);
  const { items: liveItems, status: inventoryStatus } = useMediaInventory({
    per_page: 8,
    category_id: activeCategoryId !== 'all' ? activeCategoryId : undefined,
  });

  const handleHeroSearch = (e) => {
    e.preventDefault();
    if (heroQuery.trim()) {
      navigate(`/category?query=${encodeURIComponent(heroQuery)}`);
    } else {
      navigate('/category');
    }
  };

  const normalizedLiveItems = useMemo(() => liveItems.map(normalizeInventoryItem), [liveItems]);
  const usingLiveMedia = inventoryStatus === 'success' && normalizedLiveItems.length > 0;
  const mediaSource = usingLiveMedia ? normalizedLiveItems : MEDIA_DATABASE;
  const filteredMedia = mediaSource;

  return (
    <div>

      {/* HERO SECTION */}
      <section className="relative pt-12 pb-20 px-4 sm:px-6 overflow-hidden hero-bg-solid border-b border-slate-200">
        <div className="max-w-4xl mx-auto text-center space-y-6 flex flex-col items-center">
          
          <div className="inline-flex items-center gap-2 bg-red-50 border border-red-200 text-brand-red px-3.5 py-1.5 rounded-full text-xs font-bold tracking-wide uppercase shadow-sm">
            <span className="w-2 h-2 rounded-full bg-brand-red animate-ping"></span>
            <span>India's Largest Media Aggregator Platform</span>
          </div>

          <h1 className="font-outfit font-black text-4xl sm:text-5xl lg:text-6xl text-slate-900 tracking-tight leading-[1.1]">
            Make Your Ad Campaigns Online Across <span className="gradient-text-brand">300,000+ Media Options</span>
          </h1>

          <p className="text-slate-600 text-base sm:text-lg max-w-2xl mx-auto font-normal leading-relaxed">
            Plan, compare, and book advertising spots instantly across <strong>Offline</strong>, <strong>Digital</strong>, <strong>Sports</strong> & <strong>Gifting</strong> with 100% verified direct owner rates.
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
              <Link to="/category/magazine-advertising" className="hover:text-brand-red bg-slate-100 px-2 py-0.5 rounded border border-slate-200">Vogue & Forbes</Link>
              <Link to="/category/airport-advertising" className="hover:text-brand-red bg-slate-100 px-2 py-0.5 rounded border border-slate-200">Mumbai T2 Airport</Link>
              <Link to="/category/transit-metro" className="hover:text-brand-red bg-slate-100 px-2 py-0.5 rounded border border-slate-200">Delhi Metro Trains</Link>
              <Link to="/category/app-takeover" className="hover:text-brand-red bg-slate-100 px-2 py-0.5 rounded border border-slate-200">Swiggy & Zomato</Link>
            </div>
          </form>

          {/* Trust Badges */}
          <div className="pt-4 flex flex-wrap justify-center items-center gap-6 text-xs text-slate-600 font-semibold">
            <div className="flex items-center gap-2">
              <i className="fa-solid fa-circle-check text-brand-red text-base"></i>
              <span>Zero Agent Commission</span>
            </div>
            <div className="flex items-center gap-2">
              <i className="fa-solid fa-bolt text-brand-red text-base"></i>
              <span>15-Min Proposal Response</span>
            </div>
            <div className="flex items-center gap-2">
              <i className="fa-solid fa-shield-halved text-brand-red text-base"></i>
              <span>100% Geotagged Audit</span>
            </div>
          </div>

        </div>
      </section>

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
                        className="h-8 w-auto max-w-[140px] object-contain grayscale opacity-60 hover:opacity-100 hover:grayscale-0 transition"
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

            {inventoryStatus !== 'loading' && filteredMedia.map((item) => {
              const isCarted = cart.some((c) => c.id === item.id);
              const listingHref = item.slug ? `/listing/${item.slug}` : '/listing';
              return (
                <div key={item.id} className="glass-card rounded-2xl overflow-hidden group flex flex-col justify-between h-full bg-white border border-slate-200 shadow-sm hover:shadow-xl transition-all duration-300">
                  <div>
                    <Link to={listingHref} className="relative h-48 overflow-hidden block">
                      <img src={item.image} alt={item.title} className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
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
                          ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg shadow-emerald-600/20'
                          : 'bg-brand-red hover:bg-brand-red-dark text-white shadow-lg shadow-brand-red/25 hover:scale-105'
                      }`}>
                      <i className={`fa-solid ${isCarted ? 'fa-check' : 'fa-plus'}`}></i>
                      {isCarted ? 'Added' : 'Add to Plan'}
                    </button>
                  </div>
                </div>
              );
            })}
          </div>

        </div>
      </section>

      {/* WHAT MEDIA ARE YOU LOOKING FOR — CATEGORY GRID */}
      {(mediaCategoriesStatus === 'loading' || (mediaCategoriesStatus === 'success' && mediaCategories.length > 0)) && (
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
                    const total = mediaCategories.reduce((sum, c) => sum + (c.inventory_count || 0), 0);
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
                : mediaCategories.map((category) => (
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
