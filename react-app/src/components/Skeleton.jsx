import React from 'react';

/**
 * A pulsing placeholder block. Used everywhere a component is waiting on an
 * API response — shown instead of flashing static/mock/default data that
 * would look like real content for a moment before the real data replaces it.
 */
export const Skeleton = ({ className = '' }) => (
  <div className={`animate-pulse bg-slate-200 rounded ${className}`} />
);
