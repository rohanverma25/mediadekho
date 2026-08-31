import React, { Suspense, lazy } from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import { CartProvider } from './context/CartContext';
import { SettingsProvider } from './context/SettingsContext';
import { Header } from './components/Header';
import { CategorySlider } from './components/CategorySlider';
import { Footer } from './components/Footer';
import { InquiryModal } from './components/InquiryModal';
import { SearchModal } from './components/SearchModal';
import { CartDrawer } from './components/CartDrawer';
import { Toast } from './components/Toast';
import { ProtectedRoute } from './components/ProtectedRoute';
import { ScrollToTop } from './components/ScrollToTop';
import { AnnouncementBar } from './components/AnnouncementBar';
import { MobileBottomNav } from './components/MobileBottomNav';

import { HomePage } from './pages/HomePage';
import { CategoryPage } from './pages/CategoryPage';
import { ListingDetailPage } from './pages/ListingDetailPage';
import { BlogListPage } from './pages/BlogListPage';
import { BlogDetailPage } from './pages/BlogDetailPage';
import { MagazinesPage } from './pages/MagazinesPage';
// react-pdf/pdfjs-dist pulls in a ~1MB PDF-rendering worker — code-split it
// so that weight is only ever fetched by someone actually opening the
// reader, not added to every page's initial load.
const MagazineReaderPage = lazy(() => import('./pages/MagazineReaderPage').then((m) => ({ default: m.MagazineReaderPage })));
import { LegalPage } from './pages/LegalPage';
import { NewsPage } from './pages/NewsPage';
import { FaqPage } from './pages/FaqPage';
import { ContactPage } from './pages/ContactPage';
import { AwardsPage } from './pages/AwardsPage';
import { ClientsPage } from './pages/ClientsPage';
import { CareerPage } from './pages/CareerPage';
import { CartPage } from './pages/CartPage';
import { DashboardPage } from './pages/DashboardPage';
import { OrdersPage } from './pages/OrdersPage';
import { EnquiriesPage } from './pages/EnquiriesPage';
import { ProfilePage } from './pages/ProfilePage';
import { ThankYouPage } from './pages/ThankYouPage';
import { LoginPage } from './pages/LoginPage';
import { SignupPage } from './pages/SignupPage';
import { ForgotPasswordPage } from './pages/ForgotPasswordPage';
import { ResetPasswordPage } from './pages/ResetPasswordPage';

export function App() {
  return (
    <SettingsProvider>
      <AuthProvider>
        <CartProvider>
          {/* import.meta.env.BASE_URL mirrors vite.config.js's `base` — keeps
              router links in sync with the deployed subfolder automatically
              instead of hardcoding the path in two places. */}
          <Router basename={import.meta.env.BASE_URL}>
            <div className="flex flex-col min-h-screen">
              <ScrollToTop />
              <AnnouncementBar />
              <Routes>
                <Route
                  path="*"
                  element={
                    <>
                      <Header />
                      <CategorySlider />
                      <main className="flex-1">
                        <Routes>
                          <Route path="/" element={<HomePage />} />
                          <Route path="/category/:slug" element={<CategoryPage />} />
                          <Route path="/category" element={<CategoryPage />} />
                          <Route path="/listing/:slug" element={<ListingDetailPage />} />
                          <Route path="/listing" element={<ListingDetailPage />} />
                          <Route path="/blogs" element={<BlogListPage />} />
                          <Route path="/blog" element={<BlogDetailPage />} />
                          <Route path="/magazines-reader" element={<MagazinesPage />} />
                          <Route path="/about" element={<LegalPage title="About Us" field="about_us" />} />
                          <Route path="/privacy-policy" element={<LegalPage title="Privacy Policy" field="privacy_policy" />} />
                          <Route path="/terms-of-service" element={<LegalPage title="Terms of Service" field="terms_of_use" />} />
                          <Route path="/news" element={<NewsPage />} />
                          <Route path="/faq" element={<FaqPage />} />
                          <Route path="/contact" element={<ContactPage />} />
                          <Route path="/awards" element={<AwardsPage />} />
                          <Route path="/clients" element={<ClientsPage />} />
                          <Route path="/careers" element={<CareerPage />} />
                          <Route path="/cart" element={<CartPage />} />
                          <Route path="/dashboard" element={<ProtectedRoute><DashboardPage /></ProtectedRoute>} />
                          <Route path="/orders" element={<ProtectedRoute><OrdersPage /></ProtectedRoute>} />
                          <Route path="/enquiries" element={<ProtectedRoute><EnquiriesPage /></ProtectedRoute>} />
                          <Route path="/profile" element={<ProtectedRoute><ProfilePage /></ProtectedRoute>} />
                          <Route path="/thank-you" element={<ProtectedRoute><ThankYouPage /></ProtectedRoute>} />
                        </Routes>
                      </main>
                      <Footer />
                      <MobileBottomNav />
                    </>
                  }
                />

                {/* Standalone Auth Routes (without main Header/Footer layout) */}
                <Route path="/login" element={<LoginPage />} />
                <Route path="/signup" element={<SignupPage />} />
                <Route path="/forgot-password" element={<ForgotPasswordPage />} />
                <Route path="/reset-password" element={<ResetPasswordPage />} />

                {/* Standalone Magazine Reader (full-bleed, no site chrome —
                    an immersive reading view rather than a normal page).
                    Suspense's fallback covers the moment the lazy-loaded
                    reader bundle (react-pdf/pdfjs-dist) is still downloading. */}
                <Route
                  path="/magazines-reader/:slug"
                  element={(
                    <Suspense fallback={<div className="bg-slate-900 min-h-screen" />}>
                      <MagazineReaderPage />
                    </Suspense>
                  )}
                />
              </Routes>

              {/* Global Modals & Overlays */}
              <InquiryModal />
              <SearchModal />
              <CartDrawer />
              <Toast />
            </div>
          </Router>
        </CartProvider>
      </AuthProvider>
    </SettingsProvider>
  );
}

export default App;
