import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useCart } from '../context/CartContext';
import { useAuth } from '../context/AuthContext';
import { useSettings } from '../context/SettingsContext';
import { Skeleton } from '../components/Skeleton';
import { ApiError } from '../services/api';

const USER_TYPES = [
  { value: 'retail', label: 'Retail' },
  { value: 'b2c', label: 'B2C' },
  { value: 'b2b', label: 'B2B' },
  { value: 'enterprise', label: 'Enterprise' },
];

export const SignupPage = () => {
  const navigate = useNavigate();
  const { showToast } = useCart();
  const { register } = useAuth();
  const { settings } = useSettings();

  const [fullName, setFullName] = useState('');
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const [company, setCompany] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [userType, setUserType] = useState('retail');
  const [fieldErrors, setFieldErrors] = useState({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [pendingApprovalMessage, setPendingApprovalMessage] = useState(null);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setFieldErrors({});
    setIsSubmitting(true);

    try {
      const result = await register({ name: fullName, email, phone, company, password, userType });

      if (result?.pending_approval) {
        setPendingApprovalMessage(result.message);
        return;
      }

      showToast('Account created successfully! Welcome to Media Dekho.', 'success');
      navigate('/dashboard');
    } catch (err) {
      if (err instanceof ApiError && err.status === 422) {
        const errors = err.body?.errors || {};
        setFieldErrors(Object.fromEntries(Object.entries(errors).map(([k, v]) => [k, v[0]])));
        showToast(err.body?.message || 'Please fix the errors below.', 'info');
      } else {
        showToast(err.message || 'Something went wrong. Please try again.', 'info');
      }
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="min-h-screen flex flex-col justify-between bg-slate-50">
      {/* Header Bar */}
      <header className="bg-white border-b border-slate-200 py-4 px-6">
        <div className="max-w-7xl mx-auto flex justify-between items-center">
          <Link to="/" className="flex items-center gap-3 group">
            {settings?.logo_url ? (
              <img
                src={settings.logo_url}
                alt="Media Dekho"
                className="h-10 w-auto max-w-[160px] object-contain group-hover:scale-105 transition-transform"
              />
            ) : (
              <Skeleton className="h-10 w-32 rounded-xl" />
            )}
          </Link>

          <div className="text-xs text-slate-500">
            Already have an account? 
            <Link to="/login" className="text-brand-red font-bold hover:underline ml-1">Log In</Link>
          </div>
        </div>
      </header>

      {/* Main Signup Container */}
      <main className="flex-1 flex items-center justify-center p-4 sm:p-6 py-12">
        {pendingApprovalMessage ? (
          <div className="w-full max-w-lg bg-white rounded-3xl border border-slate-200 shadow-2xl p-8 text-center space-y-4">
            <div className="w-14 h-14 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-2xl font-bold mx-auto border border-amber-100">
              <i className="fa-solid fa-clock"></i>
            </div>
            <h1 className="font-outfit font-extrabold text-2xl text-slate-900">Account Pending Approval</h1>
            <p className="text-sm text-slate-600 leading-relaxed">{pendingApprovalMessage}</p>
            <Link
              to="/"
              className="inline-flex items-center justify-center gap-2 bg-brand-red hover:bg-brand-red-dark text-white font-outfit font-bold text-sm px-6 py-3 rounded-xl shadow-lg shadow-brand-red/25 transition mt-2">
              Back to Home
            </Link>
          </div>
        ) : (
        <div className="w-full max-w-lg bg-white rounded-3xl border border-slate-200 shadow-2xl p-8 space-y-6">

          <div className="text-center space-y-2">
            <div className="w-12 h-12 rounded-2xl bg-red-50 text-brand-red flex items-center justify-center text-xl font-bold mx-auto border border-red-100">
              <i className="fa-solid fa-user-plus"></i>
            </div>
            <h1 className="font-outfit font-extrabold text-2xl sm:text-3xl text-slate-900">Create Free Account</h1>
            <p className="text-xs text-slate-500 font-medium">Join 5,000+ brands & agencies planning media campaigns with direct owner rates.</p>
          </div>

          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Full Name *</label>
              <div className="relative">
                <input 
                  type="text" 
                  required 
                  placeholder="e.g. Rajesh Kumar" 
                  value={fullName}
                  onChange={(e) => setFullName(e.target.value)}
                  className="glass-input w-full rounded-xl px-3.5 py-2.5 text-xs pl-9" />
                <i className="fa-solid fa-user absolute left-3 top-3 text-slate-400 text-xs"></i>
              </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">Work Email *</label>
                <div className="relative">
                  <input
                    type="email"
                    required
                    placeholder="name@company.com"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    className="glass-input w-full rounded-xl px-3.5 py-2.5 text-xs pl-9" />
                  <i className="fa-solid fa-envelope absolute left-3 top-3 text-slate-400 text-xs"></i>
                </div>
                {fieldErrors.email && <span className="text-[11px] text-brand-red font-medium mt-1 block">{fieldErrors.email}</span>}
              </div>
              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">Phone Number *</label>
                <div className="relative">
                  <input
                    type="tel"
                    required
                    placeholder="+91 98765 43210"
                    value={phone}
                    onChange={(e) => setPhone(e.target.value)}
                    className="glass-input w-full rounded-xl px-3.5 py-2.5 text-xs pl-9" />
                  <i className="fa-solid fa-phone absolute left-3 top-3 text-slate-400 text-xs"></i>
                </div>
                {fieldErrors.phone && <span className="text-[11px] text-brand-red font-medium mt-1 block">{fieldErrors.phone}</span>}
              </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">Company / Brand Name</label>
                <div className="relative">
                  <input
                    type="text"
                    placeholder="e.g. TechCorp Solutions"
                    value={company}
                    onChange={(e) => setCompany(e.target.value)}
                    className="glass-input w-full rounded-xl px-3.5 py-2.5 text-xs pl-9" />
                  <i className="fa-solid fa-building absolute left-3 top-3 text-slate-400 text-xs"></i>
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">Account Type *</label>
                <div className="relative">
                  <select
                    required
                    value={userType}
                    onChange={(e) => setUserType(e.target.value)}
                    className="glass-input w-full rounded-xl px-3.5 py-2.5 text-xs pl-9 cursor-pointer appearance-none">
                    {USER_TYPES.map((type) => (
                      <option key={type.value} value={type.value}>{type.label}</option>
                    ))}
                  </select>
                  <i className="fa-solid fa-briefcase absolute left-3 top-3 text-slate-400 text-xs"></i>
                  <i className="fa-solid fa-chevron-down absolute right-3 top-3 text-slate-400 text-[10px] pointer-events-none"></i>
                </div>
                {fieldErrors.user_type && <span className="text-[11px] text-brand-red font-medium mt-1 block">{fieldErrors.user_type}</span>}
              </div>
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Create Password *</label>
              <div className="relative">
                <input
                  type={showPassword ? 'text' : 'password'}
                  required
                  placeholder="At least 8 characters"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  className="glass-input w-full rounded-xl px-3.5 py-2.5 text-xs pl-9 pr-9" />
                <i className="fa-solid fa-lock absolute left-3 top-3 text-slate-400 text-xs"></i>
                <button
                  type="button"
                  onClick={() => setShowPassword((prev) => !prev)}
                  tabIndex={-1}
                  aria-label={showPassword ? 'Hide password' : 'Show password'}
                  className="absolute right-3 top-2.5 text-slate-400 hover:text-brand-red text-xs cursor-pointer">
                  <i className={`fa-solid ${showPassword ? 'fa-eye-slash' : 'fa-eye'}`}></i>
                </button>
              </div>
              {fieldErrors.password && <span className="text-[11px] text-brand-red font-medium mt-1 block">{fieldErrors.password}</span>}
            </div>

            <div className="flex items-start gap-2 text-xs">
              <input type="checkbox" required className="custom-checkbox rounded mt-0.5" />
              <span className="text-slate-600">I agree to the <a href="#" className="text-brand-red font-semibold hover:underline">Terms of Service</a> and <a href="#" className="text-brand-red font-semibold hover:underline">Privacy Policy</a>.</span>
            </div>

            <button
              type="submit"
              disabled={isSubmitting}
              className="w-full bg-brand-red hover:bg-brand-red-dark disabled:opacity-60 disabled:cursor-not-allowed text-white font-outfit font-bold text-sm py-3 rounded-xl shadow-lg shadow-brand-red/25 transition flex items-center justify-center gap-2 cursor-pointer">
              <span>{isSubmitting ? 'Creating Account...' : 'Create Account'}</span>
              <i className={`fa-solid ${isSubmitting ? 'fa-spinner fa-spin' : 'fa-check'} text-xs`}></i>
            </button>
          </form>

          <div className="pt-4 border-t border-slate-100 text-center text-xs text-slate-500">
            Already have an account? 
            <Link to="/login" className="text-brand-red font-bold hover:underline ml-1">Log In here</Link>
          </div>

        </div>
        )}
      </main>

      <footer className="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500">
        © 2026 Media Dekho Pvt Ltd. All Rights Reserved.
      </footer>
    </div>
  );
};
