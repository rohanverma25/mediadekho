import React, { useState } from 'react';
import { useJobs } from '../hooks/useJobs';
import { Skeleton } from '../components/Skeleton';
import { JobApplicationModal } from '../components/JobApplicationModal';

const TYPE_LABEL = {
  'full-time': 'Full-Time',
  'part-time': 'Part-Time',
  internship: 'Internship',
  contract: 'Contract',
};

export const CareerPage = () => {
  const { jobs, status } = useJobs();
  const [applyJob, setApplyJob] = useState(null);

  return (
    <div>
      {/* HEADER BANNER */}
      <section className="bg-gradient-to-b from-slate-100 to-slate-50 border-b border-slate-200 py-14 px-4 sm:px-6">
        <div className="max-w-3xl mx-auto text-center space-y-3">
          <span className="text-brand-red font-outfit font-bold text-xs uppercase tracking-widest block">Careers</span>
          <h1 className="font-outfit font-black text-3xl sm:text-4xl lg:text-5xl text-slate-900 tracking-tight">
            Join The Media Dekho Team
          </h1>
          <p className="text-slate-600 text-sm max-w-xl mx-auto leading-relaxed">
            We're building India's largest media aggregator platform. Explore our open roles below.
          </p>
        </div>
      </section>

      {/* JOB LISTINGS */}
      <section className="py-14 px-4 sm:px-6">
        <div className="max-w-4xl mx-auto space-y-4">
          {status === 'loading' &&
            Array.from({ length: 4 }).map((_, i) => (
              <div key={i} className="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-3">
                <Skeleton className="h-3 w-24" />
                <Skeleton className="h-5 w-2/3" />
                <Skeleton className="h-3 w-full" />
                <Skeleton className="h-9 w-32 rounded-xl" />
              </div>
            ))}

          {status === 'success' && jobs.length === 0 && (
            <div className="text-center py-16 text-slate-500">
              <i className="fa-solid fa-briefcase text-3xl mb-3 text-slate-300"></i>
              <p className="text-sm font-medium">No open positions right now. Check back soon.</p>
            </div>
          )}

          {status === 'success' &&
            jobs.map((job) => (
              <div key={job.id} className="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-lg transition-shadow">
                <div className="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                  <div className="min-w-0">
                    <div className="flex items-center gap-2 flex-wrap mb-2">
                      <span className="text-[10px] uppercase tracking-wider text-brand-red font-bold bg-red-50 border border-red-100 px-2 py-0.5 rounded-full">
                        {TYPE_LABEL[job.type] || job.type}
                      </span>
                      {job.department && (
                        <span className="text-[10px] uppercase tracking-wider text-slate-400 font-bold">{job.department}</span>
                      )}
                      {job.location && (
                        <span className="text-[10px] text-slate-400 font-semibold flex items-center gap-1">
                          <i className="fa-solid fa-location-dot"></i> {job.location}
                        </span>
                      )}
                    </div>
                    <h2 className="font-outfit font-bold text-lg text-slate-900">{job.title}</h2>
                    {job.description && (
                      <div
                        className="text-xs text-slate-500 leading-relaxed mt-1.5 line-clamp-2 [&_p]:mb-1 [&_p:last-child]:mb-0"
                        dangerouslySetInnerHTML={{ __html: job.description }}
                      />
                    )}
                  </div>
                  <button
                    onClick={() => setApplyJob(job)}
                    className="flex-shrink-0 bg-brand-red hover:bg-brand-red-dark text-white font-outfit font-bold text-xs px-5 py-2.5 rounded-xl shadow-lg shadow-brand-red/25 transition cursor-pointer">
                    Apply Now
                  </button>
                </div>
              </div>
            ))}
        </div>
      </section>

      {applyJob && (
        <JobApplicationModal job={applyJob} onClose={() => setApplyJob(null)} />
      )}
    </div>
  );
};
