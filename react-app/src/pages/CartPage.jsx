import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useCart } from '../context/CartContext';
import { useAuth } from '../context/AuthContext';
import { createOrder, verifyPayment } from '../services/orderService';
import { ApiError } from '../services/api';

export const CartPage = () => {
  const { cart, updateQuantity, toggleCartItem, clearCart, setIsInquiryOpen, setInquiryContext, showToast } = useCart();
  const { isAuthenticated, user } = useAuth();
  const navigate = useNavigate();

  const [isCheckingOut, setIsCheckingOut] = useState(false);

  // Each item's price already comes from PricingService.priceForUser() as
  // final_price — selling price with that item's own admin-configured
  // discount and tax already applied — so the grand total is a plain sum,
  // never a separate flat-rate recalculation on top (that would double-tax
  // it). listPrice/discountAmount/taxAmount are the same API's breakdown of
  // that number, shown for transparency; they fall back to price/0/0 for
  // any older cart entry saved before this breakdown existed.
  const listSubtotal = cart.reduce((sum, item) => sum + (item.listPrice ?? item.price) * (item.quantity || 1), 0);
  const discountTotal = cart.reduce((sum, item) => sum + (item.discountAmount ?? 0) * (item.quantity || 1), 0);
  const taxTotal = cart.reduce((sum, item) => sum + (item.taxAmount ?? 0) * (item.quantity || 1), 0);
  const grandTotal = cart.reduce((sum, item) => sum + item.price * (item.quantity || 1), 0);

  const handleEnquireNow = () => {
    setInquiryContext({
      subject: 'Media Plan Enquiry',
      description: cart.length > 0
        ? `Please send an official proposal for my media plan:\n${cart.map((item) => `- ${item.title} x${item.quantity || 1}`).join('\n')}`
        : '',
      items: cart.map((item) => item.title),
    });
    setIsInquiryOpen(true);
  };

  const handleCheckout = async () => {
    if (!isAuthenticated) {
      navigate('/login', { state: { from: '/cart' } });
      return;
    }

    if (cart.length === 0 || isCheckingOut) return;

    setIsCheckingOut(true);

    let order;
    try {
      order = await createOrder(cart.map((item) => ({ inventory_id: item.id, quantity: item.quantity || 1 })));
    } catch (err) {
      showToast(err instanceof ApiError ? err.message : 'Unable to start checkout. Please try again.', 'info');
      setIsCheckingOut(false);
      return;
    }

    if (!window.Razorpay) {
      showToast('Payment gateway failed to load. Please refresh and try again.', 'info');
      setIsCheckingOut(false);
      return;
    }

    const razorpay = new window.Razorpay({
      key: order.razorpay_key_id,
      amount: order.amount,
      currency: order.currency,
      order_id: order.razorpay_order_id,
      name: 'Media Dekho',
      description: 'Media Plan Booking',
      prefill: { name: user?.name, email: user?.email },
      modal: {
        ondismiss: () => setIsCheckingOut(false),
      },
      handler: async (response) => {
        try {
          await verifyPayment(order.order.order_number, response);
          clearCart();
          navigate(`/thank-you?order=${order.order.order_number}`);
        } catch {
          showToast('Payment verification failed. Please contact support with your payment ID.', 'info');
          setIsCheckingOut(false);
        }
      },
    });

    razorpay.open();
  };

  return (
    <div>

      {/* BREADCRUMB */}
      <section className="bg-gradient-to-b from-slate-100 to-slate-50 border-b border-slate-200 py-6 px-4 sm:px-6">
        <div className="max-w-7xl mx-auto">
          <nav className="flex items-center gap-2 text-xs text-slate-500 font-medium mb-1">
            <Link to="/" className="hover:text-brand-red">Home</Link>
            <i className="fa-solid fa-chevron-right text-[9px]"></i>
            <span className="text-slate-900 font-bold">Cart</span>
          </nav>
          <h1 className="font-outfit font-black text-2xl sm:text-3xl text-slate-900 tracking-tight">
            Your Selected Media Plan
          </h1>
        </div>
      </section>

      {/* MAIN CART SECTION */}
      <section className="py-10 px-4 sm:px-6">
        <div className="max-w-7xl mx-auto">

          <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            {/* LEFT COLS (8 COLS) */}
            <main className="lg:col-span-8 space-y-6">

              <div className="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex justify-between items-center">
                <div className="flex items-center gap-2">
                  <i className="fa-solid fa-layer-group text-brand-red text-lg"></i>
                  <h2 className="font-outfit font-bold text-base text-slate-900">
                    Selected Media Options (<span className="text-brand-red font-black">{cart.length}</span>)
                  </h2>
                </div>

                <button
                  onClick={clearCart}
                  className="text-xs font-semibold text-slate-400 hover:text-brand-red flex items-center gap-1 transition cursor-pointer">
                  <i className="fa-solid fa-trash-can text-xs"></i>
                  <span>Clear Entire Basket</span>
                </button>
              </div>

              {cart.length === 0 ? (
                <div className="bg-white p-12 rounded-3xl border border-slate-200 shadow-sm text-center space-y-4">
                  <div className="w-16 h-16 rounded-full bg-red-50 border border-red-100 flex items-center justify-center text-brand-red text-2xl mx-auto">
                    <i className="fa-solid fa-basket-shopping"></i>
                  </div>
                  <h3 className="font-outfit font-extrabold text-2xl text-slate-900">Your Basket is Empty</h3>
                  <p className="text-xs text-slate-500 max-w-md mx-auto leading-relaxed">
                    Browse our verified media inventory across every category to start building your plan.
                  </p>
                  <Link to="/category" className="inline-flex items-center gap-2 bg-brand-red hover:bg-brand-red-dark text-white font-outfit font-bold text-xs px-6 py-3 rounded-xl shadow-lg shadow-brand-red/25 transition">
                    <span>Browse All Media Options</span>
                    <i className="fa-solid fa-arrow-right text-xs"></i>
                  </Link>
                </div>
              ) : (
                <div className="space-y-4">
                  {cart.map((item) => {
                    const qty = item.quantity || 1;
                    const itemSubtotal = item.price * qty;
                    const listingHref = item.slug ? `/listing/${item.slug}` : '/listing';

                    return (
                      <div key={item.id} className="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5 transition hover:shadow-md">
                        <div className="flex items-center gap-4 min-w-0 flex-1">
                          <Link to={listingHref} className="w-20 h-20 rounded-2xl overflow-hidden flex-shrink-0 bg-slate-100 border border-slate-200 block">
                            <img src={item.image} alt={item.title} className="w-full h-full object-cover" />
                          </Link>

                          <div className="space-y-1 min-w-0">
                            <div className="flex items-center gap-2">
                              <span className="bg-red-50 text-brand-red text-[10px] uppercase font-extrabold px-2.5 py-0.5 rounded-full border border-red-100">
                                {item.category}
                              </span>
                              <span className="text-xs text-slate-500 font-medium">{item.location}</span>
                            </div>

                            <Link to={listingHref} className="font-outfit font-bold text-base text-slate-900 truncate block hover:text-brand-red transition">
                              {item.title}
                            </Link>

                            <div className="flex items-center gap-2 text-xs text-slate-500">
                              <span>Card Rate: <strong className="text-slate-800 font-outfit">₹{item.price.toLocaleString('en-IN')}</strong> / {item.priceUnit}</span>
                            </div>
                          </div>
                        </div>

                        <div className="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                          <div className="flex items-center gap-2">
                            <span className="text-[10px] text-slate-400 font-bold uppercase hidden sm:inline">Insertions:</span>
                            <div className="flex items-center border border-slate-200 rounded-xl bg-slate-50 p-1">
                              <button
                                onClick={() => updateQuantity(item.id, -1)}
                                className="w-7 h-7 rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-brand-red hover:text-white flex items-center justify-center text-xs font-bold transition cursor-pointer">
                                <i className="fa-solid fa-minus text-[10px]"></i>
                              </button>
                              <span className="w-8 text-center font-outfit font-extrabold text-sm text-slate-900">{qty}</span>
                              <button
                                onClick={() => updateQuantity(item.id, 1)}
                                className="w-7 h-7 rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-brand-red hover:text-white flex items-center justify-center text-xs font-bold transition cursor-pointer">
                                <i className="fa-solid fa-plus text-[10px]"></i>
                              </button>
                            </div>
                          </div>

                          <div className="text-right">
                            <span className="text-[10px] text-slate-400 font-bold uppercase block">Item Subtotal</span>
                            <span className="font-outfit font-black text-lg text-slate-900">₹{itemSubtotal.toLocaleString('en-IN')}</span>
                          </div>

                          <button
                            onClick={() => toggleCartItem(item.id, item)}
                            className="text-slate-400 hover:text-brand-red p-2 text-base transition cursor-pointer">
                            <i className="fa-solid fa-trash-can"></i>
                          </button>
                        </div>
                      </div>
                    );
                  })}
                </div>
              )}

            </main>

            {/* RIGHT SUMMARY (4 COLS) */}
            <aside className="lg:col-span-4 space-y-6">
              <div className="bg-white p-6 rounded-3xl border border-slate-200 shadow-xl space-y-6 sticky top-24">

                <div className="border-b border-slate-100 pb-4">
                  <span className="text-[10px] text-slate-400 uppercase font-bold tracking-widest block">Cost Calculation</span>
                  <h3 className="font-outfit font-extrabold text-xl text-slate-900">Order Summary</h3>
                </div>

                <div className="space-y-3 text-xs">
                  <div className="flex justify-between text-slate-600">
                    <span>Media Options List Price:</span>
                    <span className="font-bold text-slate-900">₹{listSubtotal.toLocaleString('en-IN')}</span>
                  </div>

                  {discountTotal > 0 && (
                    <div className="flex justify-between text-emerald-600 font-bold bg-emerald-50 p-2 rounded-xl border border-emerald-200">
                      <span>Discount:</span>
                      <span>-₹{discountTotal.toLocaleString('en-IN')}</span>
                    </div>
                  )}

                  <div className="flex justify-between text-slate-600">
                    <span>Tax:</span>
                    <span className="font-bold text-slate-900">₹{taxTotal.toLocaleString('en-IN')}</span>
                  </div>
                </div>

                <div className="border-t border-slate-200 pt-4">
                  <div className="flex justify-between items-baseline mb-1">
                    <span className="text-xs font-bold text-slate-700">Total Estimated Spend:</span>
                    <div className="font-outfit font-black text-2xl sm:text-3xl text-brand-red">₹{grandTotal.toLocaleString('en-IN')}</div>
                  </div>
                </div>

                <div className="grid grid-cols-1 gap-3">
                  <button
                    onClick={handleCheckout}
                    disabled={isCheckingOut || cart.length === 0}
                    className="w-full btn-shimmer bg-brand-red hover:bg-brand-red-dark disabled:opacity-60 disabled:cursor-not-allowed text-white font-outfit font-bold text-sm py-3.5 rounded-2xl shadow-xl shadow-brand-red/25 transition flex items-center justify-center gap-2 cursor-pointer">
                    <i className={`fa-solid ${isCheckingOut ? 'fa-spinner fa-spin' : 'fa-lock'} text-xs`}></i>
                    <span>{isCheckingOut ? 'Processing...' : 'Checkout'}</span>
                  </button>

                  <button
                    onClick={handleEnquireNow}
                    className="w-full bg-slate-900 hover:bg-slate-800 text-white font-outfit font-bold text-sm py-3.5 rounded-2xl transition flex items-center justify-center gap-2 cursor-pointer">
                    <i className="fa-solid fa-paper-plane text-xs"></i>
                    <span>Enquire Now</span>
                  </button>
                </div>

              </div>
            </aside>

          </div>

        </div>
      </section>
    </div>
  );
};
