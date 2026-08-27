import React from 'react';
import { Link } from 'react-router-dom';
import { useSettings } from '../context/SettingsContext';
import { useMediaCategories } from '../hooks/useMediaCategories';

const DEFAULT_DESCRIPTION =
  "Media Dekho Pvt Ltd is India's premier Media Aggregator Platform for campaign planning and ad execution across Offline, Digital, Sports, and Corporate Gifting.";
const DEFAULT_ADDRESS = '1010-1012, 10th Floor, Venus Atlantis Corporate Park, Prahlad Nagar, Ahmedabad, Gujarat 380054';
const DEFAULT_PHONE = '+91 89800 04451';
const DEFAULT_SOCIAL = {
  facebook: 'https://www.facebook.com/MediaDekho',
  instagram: 'https://www.instagram.com/MediaDekho',
  linkedin: 'https://in.linkedin.com/company/mediadekho',
  youtube: 'https://www.youtube.com/channel/MediaDekho',
};

const SEO_KEYWORDS = [
  'Public Relations',
  'Edtech PR Agency',
  'Top PR Agency in Delhi',
  'Top PR Agency in Noida',
  'best PR agency in India',
  'Press Release agency in India',
  'Press release distribution',
  'Lifestyle PR Agency',
  'Digital PR Agency',
  'Real Estate PR Agency',
  'University PR Agency',
  'Travel PR Agency',
  'Tech PR Agency',
  'Lawyers PR Agency',
  'Healthcare PR Agency',
  'Startups PR Agency',
  'IT PR Agency',
  'AI PR Agency',
  'FMCG PR Agency',
  'Fashion Tech PR Agency',
  'Bollywood PR Agency',
  'Hospitality PR Agency',
  'Gaming PR agency',
  'Pharma and Healthcare PR Agency',
  'Fintech PR Agency',
  'Agriculture PR Agency',
  'Fashion PR Agency',
  'Doctor PR Agency',
  'School PR agency',
  'Education PR Agency',
  'top magazine advertising agency in India',
  'Reputation Management agency in Delhi/NCR',
];

// The footer sits on a dark background — a subtle white pulse reads better
// there than the shared (light-gray) Skeleton, which is tuned for white cards.
const DarkSkeleton = ({ className = '' }) => (
  <span className={`inline-block rounded bg-white/10 animate-pulse ${className}`} />
);

export const Footer = () => {
  const { settings, status: settingsStatus } = useSettings();
  const settingsLoading = settingsStatus === 'loading';
  const { categories: mediaCategories, status: mediaCategoriesStatus } = useMediaCategories();

  const description = settings?.footer_description || DEFAULT_DESCRIPTION;
  const phone = settings?.contact_phone || DEFAULT_PHONE;
  const addresses = settings?.contact_addresses?.length > 0
    ? settings.contact_addresses
    : [{ title: 'Head Office', address: settings?.contact_address || DEFAULT_ADDRESS }];
  const social = {
    facebook: settings?.social?.facebook || DEFAULT_SOCIAL.facebook,
    instagram: settings?.social?.instagram || DEFAULT_SOCIAL.instagram,
    linkedin: settings?.social?.linkedin || DEFAULT_SOCIAL.linkedin,
    youtube: settings?.social?.youtube || DEFAULT_SOCIAL.youtube,
  };

  return (
    <footer className="bg-slate-900 border-t border-slate-800 pt-16 pb-28 md:pb-12 text-slate-400 text-sm">
      <div className="max-w-7xl mx-auto px-4 sm:px-6">
        
        <div className="grid grid-cols-1 md:grid-cols-5 gap-8 mb-12">
          
          {/* Brand Info */}
          <div className="md:col-span-2 space-y-4">
            <Link to="/" className="flex items-center gap-3">
              {settings?.logo_url ? (
                <img src={settings.logo_url} alt="Media Dekho" className="h-9 w-auto max-w-[140px] object-contain" />
              ) : (
                <DarkSkeleton className="h-9 w-32 rounded-xl" />
              )}
            </Link>
            {settingsLoading ? (
              <div className="space-y-1.5 max-w-sm">
                <DarkSkeleton className="h-3 w-full" />
                <DarkSkeleton className="h-3 w-4/5" />
              </div>
            ) : (
              <p className="text-xs text-slate-400 max-w-sm leading-relaxed">
                {description}
              </p>
            )}
            {settingsLoading ? (
              <div className="flex items-center gap-3">
                {Array.from({ length: 4 }).map((_, i) => (
                  <DarkSkeleton key={i} className="w-6 h-6 rounded-full" />
                ))}
              </div>
            ) : (
              <div className="flex items-center gap-3 text-lg text-slate-400">
                {social.facebook && <a href={social.facebook} target="_blank" rel="noreferrer" className="hover:text-brand-red transition"><i className="fa-brands fa-facebook"></i></a>}
                {social.instagram && <a href={social.instagram} target="_blank" rel="noreferrer" className="hover:text-brand-red transition"><i className="fa-brands fa-instagram"></i></a>}
                {social.linkedin && <a href={social.linkedin} target="_blank" rel="noreferrer" className="hover:text-brand-red transition"><i className="fa-brands fa-linkedin"></i></a>}
                {social.youtube && <a href={social.youtube} target="_blank" rel="noreferrer" className="hover:text-brand-red transition"><i className="fa-brands fa-youtube"></i></a>}
              </div>
            )}
          </div>

          {/* Quick Links */}
          <div>
            <h4 className="font-outfit font-bold text-white text-sm mb-4">Media Verticals</h4>
            <ul className="space-y-2 text-xs">
              {mediaCategoriesStatus === 'loading' &&
                Array.from({ length: 6 }).map((_, i) => (
                  <li key={i}><DarkSkeleton className="h-3 w-32" /></li>
                ))}

              {mediaCategoriesStatus === 'success' && mediaCategories.length === 0 && (
                <li className="text-slate-500">No categories yet</li>
              )}

              {mediaCategoriesStatus === 'success' &&
                mediaCategories.map((category) => (
                  <li key={category.id}>
                    <Link to={`/category/${category.slug}`} className="hover:text-white transition">
                      {category.name}
                    </Link>
                  </li>
                ))}
            </ul>
          </div>

          {/* Corporate */}
          <div>
            <h4 className="font-outfit font-bold text-white text-sm mb-4">Company</h4>
            <ul className="space-y-2 text-xs">
              <li><Link to="/about" className="hover:text-white transition">About Us</Link></li>
              <li><Link to="/blogs" className="hover:text-white transition">Blog</Link></li>
              <li><Link to="/news" className="hover:text-white transition">In The News</Link></li>
              <li><Link to="/faq" className="hover:text-white transition">FAQs</Link></li>
              <li><Link to="/awards" className="hover:text-white transition">Awards</Link></li>
              <li><Link to="/clients" className="hover:text-white transition">Clients</Link></li>
              <li><Link to="/careers" className="hover:text-white transition">Careers</Link></li>
              <li><Link to="/contact" className="hover:text-white transition">Contact Us</Link></li>
              <li><Link to="/privacy-policy" className="hover:text-white transition">Privacy Policy</Link></li>
              <li><Link to="/terms-of-service" className="hover:text-white transition">Terms of Service</Link></li>
            </ul>
          </div>

          {/* Contact HQ */}
          <div>
            <h4 className="font-outfit font-bold text-white text-sm mb-4">Headquarters</h4>
            {settingsLoading ? (
              <div className="space-y-1.5 mb-4">
                <DarkSkeleton className="h-3 w-full" />
                <DarkSkeleton className="h-3 w-3/4" />
              </div>
            ) : (
              <div className="space-y-3 mb-4">
                {addresses.map((item, i) => (
                  <div key={i}>
                    <span className="text-xs text-slate-300 font-semibold block mb-0.5">{item.title}</span>
                    <p className="text-xs leading-relaxed text-slate-400">{item.address}</p>
                  </div>
                ))}
              </div>
            )}
            <p className="text-xs text-slate-300 font-semibold mb-1">Inquiry Helpline:</p>
            {settingsLoading ? (
              <DarkSkeleton className="h-4 w-28" />
            ) : (
              <a href={`tel:${phone.replace(/\s+/g, '')}`} className="text-xs text-brand-red font-bold block">{phone}</a>
            )}
          </div>

        </div>

        {/* SEO Keyword Links */}
        <div className="pt-8 pb-2 border-t border-slate-800">
          <h4 className="font-outfit font-bold text-white text-xs mb-3">Popular Searches</h4>
          <div className="flex flex-wrap gap-x-2 gap-y-1.5 text-[11px] leading-relaxed">
            {SEO_KEYWORDS.map((keyword, i) => (
              <React.Fragment key={keyword}>
                <span className="text-slate-500 whitespace-nowrap">{keyword}</span>
                {i < SEO_KEYWORDS.length - 1 && <span className="text-slate-700">|</span>}
              </React.Fragment>
            ))}
          </div>
        </div>

        <div className="pt-8 border-t border-slate-800 text-center text-xs text-slate-500 flex flex-col sm:flex-row justify-between items-center gap-4">
          <p>© 2026 Media Dekho Pvt Ltd. All Rights Reserved.</p>
          <p>
            Built by{' '}
            <a href="https://www.codebasecoders.com" target="_blank" rel="noreferrer" className="text-slate-400 hover:text-brand-red transition font-semibold">
              CodeBase Coders
            </a>
          </p>
        </div>

      </div>
    </footer>
  );
};
