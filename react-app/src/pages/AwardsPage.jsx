import React, { useMemo, useState } from 'react';
import { useAwards } from '../hooks/useAwards';
import { Skeleton } from '../components/Skeleton';
import { AwardNominationModal } from '../components/AwardNominationModal';

const FALLBACK_IMAGE =
  'https://images.unsplash.com/photo-1567427017947-545c5f8d16ad?auto=format&fit=crop&w=800&q=80';

const formatDate = (dateStr) => {
  if (!dateStr) return null;
  return new Date(`${dateStr}T00:00:00`).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
};

export const AwardsPage = () => {
  const { awards, status } = useAwards();
  const [nominationAward, setNominationAward] = useState(null);

  const upcomingAwards = useMemo(() => awards.filter((a) => a.type === 'upcoming'), [awards]);
  const pastAwards = useMemo(() => awards.filter((a) => a.type === 'past'), [awards]);

  return (
    <div>
      {/* HEADER BANNER */}
      <section className="bg-gradient-to-b from-slate-100 to-slate-50 border-b border-slate-200 py-14 px-4 sm:px-6">
        <div className="max-w-3xl mx-auto text-center space-y-3">
          <span className="text-brand-red font-outfit font-bold text-xs uppercase tracking-widest block">Recognition</span>
          <h1 className="font-outfit font-black text-3xl sm:text-4xl lg:text-5xl text-slate-900 tracking-tight">
            Awards
          </h1>
          <p className="text-slate-600 text-sm max-w-xl mx-auto leading-relaxed">
            Explore upcoming award opportunities you can nominate a campaign for, and see the recognitions Media Dekho has been associated with.
          </p>
        </div>
      </section>

      {/* UPCOMING AWARDS */}
      <section className="py-14 px-4 sm:px-6">
        <div className="max-w-7xl mx-auto space-y-8">
          <div>
            <span className="text-brand-red font-outfit font-bold text-xs uppercase tracking-widest block mb-2">Open For Entries</span>
            <h2 className="font-outfit font-extrabold text-2xl sm:text-3xl text-slate-900">Upcoming Awards</h2>
          </div>

          {status === 'loading' && (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {Array.from({ length: 3 }).map((_, i) => (
                <div key={i} className="rounded-2xl overflow-hidden bg-white border border-slate-200 shadow-sm">
                  <Skeleton className="w-full h-44 rounded-none" />
                  <div className="p-5 space-y-3">
                    <Skeleton className="h-3 w-24" />
                    <Skeleton className="h-5 w-full" />
                    <Skeleton className="h-8 w-32 rounded-xl" />
                  </div>
                </div>
              ))}
            </div>
          )}

          {status === 'success' && upcomingAwards.length === 0 && (
            <div className="text-center py-12 text-slate-500">
              <i className="fa-solid fa-trophy text-3xl mb-3 text-slate-300"></i>
              <p className="text-sm font-medium">No upcoming awards open for nomination right now. Check back soon.</p>
            </div>
          )}

          {status === 'success' && upcomingAwards.length > 0 && (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {upcomingAwards.map((award) => (
                <div key={award.id} className="rounded-2xl overflow-hidden bg-white border border-slate-200 shadow-sm hover:shadow-lg transition-shadow flex flex-col">
                  <div className="h-44 overflow-hidden">
                    <img src={award.image_url || FALLBACK_IMAGE} alt={award.title} className="w-full h-full object-cover" />
                  </div>
                  <div className="p-5 space-y-2.5 flex-1 flex flex-col">
                    <div className="flex items-center gap-2 flex-wrap">
                      {award.event_date && (
                        <span className="text-[10px] uppercase tracking-wider text-brand-red font-bold bg-red-50 border border-red-100 px-2 py-0.5 rounded-full">
                          {formatDate(award.event_date)}
                        </span>
                      )}
                      {award.organization && (
                        <span className="text-[10px] uppercase tracking-wider text-slate-400 font-bold">{award.organization}</span>
                      )}
                    </div>
                    <h3 className="font-outfit font-bold text-lg text-slate-900 leading-snug">{award.title}</h3>
                    {award.description && (
                      <div
                        className="text-xs text-slate-500 leading-relaxed line-clamp-3 [&_p]:mb-1 [&_p:last-child]:mb-0"
                        dangerouslySetInnerHTML={{ __html: award.description }}
                      />
                    )}
                    <button
                      onClick={() => setNominationAward(award)}
                      className="mt-auto pt-2 bg-brand-red hover:bg-brand-red-dark text-white font-outfit font-bold text-xs px-4 py-2.5 rounded-xl shadow-lg shadow-brand-red/25 transition flex items-center justify-center gap-2 cursor-pointer">
                      <i className="fa-solid fa-trophy text-[10px]"></i>
                      <span>Nominate Now</span>
                    </button>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </section>

      {/* PAST ASSOCIATION AWARDS */}
      {(status === 'loading' || (status === 'success' && pastAwards.length > 0)) && (
        <section className="py-14 px-4 sm:px-6 bg-slate-100 border-t border-slate-200">
          <div className="max-w-7xl mx-auto space-y-8">
            <div>
              <span className="text-brand-red font-outfit font-bold text-xs uppercase tracking-widest block mb-2">Our Track Record</span>
              <h2 className="font-outfit font-extrabold text-2xl sm:text-3xl text-slate-900">Past Association Awards</h2>
            </div>

            {status === 'loading' && (
              <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                {Array.from({ length: 4 }).map((_, i) => (
                  <div key={i} className="rounded-2xl overflow-hidden bg-white border border-slate-200 shadow-sm">
                    <Skeleton className="w-full h-32 rounded-none" />
                    <div className="p-4 space-y-2">
                      <Skeleton className="h-4 w-full" />
                      <Skeleton className="h-3 w-2/3" />
                    </div>
                  </div>
                ))}
              </div>
            )}

            {status === 'success' && (
              <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                {pastAwards.map((award) => (
                  <div key={award.id} className="rounded-2xl overflow-hidden bg-white border border-slate-200 shadow-sm">
                    <div className="h-32 overflow-hidden">
                      <img src={award.image_url || FALLBACK_IMAGE} alt={award.title} className="w-full h-full object-cover" />
                    </div>
                    <div className="p-4">
                      <h3 className="font-outfit font-bold text-sm text-slate-900 leading-snug">{award.title}</h3>
                      <span className="text-[10px] text-slate-500 font-semibold block mt-1">
                        {[award.organization, award.event_date ? new Date(`${award.event_date}T00:00:00`).getFullYear() : null].filter(Boolean).join(' · ')}
                      </span>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        </section>
      )}

      {nominationAward && (
        <AwardNominationModal award={nominationAward} onClose={() => setNominationAward(null)} />
      )}
    </div>
  );
};
