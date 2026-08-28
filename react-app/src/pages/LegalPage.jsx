import React from 'react';
import { useSettings } from '../context/SettingsContext';
import { Skeleton } from '../components/Skeleton';
import { useDocumentMeta } from '../hooks/useDocumentMeta';

/**
 * Renders a single admin-managed legal page (Privacy Policy, Terms of Use)
 * straight from Settings — same singleton record Header/Footer already
 * poll, so no separate fetch/hook is needed here.
 */
export const LegalPage = ({ title, field }) => {
  const { settings, status } = useSettings();
  const content = settings?.[field];

  // A blank plain-text summary is the honest description here — the real
  // content is arbitrary admin-authored HTML, not something to safely
  // truncate into a meta description without risking cut-off markup.
  useDocumentMeta({ title });

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 py-14">
      <h1 className="font-outfit font-black text-3xl sm:text-4xl text-slate-900 tracking-tight mb-8">{title}</h1>

      {status === 'loading' && (
        <div className="space-y-3">
          <Skeleton className="h-3 w-full" />
          <Skeleton className="h-3 w-full" />
          <Skeleton className="h-3 w-4/5" />
          <Skeleton className="h-3 w-full" />
          <Skeleton className="h-3 w-3/5" />
        </div>
      )}

      {status !== 'loading' && content && (
        <div className="blog-content text-sm sm:text-base" dangerouslySetInnerHTML={{ __html: content }} />
      )}

      {status !== 'loading' && !content && (
        <p className="text-sm text-slate-500">This page hasn't been published yet. Check back soon.</p>
      )}
    </div>
  );
};
