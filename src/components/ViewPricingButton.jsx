import React from 'react';
import { useNavigate, useLocation } from 'react-router-dom';

export const ViewPricingButton = ({ className }) => {
  const navigate = useNavigate();
  const location = useLocation();

  const handleClick = () => {
    navigate('/login', { state: { from: `${location.pathname}${location.search}` } });
  };

  return (
    <button
      onClick={handleClick}
      type="button"
      className={
        className ||
        'inline-flex items-center gap-1.5 text-xs font-bold text-brand-red bg-red-50 hover:bg-brand-red hover:text-white border border-red-100 transition rounded-xl px-3.5 py-2 cursor-pointer'
      }>
      <i className="fa-solid fa-lock text-[10px]"></i>
      <span>View Pricing</span>
    </button>
  );
};
