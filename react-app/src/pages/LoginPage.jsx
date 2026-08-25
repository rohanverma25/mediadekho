import React, { useState } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useCart } from '../context/CartContext';
import { useAuth } from '../context/AuthContext';
import { useSettings } from '../context/SettingsContext';
import { Skeleton } from '../components/Skeleton';
import { ApiError } from '../services/api';

export const LoginPage = () => {
  const navigate = useNavigate();
  const location = useLocation();
  const { showToast } = useCart();
  const { login } = useAuth();
  const { settings, status: settingsStatus } = useSettings();
  const settingsLoading = settingsStatus === 'loading';
  const redirectTo = location.state?.from || '/dashboard';

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setErrorMessage('');
    setIsSubmitting(true);

    try {
      await login({ email, password });
      showToast('Logged in successfully! Redirecting...', 'success');
      navigate(redirectTo, { replace: true });
    } catch (err) {
      const message =
        err instanceof ApiError && err.status === 422
          ? err.body?.errors?.email?.[0] || 'These credentials do not match our records.'
          : err.message || 'Something went wrong. Please try again.';
      setErrorMessage(message);
      showToast(message, 'info');
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
            {settingsLoading ? (
              <Skeleton className="h-10 w-32 rounded-xl" />
            ) : settings?.logo_url ? (
              <img
                src={settings.logo_url}
                alt="Media Dekho"
                className="h-10 w-auto max-w-[160px] object-contain group-hover:scale-105 transition-transform"
              />
            ) : (
              <>
                <div className="w-10 h-10 rounded-xl bg-brand-red flex items-center justify-center text-white font-outfit font-black text-xl shadow-lg shadow-brand-red/30 group-hover:scale-105 transition-transform">
                  MD
                </div>
                <div>
                  <span className="font-outfit font-black text-xl tracking-tight text-slate-900 block leading-none">MEDIA</span>
                  <span className="font-outfit font-bold text-xs tracking-widest text-brand-red uppercase block">DEKHO</span>
                </div>
              </>
            )}
          </Link>

          <div className="text-xs text-slate-500">
            Don't have an account? 
            <Link to="/signup" className="text-brand-red font-bold hover:underline ml-1">Sign Up</Link>
          </div>
        </div>
      </header>

      {/* Main Login Container */}
      <main className="flex-1 flex items-center justify-center p-4 sm:p-6 py-12">
        <div className="w-full max-w-md bg-white rounded-3xl border border-slate-200 shadow-2xl p-8 space-y-6">
          
          <div className="text-center space-y-2">
            <div className="w-12 h-12 rounded-2xl bg-red-50 text-brand-red flex items-center justify-center text-xl font-bold mx-auto border border-red-100">
              <i className="fa-solid fa-arrow-right-to-bracket"></i>
            </div>
            <h1 className="font-outfit font-extrabold text-2xl sm:text-3xl text-slate-900">Welcome Back</h1>
            <p className="text-xs text-slate-500 font-medium">Enter your credentials to access your media campaign dashboard.</p>
          </div>

          <form onSubmit={handleSubmit} className="space-y-4">
            {errorMessage && (
              <div className="text-xs text-brand-red font-semibold bg-red-50 border border-red-100 rounded-xl px-3.5 py-2.5">
                {errorMessage}
              </div>
            )}

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Work Email Address *</label>
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
            </div>

            <div>
              <div className="flex justify-between items-center mb-1">
                <label className="block text-xs font-semibold text-slate-700">Password *</label>
                <a href="#" className="text-[11px] text-brand-red font-semibold hover:underline">Forgot password?</a>
              </div>
              <div className="relative">
                <input
                  type={showPassword ? 'text' : 'password'}
                  required
                  placeholder="••••••••"
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
            </div>

            <div className="flex items-center justify-between text-xs">
              <label className="flex items-center gap-2 cursor-pointer text-slate-600">
                <input type="checkbox" defaultChecked className="custom-checkbox rounded" />
                <span>Remember me for 30 days</span>
              </label>
            </div>

            <button
              type="submit"
              disabled={isSubmitting}
              className="w-full bg-brand-red hover:bg-brand-red-dark disabled:opacity-60 disabled:cursor-not-allowed text-white font-outfit font-bold text-sm py-3 rounded-xl shadow-lg shadow-brand-red/25 transition flex items-center justify-center gap-2 cursor-pointer">
              <span>{isSubmitting ? 'Logging In...' : 'Log In to Dashboard'}</span>
              <i className={`fa-solid ${isSubmitting ? 'fa-spinner fa-spin' : 'fa-right-to-bracket'} text-xs`}></i>
            </button>
          </form>

          <div className="pt-4 border-t border-slate-100 text-center text-xs text-slate-500">
            New to Media Dekho? 
            <Link to="/signup" className="text-brand-red font-bold hover:underline ml-1">Create a free account</Link>
          </div>

        </div>
      </main>

      <footer className="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500">
        © 2026 Media Dekho Pvt Ltd. All Rights Reserved.
      </footer>
    </div>
  );
};
