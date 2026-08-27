import React from 'react';
import { Link } from 'react-router-dom';
import { useCart } from '../context/CartContext';
import { useAuth } from '../context/AuthContext';
import { ViewPricingButton } from './ViewPricingButton';

export const CartDrawer = () => {
  const { cart, cartTotal, toggleCartItem, isCartDrawerOpen, setIsCartDrawerOpen, setIsInquiryOpen } = useCart();
  const { isAuthenticated } = useAuth();

  return (
    <div 
      className={`fixed inset-y-0 right-0 z-50 w-full max-w-md bg-white border-l border-slate-200 shadow-2xl transform transition-transform duration-300 ease-in-out flex flex-col ${
        isCartDrawerOpen ? 'translate-x-0' : 'translate-x-full'
      }`}>
      
      {/* Drawer Header */}
      <div className="p-5 border-b border-slate-200 flex justify-between items-center bg-slate-50">
        <div className="flex items-center gap-2">
          <i className="fa-solid fa-layer-group text-brand-red text-lg"></i>
          <h3 className="font-outfit font-bold text-lg text-slate-900">Your Cart</h3>
        </div>
        <button 
          onClick={() => setIsCartDrawerOpen(false)}
          className="text-slate-400 hover:text-slate-900 text-lg p-1 cursor-pointer">
          <i className="fa-solid fa-xmark"></i>
        </button>
      </div>

      {/* Drawer Items List */}
      <div className="flex-1 p-5 overflow-y-auto space-y-3">
        {cart.length === 0 ? (
          <div className="py-20 text-center text-slate-400">
            <i className="fa-solid fa-basket-shopping text-5xl text-slate-300 mb-4 block"></i>
            <h4 className="text-lg font-bold text-slate-800 mb-1">Your Basket is Empty</h4>
            <p className="text-xs text-slate-500 max-w-xs mx-auto">Browse our verified media inventory to start building your plan.</p>
          </div>
        ) : (
          cart.map((item) => (
            <div key={item.id} className="bg-slate-50 p-3 rounded-xl flex items-center justify-between gap-3 border border-slate-200 shadow-sm">
              <img src={item.image} alt={item.title} className="w-14 h-14 rounded-lg object-cover" />
              <div className="flex-1 min-w-0">
                <h4 className="text-xs font-bold text-slate-900 truncate">{item.title}</h4>
                <span className="text-[10px] text-slate-500 font-medium block">{item.category} • {item.location}</span>
                {isAuthenticated ? (
                  <span className="text-xs font-extrabold text-brand-red font-outfit">₹{item.price.toLocaleString('en-IN')} <span className="font-normal text-slate-500 text-[10px]">{item.priceUnit}</span></span>
                ) : (
                  <ViewPricingButton className="inline-flex items-center gap-1 text-[10px] font-bold text-brand-red bg-red-50 hover:bg-brand-red hover:text-white border border-red-100 transition rounded-lg px-2 py-1 cursor-pointer" />
                )}
              </div>
              <button 
                onClick={() => toggleCartItem(item.id)}
                className="text-slate-400 hover:text-red-600 p-2 text-sm transition cursor-pointer">
                <i className="fa-solid fa-trash-can"></i>
              </button>
            </div>
          ))
        )}
      </div>

      {/* Drawer Footer */}
      <div className="p-5 border-t border-slate-200 bg-slate-50 space-y-4">
        <div className="flex justify-between items-center text-sm">
          <span className="text-slate-600 font-medium">Total Estimated Plan Spend:</span>
          {isAuthenticated ? (
            <span className="font-outfit font-black text-xl text-brand-red">₹{cartTotal.toLocaleString('en-IN')}</span>
          ) : (
            <ViewPricingButton />
          )}
        </div>

        <div className="grid grid-cols-2 gap-2">
          <Link
            to="/cart"
            onClick={() => setIsCartDrawerOpen(false)}
            className="w-full bg-slate-900 hover:bg-slate-800 text-white font-outfit font-bold text-xs py-3 rounded-xl transition flex items-center justify-center gap-1.5">
            <i className="fa-solid fa-cart-shopping text-xs"></i>
            <span>View Full Cart</span>
          </Link>

          <button 
            onClick={() => {
              setIsCartDrawerOpen(false);
              setIsInquiryOpen(true);
            }}
            className="w-full bg-brand-red hover:bg-brand-red-dark text-white font-outfit font-bold text-xs py-3 rounded-xl shadow-lg shadow-brand-red/25 transition flex items-center justify-center gap-1.5 cursor-pointer">
            <i className="fa-solid fa-file-invoice text-xs"></i>
            <span>Get Quote</span>
          </button>
        </div>
      </div>

    </div>
  );
};
