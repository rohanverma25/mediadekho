import React, { useCallback, useEffect, useRef, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { Document, Page, pdfjs } from 'react-pdf';
import { useMagazine } from '../hooks/useMagazine';
import { Skeleton } from '../components/Skeleton';
import { useDocumentMeta } from '../hooks/useDocumentMeta';

// The standard react-pdf + Vite setup: Vite specially resolves a
// `new URL(..., import.meta.url)` expression into a hashed, bundled asset
// URL, which is how the PDF.js worker script (loaded off the main thread —
// required for react-pdf to render anything) gets found in both dev and a
// production build without needing a manually copied public/ file.
pdfjs.GlobalWorkerOptions.workerSrc = new URL(
  'pdfjs-dist/build/pdf.worker.min.mjs',
  import.meta.url,
).toString();

const MIN_SCALE = 0.6;
const MAX_SCALE = 2.2;
const SCALE_STEP = 0.2;
const SWIPE_THRESHOLD_PX = 50;
const SPREAD_BREAKPOINT_PX = 768; // Tailwind's `md` — matches when there's room for two pages side by side

/**
 * A booklet shows its front cover alone, then two-page spreads from there
 * on (2-3, 4-5, ...) — the same convention as a real printed/bound
 * magazine. `page` is the first page of whatever's currently shown.
 */
function getSpreadPages(page, numPages, pagesPerView) {
  if (pagesPerView === 1 || page <= 1) return [1];

  const left = page % 2 === 0 ? page : page - 1;
  const right = left + 1;
  return numPages && right <= numPages ? [left, right] : [left];
}

export const MagazineReaderPage = () => {
  const { slug } = useParams();
  const { magazine, status } = useMagazine(slug);

  useDocumentMeta(
    magazine
      ? { title: magazine.title, description: magazine.description || undefined, image: magazine.cover_image_url }
      : { title: 'Magazine Reader' },
  );

  const [numPages, setNumPages] = useState(null);
  const [pageNumber, setPageNumber] = useState(1);
  const [pageInput, setPageInput] = useState('1');
  const [scale, setScale] = useState(1);
  const [pdfError, setPdfError] = useState(false);
  const [isFullscreen, setIsFullscreen] = useState(false);
  const [isDesktop, setIsDesktop] = useState(() => typeof window !== 'undefined' && window.innerWidth >= SPREAD_BREAKPOINT_PX);
  const [flipDirection, setFlipDirection] = useState('next');

  const containerRef = useRef(null);
  const touchStartXRef = useRef(null);

  // Two-page spreads only make sense once there's room for them — narrow
  // viewports fall back to a single page per view, same as most digital
  // magazine readers.
  useEffect(() => {
    const handleResize = () => setIsDesktop(window.innerWidth >= SPREAD_BREAKPOINT_PX);
    window.addEventListener('resize', handleResize);
    return () => window.removeEventListener('resize', handleResize);
  }, []);

  const pagesPerView = isDesktop && numPages && numPages > 1 ? 2 : 1;
  const spreadPages = getSpreadPages(pageNumber, numPages, pagesPerView);

  useEffect(() => {
    setPageInput(String(pageNumber));
  }, [pageNumber]);

  // Reset reader state whenever a different magazine is opened (e.g.
  // navigating from one issue's reader straight to another's via a link).
  useEffect(() => {
    setNumPages(null);
    setPageNumber(1);
    setScale(1);
    setPdfError(false);
  }, [slug]);

  const changePage = useCallback((direction) => {
    setPageNumber((prev) => {
      const currentSpread = getSpreadPages(prev, numPages, pagesPerView);

      if (direction > 0) {
        const next = currentSpread[currentSpread.length - 1] + 1;
        if (numPages && next > numPages) return prev;
        setFlipDirection('next');
        return next;
      }

      const first = currentSpread[0];
      if (first <= 1) return prev;
      setFlipDirection('prev');
      // Stepping back from the [2,3] spread lands on the cover (page 1)
      // alone, not on a nonexistent [0,1] spread.
      return first === 2 ? 1 : first - 2;
    });
  }, [numPages, pagesPerView]);

  useEffect(() => {
    const handleKeyDown = (e) => {
      if (e.key === 'ArrowRight') changePage(1);
      if (e.key === 'ArrowLeft') changePage(-1);
    };
    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, [changePage]);

  useEffect(() => {
    const onFullscreenChange = () => setIsFullscreen(Boolean(document.fullscreenElement));
    document.addEventListener('fullscreenchange', onFullscreenChange);
    return () => document.removeEventListener('fullscreenchange', onFullscreenChange);
  }, []);

  const toggleFullscreen = () => {
    if (!containerRef.current) return;
    if (document.fullscreenElement) {
      document.exitFullscreen?.();
    } else {
      containerRef.current.requestFullscreen?.();
    }
  };

  const handlePageInputSubmit = (e) => {
    e.preventDefault();
    const target = parseInt(pageInput, 10);
    if (!Number.isNaN(target) && target >= 1 && (!numPages || target <= numPages)) {
      // Normalize to the spread that actually contains the requested page,
      // so jumping to e.g. page 5 opens the [4,5] spread rather than a
      // mismatched [5,6] one.
      const normalized = pagesPerView === 1 || target <= 1
        ? target
        : (target % 2 === 0 ? target : target - 1);
      setFlipDirection(normalized >= pageNumber ? 'next' : 'prev');
      setPageNumber(normalized);
    } else {
      setPageInput(String(pageNumber));
    }
  };

  const handleTouchStart = (e) => {
    touchStartXRef.current = e.touches[0]?.clientX ?? null;
  };

  const handleTouchEnd = (e) => {
    if (touchStartXRef.current === null) return;
    const delta = (e.changedTouches[0]?.clientX ?? 0) - touchStartXRef.current;
    touchStartXRef.current = null;

    if (Math.abs(delta) < SWIPE_THRESHOLD_PX) return;
    changePage(delta < 0 ? 1 : -1);
  };

  if (status === 'loading' || status === 'idle') {
    return (
      <div className="bg-slate-900 min-h-screen flex flex-col items-center justify-center gap-4 px-4">
        <Skeleton className="h-4 w-40 bg-slate-800" />
        <Skeleton className="w-[min(90vw,600px)] h-[70vh] rounded-lg bg-slate-800" />
      </div>
    );
  }

  if (status === 'error' || !magazine) {
    return (
      <div className="bg-slate-900 min-h-screen flex flex-col items-center justify-center text-center px-4">
        <i className="fa-solid fa-book-open text-3xl mb-3 text-slate-600"></i>
        <p className="text-sm text-slate-400 font-medium mb-4">This magazine couldn't be found.</p>
        <Link to="/magazines-reader" className="text-brand-red font-bold text-sm hover:underline">Back to Magazines</Link>
      </div>
    );
  }

  const atFirstPage = spreadPages[0] <= 1;
  const atLastPage = Boolean(numPages) && spreadPages[spreadPages.length - 1] >= numPages;

  return (
    <div ref={containerRef} className="bg-slate-900 min-h-screen flex flex-col">
      {/* TOP BAR */}
      <div className="flex items-center justify-between gap-3 px-4 sm:px-6 py-3 bg-slate-950 border-b border-slate-800">
        <div className="flex items-center gap-3 min-w-0">
          <Link to="/magazines-reader" aria-label="Back to Magazines" className="text-slate-400 hover:text-white transition flex-shrink-0">
            <i className="fa-solid fa-arrow-left"></i>
          </Link>
          <h1 className="text-white font-outfit font-bold text-sm truncate">{magazine.title}</h1>
        </div>

        <div className="flex items-center gap-1.5 sm:gap-2 flex-shrink-0">
          <button
            type="button"
            onClick={() => setScale((s) => Math.max(MIN_SCALE, +(s - SCALE_STEP).toFixed(2)))}
            disabled={scale <= MIN_SCALE}
            aria-label="Zoom out"
            className="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 disabled:opacity-40 disabled:cursor-not-allowed text-slate-300 flex items-center justify-center transition">
            <i className="fa-solid fa-magnifying-glass-minus text-xs"></i>
          </button>
          <span className="hidden sm:inline text-slate-400 text-[11px] font-semibold w-10 text-center tabular-nums">{Math.round(scale * 100)}%</span>
          <button
            type="button"
            onClick={() => setScale((s) => Math.min(MAX_SCALE, +(s + SCALE_STEP).toFixed(2)))}
            disabled={scale >= MAX_SCALE}
            aria-label="Zoom in"
            className="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 disabled:opacity-40 disabled:cursor-not-allowed text-slate-300 flex items-center justify-center transition">
            <i className="fa-solid fa-magnifying-glass-plus text-xs"></i>
          </button>
          <button
            type="button"
            onClick={toggleFullscreen}
            aria-label={isFullscreen ? 'Exit fullscreen' : 'Enter fullscreen'}
            className="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 flex items-center justify-center transition">
            <i className={`fa-solid ${isFullscreen ? 'fa-compress' : 'fa-expand'} text-xs`}></i>
          </button>
          <a
            href={magazine.pdf_url}
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Download PDF"
            className="w-8 h-8 rounded-lg bg-slate-800 hover:bg-brand-red text-slate-300 hover:text-white flex items-center justify-center transition">
            <i className="fa-solid fa-download text-xs"></i>
          </a>
        </div>
      </div>

      {/* READER AREA — `perspective` here is what makes the child's rotateY
          flip animation read as real 3D depth rather than a flat squash. */}
      <div
        className="flex-1 relative flex items-center justify-center overflow-auto py-6 px-3 sm:px-16"
        style={{ perspective: '1600px' }}
        onTouchStart={handleTouchStart}
        onTouchEnd={handleTouchEnd}>
        <button
          type="button"
          onClick={() => changePage(-1)}
          disabled={atFirstPage}
          aria-label="Previous page"
          className="hidden sm:flex absolute left-4 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/10 hover:bg-white/20 disabled:opacity-0 text-white items-center justify-center transition z-10">
          <i className="fa-solid fa-chevron-left"></i>
        </button>

        {pdfError ? (
          <div className="text-center text-slate-400 max-w-sm">
            <i className="fa-solid fa-triangle-exclamation text-2xl mb-3 text-amber-500"></i>
            <p className="text-sm font-medium mb-3">This PDF couldn't be displayed here.</p>
            <a href={magazine.pdf_url} target="_blank" rel="noopener noreferrer" className="text-brand-red font-bold text-sm hover:underline">
              Open the PDF directly instead
            </a>
          </div>
        ) : (
          <Document
            file={magazine.pdf_stream_url}
            onLoadSuccess={({ numPages: total }) => setNumPages(total)}
            onLoadError={() => setPdfError(true)}
            loading={<Skeleton className="w-[min(90vw,600px)] h-[80vh] rounded-lg" />}
            className="flex items-center justify-center">
            <div
              key={spreadPages.join('-')}
              className={`${flipDirection === 'next' ? 'magazine-flip-next' : 'magazine-flip-prev'} relative flex shadow-2xl rounded-sm overflow-hidden bg-white`}>
              {spreadPages.map((page) => (
                <Page
                  key={page}
                  pageNumber={page}
                  scale={scale}
                  renderTextLayer={false}
                  renderAnnotationLayer={false}
                  loading={<Skeleton className="w-[min(90vw,600px)] h-[80vh] rounded-lg" />}
                />
              ))}
              {/* Book-spine shading down the center crease — only meaningful
                  once there are two facing pages to crease between. */}
              {spreadPages.length === 2 && (
                <div
                  aria-hidden="true"
                  className="pointer-events-none absolute inset-y-0 left-1/2 w-6 -translate-x-1/2"
                  style={{ background: 'linear-gradient(to right, rgba(0,0,0,0.18), rgba(0,0,0,0) 20%, rgba(0,0,0,0) 80%, rgba(0,0,0,0.18))' }}
                />
              )}
            </div>
          </Document>
        )}

        <button
          type="button"
          onClick={() => changePage(1)}
          disabled={atLastPage}
          aria-label="Next page"
          className="hidden sm:flex absolute right-4 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/10 hover:bg-white/20 disabled:opacity-0 text-white items-center justify-center transition z-10">
          <i className="fa-solid fa-chevron-right"></i>
        </button>
      </div>

      {/* BOTTOM BAR */}
      <div className="flex items-center justify-center gap-3 px-4 py-3 bg-slate-950 border-t border-slate-800">
        <button
          type="button"
          onClick={() => changePage(-1)}
          disabled={atFirstPage}
          className="sm:hidden text-xs font-bold text-slate-300 disabled:opacity-30 flex items-center gap-1.5">
          <i className="fa-solid fa-chevron-left text-[10px]"></i> Prev
        </button>

        <form onSubmit={handlePageInputSubmit} className="flex items-center gap-2 text-xs text-slate-300 font-semibold">
          <input
            type="text"
            inputMode="numeric"
            value={pageInput}
            onChange={(e) => setPageInput(e.target.value)}
            aria-label="Page number"
            className="w-12 text-center bg-slate-800 border border-slate-700 rounded-lg py-1.5 text-white focus:outline-none focus:border-brand-red" />
          <span className="text-slate-500">
            {spreadPages.length === 2 ? `–${spreadPages[1]} ` : ''}/ {numPages ?? '—'}
          </span>
        </form>

        <button
          type="button"
          onClick={() => changePage(1)}
          disabled={atLastPage}
          className="sm:hidden text-xs font-bold text-slate-300 disabled:opacity-30 flex items-center gap-1.5">
          Next <i className="fa-solid fa-chevron-right text-[10px]"></i>
        </button>
      </div>
    </div>
  );
};
