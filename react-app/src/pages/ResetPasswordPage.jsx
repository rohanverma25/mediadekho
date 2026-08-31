import React, { useState } from 'react';
import { Link, useNavigate, useSearchParams } from 'react-router-dom';
import { useSettings } from '../context/SettingsContext';
import { useCart } from '../context/CartContext';
import { Skeleton } from '../components/Skeleton';
import { resetPassword } from '../services/authService';
import { ApiError } from '../services/api';
import { useDocumentMeta } from '../hooks/useDocumentMeta';

export const ResetPasswordPage = () => {
  useDocumentMeta({ title: 'Reset Password', noindex: true });

  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const { settings } = useSettings();
  const { showToast } = useCart();

  const token = searchParams.get('token');
  const email = searchParams.get('email');

  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);

  const missingLink = !token || !email;

  const handleSubmit = async (e) => {
    e.preventDefault();
    setErrorMessage('');
    setIsSubmitting(true);

    try {
      await resetPassword({ token, email, password, password_confirmation: passwordConfirmation });
      showToast('Password reset successfully! You can now log in.', 'success');
      navigate('/login', { replace: true });
    } catch (err) {
      const message =
        err instanceof ApiError && err.status === 422
          ? err.body?.errors?.email?.[0] || err.body?.errors?.password?.[0] || 'This reset link is invalid or has expired.'
          : err.message || 'Something went wrong. Please try again.';
      setErrorMessage(message);
      showToast(message, 'info');
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="min-h-screen flex flex-col justify-between bg-slate-50">
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
        </div>
      </header>

      <main className="flex-1 flex items-center justify-center p-4 sm:p-6 py-12">
        <div className="w-full max-w-md bg-white rounded-3xl border border-slate-200 shadow-2xl p-8 space-y-6">

          <div className="text-center space-y-2">
            <div className="w-12 h-12 rounded-2xl bg-red-50 text-brand-red flex items-center justify-center text-xl font-bold mx-auto border border-red-100">
              <i className="fa-solid fa-lock-open"></i>
            </div>
            <h1 className="font-outfit font-extrabold text-2xl sm:text-3xl text-slate-900">Set A New Password</h1>
            {!missingLink && <p className="text-xs text-slate-500 font-medium">Resetting password for <strong>{email}</strong></p>}
          </div>

          {missingLink ? (
            <div className="text-center space-y-4">
              <div className="text-xs text-brand-red font-semibold bg-red-50 border border-red-100 rounded-xl px-3.5 py-3">
                This reset link is missing or malformed. Please request a new one.
              </div>
              <Link to="/forgot-password" className="text-brand-red font-bold text-xs hover:underline inline-flex items-center gap-1.5">
                <i className="fa-solid fa-arrow-left text-[10px]"></i> Request A New Link
              </Link>
            </div>
          ) : (
            <form onSubmit={handleSubmit} className="space-y-4">
              {errorMessage && (
                <div className="text-xs text-brand-red font-semibold bg-red-50 border border-red-100 rounded-xl px-3.5 py-2.5">
                  {errorMessage}
                  {' '}
                  <Link to="/forgot-password" className="underline">Request a new link</Link>
                </div>
              )}

              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">New Password *</label>
                <div className="relative">
                  <input
                    type={showPassword ? 'text' : 'password'}
                    required
                    minLength={8}
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
                <p className="text-[11px] text-slate-400 mt-1">At least 8 characters.</p>
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">Confirm New Password *</label>
                <input
                  type={showPassword ? 'text' : 'password'}
                  required
                  placeholder="••••••••"
                  value={passwordConfirmation}
                  onChange={(e) => setPasswordConfirmation(e.target.value)}
                  className="glass-input w-full rounded-xl px-3.5 py-2.5 text-xs" />
              </div>

              <button
                type="submit"
                disabled={isSubmitting}
                className="w-full bg-brand-red hover:bg-brand-red-dark disabled:opacity-60 disabled:cursor-not-allowed text-white font-outfit font-bold text-sm py-3 rounded-xl shadow-lg shadow-brand-red/25 transition flex items-center justify-center gap-2 cursor-pointer">
                <span>{isSubmitting ? 'Resetting...' : 'Reset Password'}</span>
                <i className={`fa-solid ${isSubmitting ? 'fa-spinner fa-spin' : 'fa-check'} text-xs`}></i>
              </button>
            </form>
          )}
        </div>
      </main>

      <footer className="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500">
        © 2026 Media Dekho Pvt Ltd. All Rights Reserved.
      </footer>
    </div>
  );
};
