import React from 'react';
import { useCart } from '../context/CartContext';
import { useMyEnquiries } from '../hooks/useMyEnquiries';
import { AccountLayout } from '../components/AccountLayout';
import { Skeleton } from '../components/Skeleton';
import { useDocumentMeta } from '../hooks/useDocumentMeta';

const STATUS_BADGE = {
  new: 'bg-amber-100 text-amber-700',
  contacted: 'bg-blue-100 text-blue-700',
  closed: 'bg-slate-200 text-slate-600',
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
};

export const EnquiriesPage = () => {
  useDocumentMeta({ title: 'My Enquiries', noindex: true });

  const { enquiries, status } = useMyEnquiries();
  const { setIsInquiryOpen } = useCart();

  return (
    <AccountLayout active="enquiries">

      <div className="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex justify-between items-center">
        <h2 className="font-outfit font-bold text-base text-slate-900">
          My Enquiries (<span className="text-brand-red font-black">{enquiries.length}</span>)
        </h2>
        <button
          onClick={() => setIsInquiryOpen(true)}
          className="bg-brand-red hover:bg-brand-red-dark text-white font-outfit font-bold text-xs px-4 py-2.5 rounded-xl transition flex items-center gap-1.5 cursor-pointer">
          <i className="fa-solid fa-plus text-xs"></i>
          <span>New Enquiry</span>
        </button>
      </div>

      {status === 'loading' && (
        <div className="space-y-4">
          {Array.from({ length: 3 }).map((_, i) => (
            <Skeleton key={i} className="h-24 w-full rounded-3xl" />
          ))}
        </div>
      )}

      {status === 'success' && enquiries.length === 0 && (
        <div className="bg-white p-12 rounded-3xl border border-slate-200 shadow-sm text-center space-y-4">
          <div className="w-16 h-16 rounded-full bg-red-50 border border-red-100 flex items-center justify-center text-brand-red text-2xl mx-auto">
            <i className="fa-solid fa-comments"></i>
          </div>
          <h3 className="font-outfit font-extrabold text-2xl text-slate-900">No enquiries yet</h3>
          <p className="text-xs text-slate-500 max-w-md mx-auto leading-relaxed">
            Have a question or need a custom proposal? Send us an enquiry and our team will get back to you.
          </p>
          <button
            onClick={() => setIsInquiryOpen(true)}
            className="inline-flex items-center gap-2 bg-brand-red hover:bg-brand-red-dark text-white font-outfit font-bold text-xs px-6 py-3 rounded-xl shadow-lg shadow-brand-red/25 transition cursor-pointer">
            <span>Send an Enquiry</span>
            <i className="fa-solid fa-paper-plane text-xs"></i>
          </button>
        </div>
      )}

      {status === 'success' && enquiries.length > 0 && (
        <div className="space-y-4">
          {enquiries.map((enquiry) => (
            <div key={enquiry.id} className="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm space-y-2">
              <div className="flex items-center justify-between gap-2 flex-wrap">
                <h4 className="font-outfit font-bold text-sm text-slate-900">{enquiry.subject || 'General Enquiry'}</h4>
                <span className={`text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase ${STATUS_BADGE[enquiry.status] || 'bg-slate-200 text-slate-600'}`}>
                  {enquiry.status}
                </span>
              </div>
              <p className="text-xs text-slate-600 whitespace-pre-wrap">{enquiry.description}</p>
              <span className="text-[10px] text-slate-400 font-semibold uppercase block">
                Submitted {formatDate(enquiry.created_at)}
              </span>
            </div>
          ))}
        </div>
      )}

    </AccountLayout>
  );
};
