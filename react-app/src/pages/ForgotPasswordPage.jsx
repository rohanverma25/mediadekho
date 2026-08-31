import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useSettings } from '../context/SettingsContext';
import { Skeleton } from '../components/Skeleton';
import { forgotPassword } from '../services/authService';
import { useDocumentMeta } from '../hooks/useDocumentMeta';

export const ForgotPasswordPage = () => {
  useDocumentMeta({ title: 'Forgot Password', noindex: true });

  const { settings } = useSettings();

  const [email, setEmail] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isSent, setIsSent] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setIsSubmitting(true);

    try {
      await forgotPassword({ email });
    } catch {
      // The API always returns a generic success message regardless of
      // whether the email exists (anti-enumeration) — a network/server
      // error is the only way this can actually throw, and even then
      // showing the same "check your inbox" state is the safer default
      // rather than confirming/denying the account's existence.
    } finally {
      setIsSubmitting(false);
      setIsSent(true);
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

          <div className="text-xs text-slate-500">
            Remembered your password?
            <Link to="/login" className="text-brand-red font-bold hover:underline ml-1">Log In</Link>
          </div>
        </div>
      </header>

      <main className="flex-1 flex items-center justify-center p-4 sm:p-6 py-12">
        <div className="w-full max-w-md bg-white rounded-3xl border border-slate-200 shadow-2xl p-8 space-y-6">

          <div className="text-center space-y-2">
            <div className="w-12 h-12 rounded-2xl bg-red-50 text-brand-red flex items-center justify-center text-xl font-bold mx-auto border border-red-100">
              <i className="fa-solid fa-key"></i>
            </div>
            <h1 className="font-outfit font-extrabold text-2xl sm:text-3xl text-slate-900">Forgot Password?</h1>
            <p className="text-xs text-slate-500 font-medium">
              Enter the email on your account and we'll send you a link to reset your password.
            </p>
          </div>

          {isSent ? (
            <div className="text-center space-y-4">
              <div className="text-xs text-emerald-700 font-semibold bg-emerald-50 border border-emerald-100 rounded-xl px-3.5 py-3 flex items-start gap-2 text-left">
                <i className="fa-solid fa-circle-check mt-0.5"></i>
                <span>If an account exists for <strong>{email}</strong>, we've sent a password reset link to that inbox. It expires in 60 minutes.</span>
              </div>
              <Link to="/login" className="text-brand-red font-bold text-xs hover:underline inline-flex items-center gap-1.5">
                <i className="fa-solid fa-arrow-left text-[10px]"></i> Back to Log In
              </Link>
            </div>
          ) : (
            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">Email Address *</label>
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

              <button
                type="submit"
                disabled={isSubmitting}
                className="w-full bg-brand-red hover:bg-brand-red-dark disabled:opacity-60 disabled:cursor-not-allowed text-white font-outfit font-bold text-sm py-3 rounded-xl shadow-lg shadow-brand-red/25 transition flex items-center justify-center gap-2 cursor-pointer">
                <span>{isSubmitting ? 'Sending...' : 'Send Reset Link'}</span>
                <i className={`fa-solid ${isSubmitting ? 'fa-spinner fa-spin' : 'fa-paper-plane'} text-xs`}></i>
              </button>

              <div className="text-center">
                <Link to="/login" className="text-slate-500 font-semibold text-xs hover:text-brand-red inline-flex items-center gap-1.5">
                  <i className="fa-solid fa-arrow-left text-[10px]"></i> Back to Log In
                </Link>
              </div>
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
