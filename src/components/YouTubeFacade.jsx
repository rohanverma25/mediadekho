import React, { useState } from 'react';

/**
 * A real YouTube iframe embed pulls in ~1MB+ of YouTube's own JS before a
 * viewer even presses play — fine for one video, expensive for a slider of
 * several. This renders only a static thumbnail + play button (a couple KB)
 * until clicked, and only then swaps in the actual iframe — the standard
 * "lite YouTube embed" pattern.
 */
export const YouTubeFacade = ({ videoId, title, thumbnailUrl, className = '' }) => {
  const [playing, setPlaying] = useState(false);

  if (playing) {
    return (
      <iframe
        className={className}
        src={`https://www.youtube-nocookie.com/embed/${videoId}?autoplay=1&rel=0`}
        title={title}
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
        allowFullScreen
        loading="lazy"
      />
    );
  }

  return (
    <button
      type="button"
      onClick={() => setPlaying(true)}
      aria-label={`Play ${title}`}
      className={`relative block group cursor-pointer overflow-hidden ${className}`}>
      <img
        src={thumbnailUrl}
        alt={title}
        loading="lazy"
        decoding="async"
        className="w-full h-full object-cover"
      />
      <div className="absolute inset-0 bg-black/25 group-hover:bg-black/40 transition flex items-center justify-center">
        <div className="w-14 h-14 rounded-full bg-brand-red/90 group-hover:bg-brand-red flex items-center justify-center text-white text-lg shadow-lg transition group-hover:scale-110">
          <i className="fa-solid fa-play ml-1"></i>
        </div>
      </div>
    </button>
  );
};
