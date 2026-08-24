import React, { createContext, useContext, useState, useEffect } from 'react';
import { MEDIA_DATABASE } from '../data/mediaData';

const CartContext = createContext();

export const CartProvider = ({ children }) => {
  const [cart, setCart] = useState(() => {
    try {
      const saved = localStorage.getItem('ep_cart');
      return saved ? JSON.parse(saved) : [];
    } catch {
      return [];
    }
  });

  const [toasts, setToasts] = useState([]);
  const [isSearchOpen, setIsSearchOpen] = useState(false);
  const [isInquiryOpen, setIsInquiryOpen] = useState(false);
  const [inquiryContext, setInquiryContext] = useState(null);
  const [isCartDrawerOpen, setIsCartDrawerOpen] = useState(false);

  useEffect(() => {
    localStorage.setItem('ep_cart', JSON.stringify(cart));
  }, [cart]);

  const showToast = (message, type = 'info') => {
    const id = Date.now();
    setToasts((prev) => [...prev, { id, message, type }]);
    setTimeout(() => {
      setToasts((prev) => prev.filter((t) => t.id !== id));
    }, 4000);
  };

  /**
   * `knownItem` lets callers pass a fully-formed item (e.g. a live Media
   * Inventory result already normalized to this shape) instead of relying
   * on the static MEDIA_DATABASE lookup, which only knows about mock items.
   * It's only needed on the add path — removal reads the title back out of
   * the cart itself, so it works regardless of where the item came from.
   */
  const toggleCartItem = (id, knownItem = null) => {
    setCart((prev) => {
      const existingIndex = prev.findIndex((c) => c.id === id);
      if (existingIndex > -1) {
        showToast(`Removed "${prev[existingIndex].title}" from plan`, 'info');
        return prev.filter((c) => c.id !== id);
      }

      const item = knownItem || MEDIA_DATABASE.find((m) => m.id === id);
      if (!item) return prev;

      showToast(`Added "${item.title}" to cart!`, 'success');
      return [...prev, { ...item, quantity: 1 }];
    });
  };

  const updateQuantity = (id, change) => {
    setCart((prev) =>
      prev
        .map((item) => {
          if (item.id === id) {
            const newQty = (item.quantity || 1) + change;
            return newQty > 0 ? { ...item, quantity: newQty } : null;
          }
          return item;
        })
        .filter(Boolean)
    );
  };

  const clearCart = () => {
    setCart([]);
    showToast('Cleared all items from cart', 'info');
  };

  const cartTotal = cart.reduce((sum, item) => sum + item.price * (item.quantity || 1), 0);

  return (
    <CartContext.Provider
      value={{
        cart,
        cartTotal,
        toggleCartItem,
        updateQuantity,
        clearCart,
        toasts,
        showToast,
        isSearchOpen,
        setIsSearchOpen,
        isInquiryOpen,
        setIsInquiryOpen,
        inquiryContext,
        setInquiryContext,
        isCartDrawerOpen,
        setIsCartDrawerOpen
      }}
    >
      {children}
    </CartContext.Provider>
  );
};

export const useCart = () => useContext(CartContext);
