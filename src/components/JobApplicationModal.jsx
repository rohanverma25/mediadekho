import React, { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { useCart } from '../context/CartContext';
import { submitJobApplication } from '../services/jobService';
import { ApiError } from '../services/api';

export const JobApplicationModal = ({ job, onClose }) => {
  const { user } = useAuth();
  const { showToast } = useCart();

  const [form, setForm] = useState({
    name: user?.name || '',
    email: user?.email || '',
    phone: '',
    coverLetter: '',
  });
  const [resume, setResume] = useState(null);
  const [errors, setErrors] = useState({});
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleChange = (field) => (e) => setForm((prev) => ({ ...prev, [field]: e.target.value }));

  const handleSubmit = async (e) => {
    e.preventDefault();
    setErrors({});
    setIsSubmitting(true);

    try {
      const res = await submitJobApplication({ jobId: job.id, ...form, resume });
      showToast(res?.message || 'Your application has been submitted.', 'success');
      onClose();
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
          onClick={onClose}
          className="absolute top-5 right-5 text-slate-400 hover:text-slate-900 text-xl cursor-pointer">
          <i className="fa-solid fa-xmark"></i>
        </button>

        <div className="mb-6">
          <div className="w-12 h-12 rounded-2xl bg-red-50 border border-red-100 flex items-center justify-center text-brand-red text-xl mb-3">
            <i className="fa-solid fa-briefcase"></i>
          </div>
          <h3 className="font-outfit font-extrabold text-2xl text-slate-900">Apply Now</h3>
          <p className="text-xs text-slate-500 mt-1 font-medium">{job.title}</p>
        </div>

        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="grid grid-cols-2 gap-3">
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
              <label className="block text-xs font-semibold text-slate-700 mb-1">Email *</label>
              <input
                type="email"
                required
                value={form.email}
                onChange={handleChange('email')}
                className={`glass-input w-full rounded-xl px-3.5 py-2.5 text-xs ${fieldError('email') ? 'border-red-400' : ''}`} />
              {fieldError('email') && <p className="text-[11px] text-brand-red mt-1">{fieldError('email')}</p>}
            </div>
          </div>

          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">Phone Number</label>
            <input
              type="tel"
              value={form.phone}
              onChange={handleChange('phone')}
              className="glass-input w-full rounded-xl px-3.5 py-2.5 text-xs" />
          </div>

          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">Resume <span className="text-slate-400 font-normal">(PDF or Word, max 5MB)</span></label>
            <input
              type="file"
              accept=".pdf,.doc,.docx"
              onChange={(e) => setResume(e.target.files?.[0] || null)}
              className={`glass-input w-full rounded-xl px-3.5 py-2.5 text-xs ${fieldError('resume') ? 'border-red-400' : ''}`} />
            {fieldError('resume') && <p className="text-[11px] text-brand-red mt-1">{fieldError('resume')}</p>}
          </div>

          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">Cover Letter</label>
            <textarea
              rows="4"
              placeholder="Tell us why you're a great fit for this role..."
              value={form.coverLetter}
              onChange={handleChange('coverLetter')}
              className="glass-input w-full rounded-xl px-3.5 py-2.5 text-xs resize-none"></textarea>
          </div>

          <button
            type="submit"
            disabled={isSubmitting}
            className="w-full bg-brand-red hover:bg-brand-red-dark disabled:opacity-60 disabled:cursor-not-allowed text-white font-outfit font-bold text-sm py-3 rounded-xl shadow-lg shadow-brand-red/25 transition flex items-center justify-center gap-2 cursor-pointer">
            <span>{isSubmitting ? 'Submitting...' : 'Submit Application'}</span>
            <i className={`fa-solid ${isSubmitting ? 'fa-spinner fa-spin' : 'fa-paper-plane'} text-xs`}></i>
          </button>
        </form>

      </div>
    </div>
  );
};
