import React, { createContext, useCallback, useContext, useEffect, useRef, useState } from 'react';
import { fetchSettings } from '../services/settingsService';

// How often to re-poll for changes once the initial load succeeds. This is
// what makes an admin's save show up on an already-open tab without the
// visitor having to refresh — no websockets/broadcasting needed for
// something that only needs to be "eventually live," not instant.
const POLL_INTERVAL_MS = 30000;

const SettingsContext = createContext();

/**
 * Parses admin-authored HTML (analytics tags, chat widgets, etc.) and
 * inserts it into the given DOM node. Plain innerHTML assignment doesn't
 * execute <script> tags, so each one is rebuilt as a real script element;
 * everything else (e.g. a GA <noscript> fallback) is appended as-is.
 */
function injectRawHtml(html, target) {
  if (!html) return;

  const container = document.createElement('div');
  container.innerHTML = html;

  Array.from(container.childNodes).forEach((node) => {
    if (node.tagName === 'SCRIPT') {
      const script = document.createElement('script');
      Array.from(node.attributes).forEach((attr) => script.setAttribute(attr.name, attr.value));
      script.textContent = node.textContent;
      target.appendChild(script);
    } else {
      target.appendChild(node);
    }
  });
}

let scriptsInjected = false;

export const SettingsProvider = ({ children }) => {
  const [settings, setSettings] = useState(null);
  const [status, setStatus] = useState('loading');
  const settingsRef = useRef(null);

  const load = useCallback(async ({ isInitial } = {}) => {
    try {
      const data = await fetchSettings();

      // Skip the state update (and re-render) entirely if polling found
      // nothing new — keeps a long-open tab from re-rendering Header/Footer
      // every 30s for no reason.
      if (!isInitial && JSON.stringify(data) === JSON.stringify(settingsRef.current)) {
        return;
      }

      settingsRef.current = data;
      setSettings(data);
      setStatus('success');

      // Scripts (analytics tags, chat widgets) are injected once on first
      // load only — re-running this on every poll would re-fire pageview
      // events and duplicate chat widgets, which live-refreshing the
      // display fields (logo/contact/footer) never should trigger.
      if (!scriptsInjected) {
        scriptsInjected = true;
        injectRawHtml(data?.header_scripts, document.head);
        injectRawHtml(data?.footer_scripts, document.body);
      }
    } catch {
      if (isInitial) setStatus('error');
      // A failed background poll just leaves the last-known-good settings
      // in place — no reason to flip an already-loaded page into an error
      // state over one missed refresh.
    }
  }, []);

  useEffect(() => {
    let cancelled = false;

    load({ isInitial: true });

    const interval = setInterval(() => {
      // Skip while the tab is backgrounded — no point refreshing content
      // nobody's looking at, and it's one less request competing for the
      // dev server's single-threaded attention.
      if (!cancelled && document.visibilityState === 'visible') {
        load({ isInitial: false });
      }
    }, POLL_INTERVAL_MS);

    return () => {
      cancelled = true;
      clearInterval(interval);
    };
  }, [load]);

  return <SettingsContext.Provider value={{ settings, status, refresh: load }}>{children}</SettingsContext.Provider>;
};

export const useSettings = () => useContext(SettingsContext);
