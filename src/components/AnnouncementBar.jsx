import React, { useState } from 'react';
import { useAnnouncements } from '../hooks/useAnnouncements';

const DISMISSED_KEY = 'md_dismissed_announcement_id';

/**
 * Rendered once at the top of the app (above Header and the standalone
 * auth pages) so it shows on every page, not just the customer dashboard
 * where announcements also appear in full. Only the highest-priority
 * active announcement is shown here — this is a lightweight sitewide
 * notice, not the full list.
 */
export const AnnouncementBar = () => {
  const { announcements, status } = useAnnouncements();
  const [dismissedId, setDismissedId] = useState(() => {
    try {
      return sessionStorage.getItem(DISMISSED_KEY);
    } catch {
      return null;
    }
  });

  const announcement = announcements[0];

  if (status !== 'success' || !announcement || String(announcement.id) === dismissedId) {
    return null;
  }

  const dismiss = () => {
    try {
      sessionStorage.setItem(DISMISSED_KEY, String(announcement.id));
    } catch {
      // Private browsing / storage disabled — dismissal just won't persist across reloads.
    }
    setDismissedId(String(announcement.id));
  };

  return (
    <div className="bg-slate-900 text-white text-xs py-2 px-4 relative">
      <div className="max-w-7xl mx-auto flex items-center justify-center gap-2 pr-6 text-center">
        <i className="fa-solid fa-bullhorn text-amber-400 flex-shrink-0"></i>
        <span className="font-semibold">{announcement.title}</span>
        {announcement.message && (
          <span className="hidden sm:inline text-slate-300 font-normal line-clamp-1">— {announcement.message}</span>
        )}
      </div>
      <button
        onClick={dismiss}
        aria-label="Dismiss announcement"
        className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white transition cursor-pointer">
        <i className="fa-solid fa-xmark text-xs"></i>
      </button>
    </div>
  );
};
