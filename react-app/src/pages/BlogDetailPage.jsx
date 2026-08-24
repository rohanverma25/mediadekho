import React from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { useBlog } from '../hooks/useBlog';
import { useBlogs } from '../hooks/useBlogs';
import { Skeleton } from '../components/Skeleton';

const FALLBACK_IMAGE =
  'https://images.unsplash.com/photo-1499750310107-5fef28a66643?auto=format&fit=crop&w=1200&q=80';

export const BlogDetailPage = () => {
  const [searchParams] = useSearchParams();
  const slug = searchParams.get('slug');

  const { blog, status } = useBlog(slug);
  const { blogs: otherBlogs } = useBlogs({ per_page: 4 });
  const relatedBlogs = otherBlogs.filter((b) => b.slug !== slug).slice(0, 3);

  if (!slug) {
    return (
      <div className="max-w-3xl mx-auto px-4 sm:px-6 py-24 text-center">
        <i className="fa-solid fa-newspaper text-3xl mb-3 text-slate-300"></i>
        <p className="text-sm text-slate-500 font-medium mb-4">No blog post specified.</p>
        <Link to="/blogs" className="text-brand-red font-bold text-sm hover:underline">Back to Blog</Link>
      </div>
    );
  }

  if (status === 'loading') {
    return (
      <div className="max-w-3xl mx-auto px-4 sm:px-6 py-14 space-y-6">
        <Skeleton className="h-4 w-32" />
        <Skeleton className="h-10 w-full" />
        <Skeleton className="h-10 w-2/3" />
        <Skeleton className="w-full h-80 rounded-3xl" />
        <div className="space-y-3">
          <Skeleton className="h-3 w-full" />
          <Skeleton className="h-3 w-full" />
          <Skeleton className="h-3 w-4/5" />
        </div>
      </div>
    );
  }

  if (status === 'error' || !blog) {
    return (
      <div className="max-w-3xl mx-auto px-4 sm:px-6 py-24 text-center">
        <i className="fa-solid fa-circle-exclamation text-3xl mb-3 text-slate-300"></i>
        <p className="text-sm text-slate-500 font-medium mb-4">This blog post couldn't be found.</p>
        <Link to="/blogs" className="text-brand-red font-bold text-sm hover:underline">Back to Blog</Link>
      </div>
    );
  }

  return (
    <div>
      <article className="max-w-3xl mx-auto px-4 sm:px-6 py-14">
        <nav className="flex items-center gap-2 text-xs text-slate-500 mb-6 font-medium">
          <Link to="/" className="hover:text-brand-red">Home</Link>
          <i className="fa-solid fa-chevron-right text-[9px]"></i>
          <Link to="/blogs" className="hover:text-brand-red">Blog</Link>
          <i className="fa-solid fa-chevron-right text-[9px]"></i>
          <span className="text-slate-900 font-bold truncate">{blog.title}</span>
        </nav>

        {blog.published_at && (
          <span className="text-xs uppercase tracking-wider text-slate-400 font-bold block mb-2">
            {new Date(blog.published_at).toLocaleDateString('en-IN', { day: 'numeric', month: 'long', year: 'numeric' })}
            {blog.author_name && ` · By ${blog.author_name}`}
          </span>
        )}

        <h1 className="font-outfit font-black text-3xl sm:text-4xl text-slate-900 tracking-tight leading-tight mb-6">
          {blog.title}
        </h1>

        <img
          src={blog.featured_image_url || FALLBACK_IMAGE}
          alt={blog.title}
          className="w-full h-72 sm:h-96 object-cover rounded-3xl border border-slate-200 shadow-sm mb-8"
        />

        <div
          className="blog-content text-sm sm:text-base"
          dangerouslySetInnerHTML={{ __html: blog.content }}
        />
      </article>

      {relatedBlogs.length > 0 && (
        <section className="bg-slate-50 border-t border-slate-200 py-14 px-4 sm:px-6">
          <div className="max-w-5xl mx-auto space-y-8">
            <h2 className="font-outfit font-extrabold text-2xl text-slate-900">More From The Blog</h2>
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-6">
              {relatedBlogs.map((b) => (
                <Link
                  key={b.id}
                  to={`/blog?slug=${b.slug}`}
                  className="group rounded-2xl overflow-hidden bg-white border border-slate-200 shadow-sm hover:shadow-lg transition-shadow">
                  <div className="h-32 overflow-hidden">
                    <img
                      src={b.featured_image_url || FALLBACK_IMAGE}
                      alt={b.title}
                      className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                    />
                  </div>
                  <div className="p-4">
                    <h3 className="font-outfit font-bold text-sm text-slate-900 group-hover:text-brand-red transition line-clamp-2">
                      {b.title}
                    </h3>
                  </div>
                </Link>
              ))}
            </div>
          </div>
        </section>
      )}
    </div>
  );
};
