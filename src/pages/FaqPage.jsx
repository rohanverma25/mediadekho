import React, { useMemo, useState } from 'react';
import { useFaqs } from '../hooks/useFaqs';
import { Skeleton } from '../components/Skeleton';
import { useDocumentMeta } from '../hooks/useDocumentMeta';
import { usePageMeta } from '../hooks/usePageMeta';

export const FaqPage = () => {
  const { meta } = usePageMeta('faq');
  useDocumentMeta({
    title: meta?.title || 'Frequently Asked Questions',
    description: meta?.description || 'Answers to common questions about media planning, pricing, and booking with Media Dekho.',
    image: meta?.og_image_url,
  });

  const { faqs, status } = useFaqs();
  const [query, setQuery] = useState('');
  const [activeFaq, setActiveFaq] = useState(0);

  const filteredFaqs = useMemo(() => {
    const q = query.trim().toLowerCase();
    if (!q) return faqs;
    return faqs.filter(
      (faq) => faq.question.toLowerCase().includes(q) || faq.answer.toLowerCase().includes(q)
    );
  }, [faqs, query]);

  return (
    <div>
      {/* HEADER BANNER */}
      <section className="bg-gradient-to-b from-slate-100 to-slate-50 border-b border-slate-200 py-14 px-4 sm:px-6">
        <div className="max-w-3xl mx-auto text-center space-y-5">
          <span className="text-brand-red font-outfit font-bold text-xs uppercase tracking-widest block">Support Center</span>
          <h1 className="font-outfit font-black text-3xl sm:text-4xl lg:text-5xl text-slate-900 tracking-tight">
            Frequently Asked Questions
          </h1>
          <p className="text-slate-600 text-sm max-w-xl mx-auto leading-relaxed">
            Answers to the most common questions about booking, pricing, and how Media Dekho works.
          </p>

          <div className="relative max-w-md mx-auto pt-2">
            <i className="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 pt-1 text-slate-400 text-xs"></i>
            <input
              type="text"
              value={query}
              onChange={(e) => setQuery(e.target.value)}
              placeholder="Search questions..."
              className="glass-input w-full rounded-2xl pl-10 pr-4 py-3 text-sm shadow-sm"
            />
          </div>
        </div>
      </section>

      {/* FAQ LIST */}
      <section className="py-14 px-4 sm:px-6">
        <div className="max-w-3xl mx-auto space-y-4">
          {status === 'loading' &&
            Array.from({ length: 6 }).map((_, i) => <Skeleton key={i} className="h-16 w-full rounded-2xl" />)}

          {status === 'success' && faqs.length === 0 && (
            <div className="text-center py-12 text-slate-500">
              <i className="fa-solid fa-circle-question text-3xl mb-3 text-slate-300"></i>
              <p className="text-sm font-medium">No FAQs published yet. Check back soon.</p>
            </div>
          )}

          {status === 'success' && faqs.length > 0 && filteredFaqs.length === 0 && (
            <div className="text-center py-12 text-slate-500">
              <i className="fa-solid fa-magnifying-glass text-3xl mb-3 text-slate-300"></i>
              <p className="text-sm font-medium">No questions match "{query}".</p>
            </div>
          )}

          {status === 'success' &&
            filteredFaqs.map((faq, index) => (
              <div key={faq.id} className={`faq-item ${activeFaq === index ? 'active' : ''}`}>
                <div onClick={() => setActiveFaq(activeFaq === index ? null : index)} className="faq-header">
                  <span>{faq.question}</span>
                  <i className="fa-solid fa-chevron-down text-xs"></i>
                </div>
                <div className="faq-content" dangerouslySetInnerHTML={{ __html: faq.answer }} />
              </div>
            ))}
        </div>
      </section>
    </div>
  );
};
