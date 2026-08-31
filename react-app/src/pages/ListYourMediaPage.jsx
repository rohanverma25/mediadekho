import React, { useState } from 'react';
import { useCart } from '../context/CartContext';
import { useAuth } from '../context/AuthContext';
import { submitMediaListingRequest } from '../services/mediaListingRequestService';
import { ApiError } from '../services/api';
import { useDocumentMeta } from '../hooks/useDocumentMeta';
import { usePageMeta } from '../hooks/usePageMeta';

const BENEFITS = [
  {
    icon: 'fa-users',
    title: 'Reach New Customers At No Cost',
    description: "Get your own free sales team — list your media once and let advertisers across India discover it, without spending on marketing.",
  },
  {
    icon: 'fa-magnifying-glass-dollar',
    title: 'Advertisers Can Find & Book Easily',
    description: 'Your inventory shows up in search and category listings, so brands can find, compare, and book your media in a few clicks.',
  },
  {
    icon: 'fa-bullhorn',
    title: 'Ease Of Promotion',
    description: 'We handle the presentation — photos, pricing, and details — so your media gets promoted the right way to the right audience.',
  },
  {
    icon: 'fa-star',
    title: 'Get Recommended',
    description: 'Our platform recommends your media to advertisers actively looking for inventory that matches what you offer.',
  },
];

const EMPTY_FORM = {
  companyName: '',
  contactName: '',
  email: '',
  phone: '',
  mediaTitle: '',
  mediaType: '',
  location: '',
  approximateRate: '',
  description: '',
};

export const ListYourMediaPage = () => {
  const { meta } = usePageMeta('list-your-media');
  useDocumentMeta({
    title: meta?.title || 'List Your Media',
    description: meta?.description || 'List your media inventory on Media Dekho for free and get discovered by advertisers across India.',
    image: meta?.og_image_url,
  });

  const { showToast } = useCart();
  const { user } = useAuth();

  const [form, setForm] = useState({
    ...EMPTY_FORM,
    contactName: user?.name || '',
    email: user?.email || '',
    phone: user?.phone || '',
  });
  const [image, setImage] = useState(null);
  const [mediaKit, setMediaKit] = useState(null);
  const [errors, setErrors] = useState({});
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleChange = (field) => (e) => setForm((prev) => ({ ...prev, [field]: e.target.value }));

  const handleSubmit = async (e) => {
    e.preventDefault();
    setErrors({});
    setIsSubmitting(true);

    try {
      const res = await submitMediaListingRequest({ ...form, image, mediaKit });
      showToast(res?.message || "Thanks! We've received your media details.", 'success');
      setForm({ ...EMPTY_FORM, contactName: user?.name || '', email: user?.email || '', phone: user?.phone || '' });
      setImage(null);
      setMediaKit(null);
    } catch (err) {
      if (err instanceof ApiError && err.status === 422) {
        setErrors(err.body?.errors || {});
        showToast('Please check the form and try again.', 'info');
      } else {
        showToast(err.message || 'Something went wrong. Please try again.', 'info');
      }
    } finally {
      setIsSubmitting(false);
    }
  };

  const fieldError = (field) => errors[field]?.[0];

  return (
    <div>
      {/* HEADER BANNER */}
      <section className="bg-gradient-to-b from-slate-100 to-slate-50 border-b border-slate-200 py-14 px-4 sm:px-6">
        <div className="max-w-3xl mx-auto text-center space-y-4">
          <span className="text-brand-red font-outfit font-bold text-xs uppercase tracking-widest block">List Your Media</span>
          <h1 className="font-outfit font-black text-3xl sm:text-4xl lg:text-5xl text-slate-900 tracking-tight">
            List Your Media And Get New Clients
          </h1>
          <p className="text-slate-600 text-sm max-w-xl mx-auto leading-relaxed">
            Own a hoarding, newspaper, radio slot, or any advertising media? List it on Media Dekho for free and reach out to advertisers across the country.
          </p>
          <div className="flex flex-wrap items-center justify-center gap-3 pt-2">
            <span className="inline-flex items-center gap-1.5 bg-white border border-slate-200 rounded-full px-4 py-1.5 text-xs font-bold text-slate-700 shadow-sm">
              <i className="fa-solid fa-circle-check text-emerald-500"></i> Free Listing
            </span>
            <span className="inline-flex items-center gap-1.5 bg-white border border-slate-200 rounded-full px-4 py-1.5 text-xs font-bold text-slate-700 shadow-sm">
              <i className="fa-solid fa-circle-check text-emerald-500"></i> No Cost To Advertisers Finding You
            </span>
          </div>
        </div>
      </section>

      {/* FORM */}
      <section className="py-14 px-4 sm:px-6">
        <div className="max-w-3xl mx-auto">
          <form onSubmit={handleSubmit} className="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-sm space-y-4">
            <div>
              <h2 className="font-outfit font-extrabold text-xl text-slate-900">Tell Us About Your Media</h2>
              <p className="text-xs text-slate-500 mt-1">Our team will review your details and reach out to get it listed.</p>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">Company Name *</label>
                <input
                  type="text"
                  required
                  value={form.companyName}
                  onChange={handleChange('companyName')}
                  className={`glass-input w-full rounded-xl px-3.5 py-2.5 text-xs ${fieldError('company_name') ? 'border-red-400' : ''}`} />
                {fieldError('company_name') && <p className="text-[11px] text-brand-red mt-1">{fieldError('company_name')}</p>}
              </div>
              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">Your Name *</label>
                <input
                  type="text"
                  required
                  value={form.contactName}
                  onChange={handleChange('contactName')}
                  className={`glass-input w-full rounded-xl px-3.5 py-2.5 text-xs ${fieldError('contact_name') ? 'border-red-400' : ''}`} />
                {fieldError('contact_name') && <p className="text-[11px] text-brand-red mt-1">{fieldError('contact_name')}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">Email *</label>
                <input
                  type="email"
                  required
                  value={form.email}
                  onChange={handleChange('email')}
                  className={`glass-input w-full rounded-xl px-3.5 py-2.5 text-xs ${fieldError('email') ? 'border-red-400' : ''}`} />
                {fieldError('email') && <p className="text-[11px] text-brand-red mt-1">{fieldError('email')}</p>}
              </div>
              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">Phone Number *</label>
                <input
                  type="tel"
                  required
                  value={form.phone}
                  onChange={handleChange('phone')}
                  className={`glass-input w-full rounded-xl px-3.5 py-2.5 text-xs ${fieldError('phone') ? 'border-red-400' : ''}`} />
                {fieldError('phone') && <p className="text-[11px] text-brand-red mt-1">{fieldError('phone')}</p>}
              </div>
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Media / Property Title *</label>
              <input
                type="text"
                required
                placeholder="e.g. Downtown Highway Billboard"
                value={form.mediaTitle}
                onChange={handleChange('mediaTitle')}
                className={`glass-input w-full rounded-xl px-3.5 py-2.5 text-xs ${fieldError('media_title') ? 'border-red-400' : ''}`} />
              {fieldError('media_title') && <p className="text-[11px] text-brand-red mt-1">{fieldError('media_title')}</p>}
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">Media Type</label>
                <input
                  type="text"
                  placeholder="e.g. Hoarding, Newspaper, Radio"
                  value={form.mediaType}
                  onChange={handleChange('mediaType')}
                  className="glass-input w-full rounded-xl px-3.5 py-2.5 text-xs" />
              </div>
              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">Location</label>
                <input
                  type="text"
                  placeholder="City, State"
                  value={form.location}
                  onChange={handleChange('location')}
                  className="glass-input w-full rounded-xl px-3.5 py-2.5 text-xs" />
              </div>
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Approximate Rate</label>
              <input
                type="text"
                placeholder="e.g. ₹50,000/month"
                value={form.approximateRate}
                onChange={handleChange('approximateRate')}
                className="glass-input w-full rounded-xl px-3.5 py-2.5 text-xs" />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Media Details</label>
              <textarea
                rows="4"
                placeholder="Dimensions, footfall/circulation, availability, or anything else advertisers should know..."
                value={form.description}
                onChange={handleChange('description')}
                className="glass-input w-full rounded-xl px-3.5 py-2.5 text-xs resize-none"></textarea>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">Photo <span className="text-slate-400 font-normal">(JPG/PNG, max 5MB)</span></label>
                <input
                  type="file"
                  accept=".jpg,.jpeg,.png,.webp"
                  onChange={(e) => setImage(e.target.files?.[0] || null)}
                  className={`glass-input w-full rounded-xl px-3.5 py-2.5 text-xs ${fieldError('image') ? 'border-red-400' : ''}`} />
                {fieldError('image') && <p className="text-[11px] text-brand-red mt-1">{fieldError('image')}</p>}
              </div>
              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">Media Kit <span className="text-slate-400 font-normal">(PDF/Doc, max 10MB)</span></label>
                <input
                  type="file"
                  accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                  onChange={(e) => setMediaKit(e.target.files?.[0] || null)}
                  className={`glass-input w-full rounded-xl px-3.5 py-2.5 text-xs ${fieldError('media_kit') ? 'border-red-400' : ''}`} />
                {fieldError('media_kit') && <p className="text-[11px] text-brand-red mt-1">{fieldError('media_kit')}</p>}
              </div>
            </div>

            <button
              type="submit"
              disabled={isSubmitting}
              className="w-full bg-brand-red hover:bg-brand-red-dark disabled:opacity-60 disabled:cursor-not-allowed text-white font-outfit font-bold text-sm py-3.5 rounded-xl shadow-lg shadow-brand-red/25 transition flex items-center justify-center gap-2 cursor-pointer">
              <span>{isSubmitting ? 'Submitting...' : 'List Now'}</span>
              <i className={`fa-solid ${isSubmitting ? 'fa-spinner fa-spin' : 'fa-paper-plane'} text-xs`}></i>
            </button>
          </form>
        </div>
      </section>

      {/* BENEFITS */}
      <section className="py-14 px-4 sm:px-6 bg-slate-50 border-y border-slate-200">
        <div className="max-w-6xl mx-auto">
          <div className="text-center max-w-xl mx-auto mb-10 space-y-2">
            <span className="text-brand-red font-outfit font-bold text-xs uppercase tracking-widest block">Why List With Us</span>
            <h2 className="font-outfit font-black text-2xl sm:text-3xl text-slate-900 tracking-tight">Benefits Of Listing</h2>
          </div>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {BENEFITS.map((benefit) => (
              <div key={benefit.title} className="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm text-center space-y-3">
                <div className="w-12 h-12 mx-auto rounded-2xl bg-red-50 border border-red-100 flex items-center justify-center text-brand-red text-lg">
                  <i className={`fa-solid ${benefit.icon}`}></i>
                </div>
                <h3 className="font-outfit font-bold text-sm text-slate-900">{benefit.title}</h3>
                <p className="text-xs text-slate-500 leading-relaxed">{benefit.description}</p>
              </div>
            ))}
          </div>
        </div>
      </section>
    </div>
  );
};
