import { apiFetch } from './api';

export async function fetchAwards() {
  const json = await apiFetch('/awards');
  return json.data ?? [];
}

export async function submitAwardNomination({ awardId, name, email, phone, companyName, description }) {
  return apiFetch('/award-nominations', {
    method: 'POST',
    body: JSON.stringify({
      award_id: awardId,
      name,
      email,
      phone: phone || undefined,
      company_name: companyName || undefined,
      description,
    }),
  });
}

export async function fetchMyAwardNominations() {
  const json = await apiFetch('/my/award-nominations');
  return json.data ?? [];
}
