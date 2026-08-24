import React, { useEffect, useState } from 'react';
import { useCart } from '../context/CartContext';
import { useAuth } from '../context/AuthContext';
import { submitContactLead } from '../services/contactService';
import { ApiError } from '../services/api';

const EMPTY_FORM = {
  name: '',
  email: '',
  phone: '',
  companyName: '',
  location: '',
  subject: '',
  description: '',
};

export const InquiryModal = () => {
  const { isInquiryOpen, setIsInquiryOpen, inquiryContext, setInquiryContext, showToast } = useCart();
  const { user } = useAuth();
  const [form, setForm] = useState(EMPTY_FORM);
  const [errors, setErrors] = useState({});
  const [isSubmitting, setIsSubmitting] = useState(false);

  // Re-seed the form from whatever context the caller passed (e.g. a
  // listing's title) each time the modal opens, so a generic "Enquire Now"
  // from the header doesn't carry over a previous listing's subject. A
  // logged-in user's known details are prefilled too — still editable,
  // just saves them re-typing what we already have on file.
  useEffect(() => {
    if (isInquiryOpen) {
      setForm({
        ...EMPTY_FORM,
        name: user?.name || '',
        email: user?.email || '',
        phone: user?.phone || '',
        subject: inquiryContext?.subject || '',
        description: inquiryContext?.description || '',
      });
      setErrors({});
    }
  }, [isInquiryOpen, inquiryContext, user]);

  if (!isInquiryOpen) return null;

  const handleChange = (field) => (e) => setForm((prev) => ({ ...prev, [field]: e.target.value }));

  const closeModal = () => {
    setIsInquiryOpen(false);
    setInquiryContext(null);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setErrors({});
    setIsSubmitting(true);

    try {
      const res = await submitContactLead(form);
      showToast(res?.message || "Thanks! We've received your enquiry and will get back to you shortly.", 'success');
      closeModal();
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
    <div className="fixed inset-0 z-50 modal-backdrop flex items-center justify-center p-4">
      <div className="bg-white w-full max-w-lg rounded-3xl border border-slate-200 shadow-2xl p-6 sm:p-8 relative animate-in fade-in zoom-in duration-200 max-h-[90vh] overflow-y-auto">

        <button
          onClick={closeModal}
          className="absolute top-5 right-5 text-slate-400 hover:text-slate-900 text-xl cursor-pointer">
          <i className="fa-solid fa-xmark"></i>
        </button>

        <div className="mb-6 text-center">
          <div className="w-12 h-12 rounded-2xl bg-red-50 border border-red-100 flex items-center justify-center text-brand-red text-xl mx-auto mb-3">
            <i className="fa-solid fa-paper-plane"></i>
          </div>
          <h3 className="font-outfit font-extrabold text-2xl text-slate-900">Enquire Now</h3>
          <p className="text-xs text-slate-500 mt-1 font-medium">Send us your details and our team will get back to you shortly.</p>
        </div>

        {inquiryContext?.items?.length > 0 && (
          <div className="mb-4 bg-slate-50 border border-slate-200 rounded-xl p-3">
            <span className="text-[10px] text-slate-400 uppercase font-bold tracking-widest block mb-1.5">
              Regarding {inquiryContext.items.length > 1 ? `${inquiryContext.items.length} Listings` : 'This Listing'}
            </span>
            <ul className="space-y-1">
              {inquiryContext.items.map((name, i) => (
                <li key={i} className="text-xs font-semibold text-slate-800 flex items-start gap-1.5">
                  <i className="fa-solid fa-circle-check text-brand-red text-[9px] mt-0.5"></i>
                  <span>{name}</span>
                </li>
              ))}
            </ul>
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
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
              <label className="block text-xs font-semibold text-slate-700 mb-1">Work Email *</label>
              <input
                type="email"
                required
                value={form.email}
                onChange={handleChange('email')}
                className={`glass-input w-full rounded-xl px-3.5 py-2.5 text-xs ${fieldError('email') ? 'border-red-400' : ''}`} />
              {fieldError('email') && <p className="text-[11px] text-brand-red mt-1">{fieldError('email')}</p>}
            </div>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Phone Number</label>
              <input
                type="tel"
                value={form.phone}
                onChange={handleChange('phone')}
                className="glass-input w-full rounded-xl px-3.5 py-2.5 text-xs" />
            </div>
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Company / Brand</label>
              <input
                type="text"
                value={form.companyName}
                onChange={handleChange('companyName')}
                className="glass-input w-full rounded-xl px-3.5 py-2.5 text-xs" />
            </div>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
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
              rows="4"
              placeholder="Tell us your target locations, timeline, or preferred media options..."
              value={form.description}
              onChange={handleChange('description')}
              className={`glass-input w-full rounded-xl px-3.5 py-2.5 text-xs resize-none ${fieldError('description') ? 'border-red-400' : ''}`}></textarea>
            {fieldError('description') && <p className="text-[11px] text-brand-red mt-1">{fieldError('description')}</p>}
          </div>

          <button
            type="submit"
            disabled={isSubmitting}
            className="w-full bg-brand-red hover:bg-brand-red-dark disabled:opacity-60 disabled:cursor-not-allowed text-white font-outfit font-bold text-sm py-3 rounded-xl shadow-lg shadow-brand-red/25 transition flex items-center justify-center gap-2 cursor-pointer">
            <span>{isSubmitting ? 'Sending...' : 'Submit Enquiry'}</span>
            <i className={`fa-solid ${isSubmitting ? 'fa-spinner fa-spin' : 'fa-check'} text-xs`}></i>
          </button>
        </form>

      </div>
    </div>
  );
};
