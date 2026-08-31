import React, { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { useCart } from '../context/CartContext';
import { updateProfile } from '../services/profileService';
import { ApiError } from '../services/api';
import { AccountLayout } from '../components/AccountLayout';
import { useDocumentMeta } from '../hooks/useDocumentMeta';

export const ProfilePage = () => {
  useDocumentMeta({ title: 'My Profile', noindex: true });

  const { user, updateUser } = useAuth();
  const { showToast } = useCart();

  // GST is only relevant to agency-tier accounts — Retail/B2C individual
  // customers never need to supply one.
  const isAgencyAccount = user?.roles?.some((role) => role === 'B2B Customer' || role === 'Enterprise Customer');

  const [form, setForm] = useState({
    name: user?.name || '',
    email: user?.email || '',
    phone: user?.phone || '',
    company: user?.company || '',
    gst_number: user?.gst_number || '',
    password: '',
    password_confirmation: '',
  });
  const [errors, setErrors] = useState({});
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleChange = (field) => (e) => setForm((prev) => ({ ...prev, [field]: e.target.value }));
  const fieldError = (field) => errors[field]?.[0];

  const handleSubmit = async (e) => {
    e.preventDefault();
    setErrors({});
    setIsSubmitting(true);

    try {
      const updated = await updateProfile(form);
      updateUser(updated);
      showToast('Profile updated successfully.', 'success');
      setForm((prev) => ({ ...prev, password: '', password_confirmation: '' }));
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

  return (
    <AccountLayout active="profile">
      <div className="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-sm max-w-2xl">
        <div className="mb-6">
          <span className="text-[10px] text-brand-red font-bold uppercase tracking-widest block mb-1">Account Settings</span>
          <h2 className="font-outfit font-extrabold text-xl text-slate-900">Update Profile</h2>
        </div>

        <form onSubmit={handleSubmit} className="space-y-4">
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
              <label className="block text-xs font-semibold text-slate-700 mb-1">Company / Brand</label>
              <input
                type="text"
                value={form.company}
                onChange={handleChange('company')}
                className="glass-input w-full rounded-xl px-3.5 py-2.5 text-xs" />
            </div>
          </div>

          {isAgencyAccount && (
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">GSTIN</label>
              <input
                type="text"
                placeholder="22AAAAA0000A1Z5"
                maxLength={15}
                value={form.gst_number}
                onChange={(e) => setForm((prev) => ({ ...prev, gst_number: e.target.value.toUpperCase() }))}
                className={`glass-input w-full rounded-xl px-3.5 py-2.5 text-xs uppercase ${fieldError('gst_number') ? 'border-red-400' : ''}`} />
              {fieldError('gst_number')
                ? <p className="text-[11px] text-brand-red mt-1">{fieldError('gst_number')}</p>
                : <p className="text-[11px] text-slate-400 mt-1">Used for GST-compliant invoicing on your orders.</p>}
            </div>
          )}

          <div className="pt-4 border-t border-slate-100">
            <h3 className="text-xs font-bold text-slate-700 mb-1">Change Password</h3>
            <p className="text-[11px] text-slate-400 mb-3">Leave blank to keep your current password.</p>
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">New Password</label>
                <input
                  type="password"
                  autoComplete="new-password"
                  value={form.password}
                  onChange={handleChange('password')}
                  className={`glass-input w-full rounded-xl px-3.5 py-2.5 text-xs ${fieldError('password') ? 'border-red-400' : ''}`} />
                {fieldError('password') && <p className="text-[11px] text-brand-red mt-1">{fieldError('password')}</p>}
              </div>
              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">Confirm New Password</label>
                <input
                  type="password"
                  autoComplete="new-password"
                  value={form.password_confirmation}
                  onChange={handleChange('password_confirmation')}
                  className="glass-input w-full rounded-xl px-3.5 py-2.5 text-xs" />
              </div>
            </div>
          </div>

          <button
            type="submit"
            disabled={isSubmitting}
            className="w-full bg-brand-red hover:bg-brand-red-dark disabled:opacity-60 disabled:cursor-not-allowed text-white font-outfit font-bold text-sm py-3 rounded-xl shadow-lg shadow-brand-red/25 transition flex items-center justify-center gap-2 cursor-pointer">
            <span>{isSubmitting ? 'Saving...' : 'Save Changes'}</span>
            <i className={`fa-solid ${isSubmitting ? 'fa-spinner fa-spin' : 'fa-check'} text-xs`}></i>
          </button>
        </form>
      </div>
    </AccountLayout>
  );
};
