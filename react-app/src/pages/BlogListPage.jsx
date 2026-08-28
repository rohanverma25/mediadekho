import React from 'react';
import { Link } from 'react-router-dom';
import { useBlogs } from '../hooks/useBlogs';
import { Skeleton } from '../components/Skeleton';
import { useDocumentMeta } from '../hooks/useDocumentMeta';

const FALLBACK_IMAGE =
  'https://images.unsplash.com/photo-1499750310107-5fef28a66643?auto=format&fit=crop&w=800&q=80';

export const BlogListPage = () => {
  useDocumentMeta({
    title: 'Blog',
    description: 'Practical advice on media planning, advertising trends, and getting the most out of your campaign budget.',
  });

  const { blogs, status } = useBlogs();

  return (
    <div>
      {/* HEADER BANNER */}
      <section className="bg-gradient-to-b from-slate-100 to-slate-50 border-b border-slate-200 py-14 px-4 sm:px-6">
        <div className="max-w-4xl mx-auto text-center space-y-3">
          <span className="text-brand-red font-outfit font-bold text-xs uppercase tracking-widest block">Media Dekho Blog</span>
          <h1 className="font-outfit font-black text-3xl sm:text-4xl lg:text-5xl text-slate-900 tracking-tight">
            Insights, Guides & Industry News
          </h1>
          <p className="text-slate-600 text-sm max-w-2xl mx-auto leading-relaxed">
            Practical advice on media planning, advertising trends, and getting the most out of your campaign budget.
          </p>
        </div>
      </section>

      {/* BLOG GRID */}
      <section className="py-14 px-4 sm:px-6">
        <div className="max-w-7xl mx-auto">
          {status === 'loading' && (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {Array.from({ length: 6 }).map((_, i) => (
                <div key={i} className="rounded-2xl overflow-hidden bg-white border border-slate-200 shadow-sm">
                  <Skeleton className="w-full h-48 rounded-none" />
                  <div className="p-5 space-y-3">
                    <Skeleton className="h-3 w-24" />
                    <Skeleton className="h-5 w-full" />
                    <Skeleton className="h-3 w-full" />
                    <Skeleton className="h-3 w-2/3" />
                  </div>
                </div>
              ))}
            </div>
          )}

          {status === 'success' && blogs.length === 0 && (
            <div className="text-center py-20 text-slate-500">
              <i className="fa-solid fa-newspaper text-3xl mb-3 text-slate-300"></i>
              <p className="text-sm font-medium">No blog posts published yet. Check back soon.</p>
            </div>
          )}

          {status === 'success' && blogs.length > 0 && (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {blogs.map((blog) => (
                <Link
                  key={blog.id}
                  to={`/blog?slug=${blog.slug}`}
                  className="group rounded-2xl overflow-hidden bg-white border border-slate-200 shadow-sm hover:shadow-lg transition-shadow flex flex-col">
                  <div className="h-48 overflow-hidden">
                    <img
                      src={blog.featured_image_url || FALLBACK_IMAGE}
                      alt={blog.title}
                      className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                    />
                  </div>
                  <div className="p-5 space-y-2.5 flex-1 flex flex-col">
                    {blog.published_at && (
                      <span className="text-[10px] uppercase tracking-wider text-slate-400 font-bold">
                        {new Date(blog.published_at).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' })}
                        {blog.author_name && ` · ${blog.author_name}`}
                      </span>
                    )}
                    <h2 className="font-outfit font-bold text-lg text-slate-900 group-hover:text-brand-red transition line-clamp-2 leading-snug">
                      {blog.title}
                    </h2>
                    {blog.excerpt && (
                      <p className="text-xs text-slate-500 leading-relaxed line-clamp-3">{blog.excerpt}</p>
                    )}
                    <span className="text-xs font-bold text-brand-red mt-auto pt-2 flex items-center gap-1.5">
                      Read More <i className="fa-solid fa-arrow-right text-[10px]"></i>
                    </span>
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
