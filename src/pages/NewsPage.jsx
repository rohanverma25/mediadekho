import React from 'react';
import { useNews } from '../hooks/useNews';
import { Skeleton } from '../components/Skeleton';
import { useDocumentMeta } from '../hooks/useDocumentMeta';
import { usePageMeta } from '../hooks/usePageMeta';

export const NewsPage = () => {
  const { meta } = usePageMeta('news');
  useDocumentMeta({
    title: meta?.title || 'Media Dekho In The News',
    description: meta?.description || 'Coverage and mentions from publications across the industry.',
    image: meta?.og_image_url,
  });

  const { news, status } = useNews();

  return (
    <div>
      {/* HEADER BANNER */}
      <section className="bg-gradient-to-b from-slate-100 to-slate-50 border-b border-slate-200 py-14 px-4 sm:px-6">
        <div className="max-w-4xl mx-auto text-center space-y-3">
          <span className="text-brand-red font-outfit font-bold text-xs uppercase tracking-widest block">In The Press</span>
          <h1 className="font-outfit font-black text-3xl sm:text-4xl lg:text-5xl text-slate-900 tracking-tight">
            Media Dekho In The News
          </h1>
          <p className="text-slate-600 text-sm max-w-2xl mx-auto leading-relaxed">
            Coverage and mentions from publications across the industry. Click any article to read the full story.
          </p>
        </div>
      </section>

      {/* NEWS GRID */}
      <section className="py-14 px-4 sm:px-6">
        <div className="max-w-7xl mx-auto">
          {status === 'loading' && (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {Array.from({ length: 6 }).map((_, i) => (
                <div key={i} className="rounded-2xl overflow-hidden bg-white border border-slate-200 shadow-sm">
                  <Skeleton className="w-full h-48 rounded-none" />
                  <div className="p-5 space-y-3">
                    <Skeleton className="h-4 w-full" />
                    <Skeleton className="h-4 w-2/3" />
                  </div>
                </div>
              ))}
            </div>
          )}

          {status === 'success' && news.length === 0 && (
            <div className="text-center py-20 text-slate-500">
              <i className="fa-solid fa-newspaper text-3xl mb-3 text-slate-300"></i>
              <p className="text-sm font-medium">No press coverage published yet. Check back soon.</p>
            </div>
          )}

          {status === 'success' && news.length > 0 && (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {news.map((item) => (
                <a
                  key={item.id}
                  href={item.link}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="group rounded-2xl overflow-hidden bg-white border border-slate-200 shadow-sm hover:shadow-lg transition-shadow flex flex-col">
                  <div className="h-48 overflow-hidden bg-slate-100">
                    <img
                      src={item.image_url}
                      alt={item.title}
                      className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                    />
                  </div>
                  <div className="p-5 flex-1 flex flex-col">
                    <h2 className="font-outfit font-bold text-base text-slate-900 group-hover:text-brand-red transition line-clamp-2 leading-snug">
                      {item.title}
                    </h2>
                    <span className="text-xs font-bold text-brand-red mt-auto pt-3 flex items-center gap-1.5">
                      Read Full Story <i className="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                    </span>
                  </div>
                </a>
              ))}
            </div>
          )}
        </div>
      </section>
    </div>
  );
};
