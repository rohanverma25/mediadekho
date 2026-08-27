import React from 'react';
import { useClientLogos } from '../hooks/useClientLogos';
import { Skeleton } from '../components/Skeleton';

export const ClientsPage = () => {
  const { logos, status } = useClientLogos();

  return (
    <div>
      {/* HEADER BANNER */}
      <section className="bg-gradient-to-b from-slate-100 to-slate-50 border-b border-slate-200 py-14 px-4 sm:px-6">
        <div className="max-w-4xl mx-auto text-center space-y-3">
          <span className="text-brand-red font-outfit font-bold text-xs uppercase tracking-widest block">Trusted By</span>
          <h1 className="font-outfit font-black text-3xl sm:text-4xl lg:text-5xl text-slate-900 tracking-tight">
            Our Clients
          </h1>
          <p className="text-slate-600 text-sm max-w-2xl mx-auto leading-relaxed">
            5,000+ global brands and fast-growing startups plan and execute their campaigns with Media Dekho.
          </p>
        </div>
      </section>

      {/* CLIENT LOGO GRID */}
      <section className="py-14 px-4 sm:px-6">
        <div className="max-w-7xl mx-auto">
          {status === 'loading' && (
            <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
              {Array.from({ length: 10 }).map((_, i) => (
                <div key={i} className="rounded-2xl bg-white border border-slate-200 shadow-sm p-6 flex items-center justify-center h-28">
                  <Skeleton className="h-10 w-full" />
                </div>
              ))}
            </div>
          )}

          {status === 'success' && logos.length === 0 && (
            <div className="text-center py-20 text-slate-500">
              <i className="fa-solid fa-handshake text-3xl mb-3 text-slate-300"></i>
              <p className="text-sm font-medium">No clients published yet. Check back soon.</p>
            </div>
          )}

          {status === 'success' && logos.length > 0 && (
            <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
              {logos.map((logo) => {
                const content = (
                  <img
                    src={logo.logo_url}
                    alt={logo.name}
                    title={logo.name}
                    className="max-h-12 max-w-full object-contain"
                  />
                );

                return logo.website_url ? (
                  <a
                    key={logo.id}
                    href={logo.website_url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="group rounded-2xl bg-white border border-slate-200 shadow-sm hover:shadow-lg transition-shadow p-6 flex items-center justify-center h-28">
                    {content}
                  </a>
                ) : (
                  <div
                    key={logo.id}
                    className="group rounded-2xl bg-white border border-slate-200 shadow-sm p-6 flex items-center justify-center h-28">
                    {content}
                  </div>
                );
              })}
            </div>
          )}
        </div>
      </section>
    </div>
  );
};
