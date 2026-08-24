import { apiFetch } from './api';

export async function submitContactLead({ name, email, phone, companyName, location, subject, description }) {
  return apiFetch('/contact', {
    method: 'POST',
    body: JSON.stringify({
      name,
      email,
      phone: phone || undefined,
      company_name: companyName || undefined,
      location: location || undefined,
      subject: subject || undefined,
      description,
    }),
  });
}
