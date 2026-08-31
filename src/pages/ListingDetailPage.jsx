import React, { useEffect, useMemo, useState } from 'react';
import { Link, useLocation, useNavigate, useParams } from 'react-router-dom';
import { useCart } from '../context/CartContext';
import { fetchMediaInventoryBySlug, normalizeInventoryItem } from '../services/mediaInventoryService';
import { Skeleton } from '../components/Skeleton';
import { ViewPricingButton } from '../components/ViewPricingButton';
import { useAuth } from '../context/AuthContext';
import { useDocumentMeta } from '../hooks/useDocumentMeta';

export const ListingDetailPage = () => {
  const { toggleCartItem, cart, setIsInquiryOpen, setInquiryContext } = useCart();
  const { isAuthenticated } = useAuth();
  const { slug } = useParams();
  const navigate = useNavigate();
  const location = useLocation();

  const [liveItem, setLiveItem] = useState(null);
  const [liveStatus, setLiveStatus] = useState(slug ? 'loading' : 'empty');

  useEffect(() => {
    if (!slug) {
      setLiveItem(null);
      setLiveStatus('empty');
      return;
    }

    let cancelled = false;
    setLiveStatus('loading');

    fetchMediaInventoryBySlug(slug)
      .then((data) => {
        if (cancelled) return;
        setLiveItem(data);
        setLiveStatus('success');
      })
      .catch(() => {
        if (cancelled) return;
        setLiveItem(null);
        setLiveStatus('error');
      });

    return () => {
      cancelled = true;
    };
  }, [slug]);

  const hasLiveItem = liveStatus === 'success' && liveItem !== null;
  const normalizedLiveItem = hasLiveItem ? normalizeInventoryItem(liveItem) : null;
  const isCarted = hasLiveItem && cart.some((c) => c.id === liveItem.id);

  // The gallery (multiple uploaded images) and the single main "Image" field
  // are separate admin inputs — merge them into one deduped, browsable set
  // so every image the admin attached to this listing is viewable, not just
  // the gallery ones.
  const images = useMemo(() => {
    if (!hasLiveItem) return [];
    const primary = liveItem.cover_image_url || liveItem.image_url;
    const all = primary ? [primary, ...(liveItem.images ?? [])] : liveItem.images ?? [];
    return [...new Set(all)];
  }, [hasLiveItem, liveItem]);

  const heroImage = images[0] || null;

  const [activeImg, setActiveImg] = useState(heroImage);
  const [activeFaq, setActiveFaq] = useState(0);

  useEffect(() => {
    setActiveImg(heroImage);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [hasLiveItem, heroImage]);

  const faqs = hasLiveItem && liveItem.faqs?.length > 0 ? liveItem.faqs : [];
  const keyInsights = hasLiveItem && liveItem.key_insights?.length > 0 ? liveItem.key_insights : [];
  // Admin-flagged insights (e.g. Reach) surface right under the title next
  // to the frequency/language pills; everything else stays in the full
  // grid further down — never both, to avoid showing the same fact twice.
  const headingInsights = keyInsights.filter((insight) => insight.show_after_heading);
  const bodyInsights = keyInsights.filter((insight) => !insight.show_after_heading);

  useDocumentMeta(
    hasLiveItem
      ? {
          title: liveItem.meta_title || liveItem.title,
          description: liveItem.meta_description || liveItem.short_description || undefined,
          image: liveItem.meta_image_url || heroImage,
        }
      : { title: 'Media Listing' },
  );

  // A slug is present but the item hasn't resolved yet — show a skeleton
  // instead of any placeholder/demo content flashing on screen.
  if (slug && liveStatus === 'loading') {
    return (
      <div>
        <div className="bg-white border-b border-slate-200 py-3 px-4 sm:px-6">
          <div className="max-w-7xl mx-auto">
            <Skeleton className="h-4 w-64" />
          </div>
        </div>

        <section className="py-10 px-4 sm:px-6 bg-white border-b border-slate-200">
          <div className="max-w-7xl mx-auto">
            <div className="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
              <div className="lg:col-span-5 space-y-4">
                <Skeleton className="rounded-3xl aspect-[4/3] w-full" />
                <div className="grid grid-cols-4 gap-3">
                  {Array.from({ length: 4 }).map((_, i) => (
                    <Skeleton key={i} className="rounded-xl h-20 w-full" />
                  ))}
                </div>
              </div>

              <div className="lg:col-span-7 space-y-6">
                <div className="space-y-3">
                  <Skeleton className="h-3 w-48" />
                  <Skeleton className="h-10 w-full" />
                  <Skeleton className="h-10 w-2/3" />
                  <Skeleton className="h-4 w-full" />
                  <Skeleton className="h-4 w-5/6" />
                </div>

                <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-200">
                  {Array.from({ length: 4 }).map((_, i) => (
                    <div key={i} className="space-y-1.5">
                      <Skeleton className="h-3 w-16" />
                      <Skeleton className="h-4 w-20" />
                    </div>
                  ))}
                </div>

                <div className="bg-white p-6 rounded-3xl border border-slate-200 shadow-xl space-y-5">
                  <div>
                    <Skeleton className="h-3 w-32 mb-2" />
                    <Skeleton className="h-9 w-40" />
                  </div>
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                    <Skeleton className="h-12 w-full rounded-xl" />
                    <Skeleton className="h-12 w-full rounded-xl" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    );
  }

  // No slug, or the fetch failed / found nothing — nothing real to show.
  if (!hasLiveItem) {
    return (
      <div className="py-24 px-4 sm:px-6">
        <div className="max-w-lg mx-auto bg-white p-12 rounded-3xl border border-slate-200 shadow-sm text-center space-y-4">
          <i className="fa-solid fa-circle-exclamation text-4xl text-brand-red"></i>
          <h1 className="font-outfit font-bold text-xl text-slate-900">Listing not found</h1>
          <p className="text-sm text-slate-500">
            This media listing doesn't exist or is no longer available.
          </p>
          <Link
            to="/category"
            className="inline-block bg-brand-red text-white font-bold text-xs px-5 py-2.5 rounded-xl cursor-pointer">
            Browse Media Inventory
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div>
      {/* Breadcrumb Bar */}
      <div className="bg-white border-b border-slate-200 py-3 px-4 sm:px-6">
        <div className="max-w-7xl mx-auto flex items-center justify-between">
          <nav className="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <Link to="/" className="hover:text-brand-red">Home</Link>
            <i className="fa-solid fa-chevron-right text-[9px]"></i>
            <Link
              to={liveItem.category?.slug ? `/category/${liveItem.category.slug}` : '/category'}
              className="hover:text-brand-red">
              {liveItem.category?.name || 'Media Inventory'}
            </Link>
            <i className="fa-solid fa-chevron-right text-[9px]"></i>
            <span className="text-slate-900 font-bold">{liveItem.title}</span>
          </nav>
        </div>
      </div>

      {/* HERO OVERVIEW SECTION */}
      <section className="py-10 px-4 sm:px-6 bg-white border-b border-slate-200">
        <div className="max-w-7xl mx-auto">

          <div className="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">

            <div className="lg:col-span-5 space-y-4">
              <div className="relative bg-slate-100 rounded-3xl overflow-hidden border border-slate-200 shadow-xl aspect-[4/3] group">
                {activeImg ? (
                  <img src={activeImg} alt={liveItem.title} className="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500" />
                ) : (
                  <div className="w-full h-full flex items-center justify-center text-slate-300">
                    <i className="fa-solid fa-image text-5xl"></i>
                  </div>
                )}
                <span className="absolute top-4 left-4 bg-brand-red text-white text-xs uppercase font-extrabold px-3 py-1 rounded-full shadow-lg">
                  {liveItem.category?.name || 'Media Inventory'}
                </span>
              </div>

              {images.length > 1 && (
                <div className="grid grid-cols-4 gap-3">
                  {images.map((src, i) => (
                    <img
                      key={i}
                      src={src}
                      alt="Thumbnail"
                      onClick={() => setActiveImg(src)}
                      className={`rounded-xl border-2 cursor-pointer object-contain bg-slate-100 h-20 w-full transition ${
                        activeImg === src ? 'border-brand-red' : 'border-slate-200 hover:border-brand-red'
                      }`} />
                  ))}
                </div>
              )}
            </div>

            <div className="lg:col-span-7 space-y-6">
              <div>
                <div className="flex items-center gap-2 text-xs font-bold text-brand-red uppercase tracking-widest mb-2">
                  <i className="fa-solid fa-book-open"></i>
                  {liveItem.category?.name || 'Media Inventory'}
                  {liveItem.subcategory?.name && (
                    <>
                      <i className="fa-solid fa-chevron-right text-[9px]"></i>
                      {liveItem.subcategory.name}
                    </>
                  )}
                </div>
                <h1 className="font-outfit font-semibold text-2xl sm:text-3xl lg:text-4xl text-slate-900 tracking-tight leading-tight mb-3">
                  {liveItem.title}
                </h1>

                {(liveItem.frequency?.name || liveItem.language?.name || headingInsights.length > 0) && (
                  <div className="flex flex-wrap items-center gap-2 mb-3">
                    {liveItem.frequency?.name && (
                      <span className="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-full">
                        <i className="fa-solid fa-repeat text-brand-red text-[10px]"></i> {liveItem.frequency.name}
                      </span>
                    )}
                    {liveItem.language?.name && (
                      <span className="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-full">
                        <i className="fa-solid fa-language text-brand-red text-[10px]"></i> {liveItem.language.name}
                      </span>
                    )}
                    {headingInsights.map((insight, i) => (
                      <span
                        key={i}
                        className="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-full">
                        <i className="fa-solid fa-circle-check text-brand-red text-[10px]"></i> {insight.label}: {insight.value}
                      </span>
                    ))}
                  </div>
                )}

                {liveItem.short_description && (
                  <p className="text-slate-600 text-sm leading-relaxed">
                    {liveItem.short_description}
                  </p>
                )}
              </div>

              {bodyInsights.length > 0 && (
                <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-200">
                  {bodyInsights.slice(0, 4).map((insight, i) => (
                    <div key={i}>
                      <span className="text-[10px] text-slate-400 uppercase font-bold block">{insight.label}</span>
                      <span className="font-outfit font-extrabold text-base text-slate-900">{insight.value}</span>
                    </div>
                  ))}
                </div>
              )}

              <div className="bg-white p-6 rounded-3xl border border-slate-200 shadow-xl space-y-5">
                <div>
                  {!isAuthenticated ? (
                    <div className="pt-1">
                      <ViewPricingButton />
                    </div>
                  ) : liveItem.price?.available ? (
                    <div className="space-y-1.5">
                      <div className="text-3xl sm:text-4xl font-black text-slate-900 font-outfit">
                        ₹{liveItem.price.final_price.toLocaleString('en-IN')}
                      </div>
                      <div className="flex flex-wrap items-center gap-2 text-[11px] text-slate-500 font-semibold">
                        <span>List Price: ₹{liveItem.price.list_price.toLocaleString('en-IN')}</span>
                        {liveItem.price.discount_amount > 0 && (
                          <span className="text-emerald-600 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                            -₹{liveItem.price.discount_amount.toLocaleString('en-IN')} OFF
                          </span>
                        )}
                        <span>+ ₹{liveItem.price.tax_amount.toLocaleString('en-IN')} tax ({liveItem.price.tax_percentage}%)</span>
                      </div>
                    </div>
                  ) : (
                    <div className="text-3xl sm:text-4xl font-black text-slate-900 font-outfit">
                      <span className="text-xl">Contact for Pricing</span>
                    </div>
                  )}
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                  <button
                    onClick={() => {
                      if (!isAuthenticated) {
                        navigate('/login', { state: { from: `${location.pathname}${location.search}` } });
                        return;
                      }
                      toggleCartItem(liveItem.id, normalizedLiveItem);
                    }}
                    className={`w-full font-outfit font-bold text-sm py-3.5 rounded-xl shadow-lg transition flex items-center justify-center gap-2 cursor-pointer ${
                      isCarted ? 'bg-emerald-600 text-white' : 'bg-brand-red hover:bg-brand-red-dark text-white shadow-brand-red/25'
                    }`}>
                    <i className={`fa-solid ${isCarted ? 'fa-check' : 'fa-plus'} text-xs`}></i>
                    <span>{isCarted ? 'Added to Cart' : 'Add to Cart'}</span>
                  </button>

                  <button
                    onClick={() => {
                      setInquiryContext({
                        subject: `Enquiry: ${liveItem.title}`,
                        description: `I'm interested in "${liveItem.title}"${liveItem.category?.name ? ` (${liveItem.category.name})` : ''}. Please share more details.`,
                        items: [liveItem.title],
                      });
                      setIsInquiryOpen(true);
                    }}
                    className="w-full bg-slate-900 hover:bg-slate-800 text-white font-outfit font-bold text-sm py-3.5 rounded-xl transition flex items-center justify-center gap-2 cursor-pointer">
                    <i className="fa-solid fa-paper-plane text-xs"></i>
                    <span>Enquire Now</span>
                  </button>
                </div>
              </div>

            </div>

          </div>

        </div>
      </section>

      {/* CONTENT SECTION */}
      {liveItem.description && (
        <section className="py-16 px-4 sm:px-6 bg-white border-t border-slate-200">
          <div className="max-w-4xl mx-auto">
            <span className="text-brand-red font-outfit font-bold text-xs uppercase tracking-widest block mb-2">Overview</span>
            <h2 className="font-outfit font-extrabold text-2xl sm:text-3xl text-slate-900 mb-6">
              About {liveItem.title}
            </h2>
            <div className="blog-content text-sm sm:text-base" dangerouslySetInnerHTML={{ __html: liveItem.description }} />
          </div>
        </section>
      )}

      {/* FAQS SECTION */}
      {faqs.length > 0 && (
        <section className="py-16 px-4 sm:px-6 bg-slate-100 border-t border-slate-200">
          <div className="max-w-4xl mx-auto space-y-10">
            <div className="text-center">
              <span className="text-brand-red font-outfit font-bold text-xs uppercase tracking-widest block mb-2">Got Questions?</span>
              <h2 className="font-outfit font-extrabold text-3xl sm:text-4xl text-slate-900 mb-3">
                {liveItem.title} FAQs
              </h2>
            </div>

            <div className="space-y-4">
              {faqs.map((faq, index) => (
                <div key={faq.id ?? index} className={`faq-item ${activeFaq === index ? 'active' : ''}`}>
                  <div onClick={() => setActiveFaq(activeFaq === index ? null : index)} className="faq-header cursor-pointer">
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
