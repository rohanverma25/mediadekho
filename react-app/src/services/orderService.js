import { apiFetch } from './api';

export async function createOrder(items) {
  return apiFetch('/orders', {
    method: 'POST',
    body: JSON.stringify({ items }),
  });
}

export async function verifyPayment(orderNumber, { razorpay_payment_id, razorpay_order_id, razorpay_signature }) {
  return apiFetch(`/orders/${orderNumber}/verify`, {
    method: 'POST',
    body: JSON.stringify({ razorpay_payment_id, razorpay_order_id, razorpay_signature }),
  });
}

export async function fetchMyOrders() {
  return apiFetch('/orders');
}

export async function fetchOrder(orderNumber) {
  return apiFetch(`/orders/${orderNumber}`);
}
