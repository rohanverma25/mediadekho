import React, { useState } from 'react';

/**
 * Mirrors YouTubeFacade's lazy-load-on-click UX, but for a directly
 * uploaded video file — a native <video> element instead of an iframe.
 * Falls back to a generic play icon when no poster/thumbnail was uploaded.
 */
export const UploadedVideoFacade = ({ videoUrl, title, thumbnailUrl, className = '' }) => {
  const [playing, setPlaying] = useState(false);

  if (playing) {
    return (
      <video
        className={className}
        src={videoUrl}
        title={title}
        controls
        autoPlay
        playsInline
      />
    );
  }

  return (
    <button
      type="button"
      onClick={() => setPlaying(true)}
      aria-label={`Play ${title}`}
      className={`relative block group cursor-pointer overflow-hidden bg-slate-900 ${className}`}>
      {thumbnailUrl ? (
        <img
          src={thumbnailUrl}
          alt={title}
          loading="lazy"
          decoding="async"
          className="w-full h-full object-cover"
        />
      ) : (
        <div className="w-full h-full flex items-center justify-center text-slate-600">
          <i className="fa-solid fa-film text-3xl"></i>
        </div>
      )}
      <div className="absolute inset-0 bg-black/25 group-hover:bg-black/40 transition flex items-center justify-center">
        <div className="w-14 h-14 rounded-full bg-brand-red/90 group-hover:bg-brand-red flex items-center justify-center text-white text-lg shadow-lg transition group-hover:scale-110">
          <i className="fa-solid fa-play ml-1"></i>
        </div>
      </div>
    </button>
  );
};
