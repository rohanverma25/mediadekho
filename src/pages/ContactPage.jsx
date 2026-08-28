import React, { useState } from 'react';
import { useSettings } from '../context/SettingsContext';
import { useCart } from '../context/CartContext';
import { useAuth } from '../context/AuthContext';
import { submitContactLead } from '../services/contactService';
import { ApiError } from '../services/api';
import { Skeleton } from '../components/Skeleton';
import { useDocumentMeta } from '../hooks/useDocumentMeta';

const DEFAULT_PHONE = '+91 89800 04451';

const EMPTY_FORM = {
  name: '',
  email: '',
  phone: '',
  companyName: '',
  location: '',
  subject: '',
  description: '',
};

export const ContactPage = () => {
  useDocumentMeta({
    title: 'Contact Us',
    description: 'Get in touch with Media Dekho for media planning, campaign proposals, and support.',
  });

  const { settings, status: settingsStatus } = useSettings();
  const { showToast } = useCart();
  const { user } = useAuth();

  // Prefilled from the logged-in user's known details — still editable,
  // just saves them re-typing what we already have on file.
  const [form, setForm] = useState({
    ...EMPTY_FORM,
    name: user?.name || '',
    email: user?.email || '',
    phone: user?.phone || '',
  });
  const [errors, setErrors] = useState({});
  const [isSubmitting, setIsSubmitting] = useState(false);

  const settingsLoading = settingsStatus === 'loading';
  const phone = settings?.contact_phone || DEFAULT_PHONE;
  const emails = settings?.contact_emails?.length > 0 ? settings.contact_emails : [];
  const addresses = settings?.contact_addresses?.length > 0
    ? settings.contact_addresses
    : settings?.contact_address
      ? [{ title: 'Office', address: settings.contact_address }]
      : [];

  const primaryAddress = addresses[0]?.address || settings?.contact_address;
  const mapSrc = settings?.map_embed_url
    || (primaryAddress ? `https://www.google.com/maps?q=${encodeURIComponent(primaryAddress)}&output=embed` : null);

  const handleChange = (field) => (e) => setForm((prev) => ({ ...prev, [field]: e.target.value }));

  const handleSubmit = async (e) => {
    e.preventDefault();
    setErrors({});
    setIsSubmitting(true);

    try {
      const res = await submitContactLead(form);
      showToast(res?.message || "Thanks! We've received your message.", 'success');
      setForm({ ...EMPTY_FORM, name: user?.name || '', email: user?.email || '', phone: user?.phone || '' });
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
        <div className="max-w-3xl mx-auto text-center space-y-3">
          <span className="text-brand-red font-outfit font-bold text-xs uppercase tracking-widest block">Get In Touch</span>
          <h1 className="font-outfit font-black text-3xl sm:text-4xl lg:text-5xl text-slate-900 tracking-tight">
            Contact Us
          </h1>
          <p className="text-slate-600 text-sm max-w-xl mx-auto leading-relaxed">
            Have a campaign in mind or a question about our platform? Send us a message and our team will get back to you.
          </p>
        </div>
      </section>

      <section className="py-14 px-4 sm:px-6">
        <div className="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-5 gap-10">

          {/* CONTACT FORM */}
          <form onSubmit={handleSubmit} className="lg:col-span-3 bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-sm space-y-4">
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">Full Name *</label>
                <input
                  type="text"
                  required
                  value={form.name}
                  onChange={handleChange('name')}
                  className={`glass-input w-full rounded-xl px-3.5 py-2.5 text-xs ${fieldError('name') ? 'border-red-400' : ''}`} />
                {fieldError('name') && <p className="text-[11px] text-brand-red mt-1">{fieldError('name')}</p>}
              </div>
              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">Email Address *</label>
                <input
                  type="email"
                  required
                  value={form.email}
                  onChange={handleChange('email')}
                  className={`glass-input w-full rounded-xl px-3.5 py-2.5 text-xs ${fieldError('email') ? 'border-red-400' : ''}`} />
                {fieldError('email') && <p className="text-[11px] text-brand-red mt-1">{fieldError('email')}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">Phone Number</label>
                <input
                  type="tel"
                  value={form.phone}
                  onChange={handleChange('phone')}
                  className="glass-input w-full rounded-xl px-3.5 py-2.5 text-xs" />
              </div>
              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">Company Name</label>
                <input
                  type="text"
                  value={form.companyName}
                  onChange={handleChange('companyName')}
                  className="glass-input w-full rounded-xl px-3.5 py-2.5 text-xs" />
              </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">Location</label>
                <input
                  type="text"
                  placeholder="City, State"
                  value={form.location}
                  onChange={handleChange('location')}
                  className="glass-input w-full rounded-xl px-3.5 py-2.5 text-xs" />
              </div>
              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">Subject</label>
                <input
                  type="text"
                  value={form.subject}
                  onChange={handleChange('subject')}
                  className="glass-input w-full rounded-xl px-3.5 py-2.5 text-xs" />
              </div>
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Description *</label>
              <textarea
                required
                rows="5"
                placeholder="Tell us about your campaign, timeline, or question..."
                value={form.description}
                onChange={handleChange('description')}
                className={`glass-input w-full rounded-xl px-3.5 py-2.5 text-xs resize-none ${fieldError('description') ? 'border-red-400' : ''}`}></textarea>
              {fieldError('description') && <p className="text-[11px] text-brand-red mt-1">{fieldError('description')}</p>}
            </div>

            <button
              type="submit"
              disabled={isSubmitting}
              className="w-full bg-brand-red hover:bg-brand-red-dark disabled:opacity-60 disabled:cursor-not-allowed text-white font-outfit font-bold text-sm py-3.5 rounded-xl shadow-lg shadow-brand-red/25 transition flex items-center justify-center gap-2 cursor-pointer">
              <span>{isSubmitting ? 'Sending...' : 'Send Message'}</span>
              <i className={`fa-solid ${isSubmitting ? 'fa-spinner fa-spin' : 'fa-paper-plane'} text-xs`}></i>
            </button>
          </form>

          {/* CONTACT DETAILS */}
          <div className="lg:col-span-2 space-y-6">
            <div className="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-5">

              <div className="flex items-start gap-3">
                <div className="w-9 h-9 rounded-xl bg-red-50 text-brand-red flex items-center justify-center flex-shrink-0">
                  <i className="fa-solid fa-phone text-sm"></i>
                </div>
                <div>
                  <span className="text-[10px] uppercase tracking-wider text-slate-400 font-bold block mb-0.5">Phone</span>
                  {settingsLoading ? (
                    <Skeleton className="h-4 w-32" />
                  ) : (
                    <a href={`tel:${phone.replace(/\s+/g, '')}`} className="text-sm font-bold text-slate-900 hover:text-brand-red transition">{phone}</a>
                  )}
                </div>
              </div>

              <div className="flex items-start gap-3">
                <div className="w-9 h-9 rounded-xl bg-red-50 text-brand-red flex items-center justify-center flex-shrink-0">
                  <i className="fa-solid fa-envelope text-sm"></i>
                </div>
                <div className="space-y-1.5 flex-1">
                  <span className="text-[10px] uppercase tracking-wider text-slate-400 font-bold block mb-0.5">Email</span>
                  {settingsLoading ? (
                    <Skeleton className="h-4 w-40" />
                  ) : emails.length > 0 ? (
                    emails.map((item, i) => (
                      <div key={i} className="flex items-baseline gap-2">
                        <span className="text-[11px] font-semibold text-slate-500">{item.title}:</span>
                        <a href={`mailto:${item.email}`} className="text-sm font-bold text-slate-900 hover:text-brand-red transition">{item.email}</a>
                      </div>
                    ))
                  ) : settings?.contact_email ? (
                    <a href={`mailto:${settings.contact_email}`} className="text-sm font-bold text-slate-900 hover:text-brand-red transition">{settings.contact_email}</a>
                  ) : (
                    <span className="text-xs text-slate-400">Not available</span>
                  )}
                </div>
              </div>

              <div className="flex items-start gap-3">
                <div className="w-9 h-9 rounded-xl bg-red-50 text-brand-red flex items-center justify-center flex-shrink-0">
                  <i className="fa-solid fa-location-dot text-sm"></i>
                </div>
                <div className="space-y-2 flex-1">
                  <span className="text-[10px] uppercase tracking-wider text-slate-400 font-bold block mb-0.5">Address</span>
                  {settingsLoading ? (
                    <div className="space-y-1.5">
                      <Skeleton className="h-3 w-full" />
                      <Skeleton className="h-3 w-4/5" />
                    </div>
                  ) : addresses.length > 0 ? (
                    addresses.map((item, i) => (
                      <div key={i}>
                        <span className="text-[11px] font-semibold text-slate-500 block">{item.title}</span>
                        <span className="text-xs text-slate-700 leading-relaxed">{item.address}</span>
                      </div>
                    ))
                  ) : (
                    <span className="text-xs text-slate-400">Not available</span>
                  )}
                </div>
              </div>

            </div>

            {/* MAP */}
            {!settingsLoading && mapSrc && (
              <div className="rounded-3xl overflow-hidden border border-slate-200 shadow-sm h-64">
                <iframe
                  title="Office Location"
                  src={mapSrc}
                  width="100%"
                  height="100%"
                  style={{ border: 0 }}
                  loading="lazy"
                  referrerPolicy="no-referrer-when-downgrade"
                />
              </div>
            )}
            {settingsLoading && <Skeleton className="h-64 w-full rounded-3xl" />}
          </div>

        </div>
      </section>
    </div>
  );
};
