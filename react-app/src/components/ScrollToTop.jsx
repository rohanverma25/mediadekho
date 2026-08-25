import { useEffect } from 'react';
import { useLocation } from 'react-router-dom';

/**
 * React Router doesn't reset scroll position on navigation (browsers only
 * do that on a full page load) — without this, clicking a link while
 * scrolled halfway down a page leaves the next page open at that same
 * scroll offset instead of starting at the top.
 */
export const ScrollToTop = () => {
  const { pathname } = useLocation();

  useEffect(() => {
    window.scrollTo(0, 0);
  }, [pathname]);

  return null;
};
