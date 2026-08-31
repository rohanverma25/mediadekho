import React from 'react';
import { Link } from 'react-router-dom';
import { useMagazines } from '../hooks/useMagazines';
import { Skeleton } from '../components/Skeleton';
import { useDocumentMeta } from '../hooks/useDocumentMeta';

const FALLBACK_COVER =
  'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&w=600&q=80';

export const MagazinesPage = () => {
  useDocumentMeta({
    title: 'Magazine Reader',
    description: 'Browse and read Media Dekho magazine issues online — no download required.',
  });

  const { magazines, status } = useMagazines();

  return (
    <div>
      {/* HEADER BANNER */}
      <section className="bg-gradient-to-b from-slate-100 to-slate-50 border-b border-slate-200 py-14 px-4 sm:px-6">
        <div className="max-w-4xl mx-auto text-center space-y-3">
          <span className="text-brand-red font-outfit font-bold text-xs uppercase tracking-widest block">Magazine Reader</span>
          <h1 className="font-outfit font-black text-3xl sm:text-4xl lg:text-5xl text-slate-900 tracking-tight">
            Read Our Magazines Online
          </h1>
          <p className="text-slate-600 text-sm max-w-2xl mx-auto leading-relaxed">
            Flip through every issue right in your browser — no downloads, no apps.
          </p>
        </div>
      </section>

      {/* MAGAZINE GRID */}
      <section className="py-14 px-4 sm:px-6">
        <div className="max-w-7xl mx-auto">
          {status === 'loading' && (
            <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
              {Array.from({ length: 8 }).map((_, i) => (
                <div key={i} className="rounded-2xl overflow-hidden bg-white border border-slate-200 shadow-sm">
                  <Skeleton className="w-full aspect-[3/4] rounded-none" />
                  <div className="p-4 space-y-2">
                    <Skeleton className="h-4 w-full" />
                    <Skeleton className="h-3 w-2/3" />
                  </div>
                </div>
              ))}
            </div>
          )}

          {status === 'success' && magazines.length === 0 && (
            <div className="text-center py-20 text-slate-500">
              <i className="fa-solid fa-book-open text-3xl mb-3 text-slate-300"></i>
              <p className="text-sm font-medium">No magazine issues published yet. Check back soon.</p>
            </div>
          )}

          {status === 'success' && magazines.length > 0 && (
            <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
              {magazines.map((magazine) => (
                <Link
                  key={magazine.id}
                  to={`/magazines-reader/${magazine.slug}`}
                  className="group rounded-2xl overflow-hidden bg-white border border-slate-200 shadow-sm hover:shadow-lg transition-shadow flex flex-col">
                  <div className="relative aspect-[3/4] overflow-hidden bg-slate-100">
                    <img
                      src={magazine.cover_image_url || FALLBACK_COVER}
                      alt={magazine.title}
                      loading="lazy"
                      className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                    />
                    <div className="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition flex items-center justify-center">
                      <span className="opacity-0 group-hover:opacity-100 transition bg-white text-brand-red font-outfit font-bold text-xs px-4 py-2 rounded-xl shadow-lg flex items-center gap-1.5">
                        <i className="fa-solid fa-book-open-reader text-[11px]"></i> Read Now
                      </span>
                    </div>
                  </div>
                  <div className="p-4 space-y-1">
                    {magazine.published_at && (
                      <span className="text-[10px] uppercase tracking-wider text-slate-400 font-bold block">
                        {new Date(magazine.published_at).toLocaleDateString('en-IN', { month: 'long', year: 'numeric' })}
                      </span>
                    )}
                    <h2 className="font-outfit font-bold text-sm text-slate-900 group-hover:text-brand-red transition line-clamp-2 leading-snug">
                      {magazine.title}
                    </h2>
                  </div>
                </Link>
              ))}
            </div>
          )}
        </div>
      </section>
    </div>
  );
};
