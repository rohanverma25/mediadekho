import { apiFetch } from './api';

const FALLBACK_IMAGE =
  'https://images.unsplash.com/photo-1611162617213-7d7a39e9b1d7?auto=format&fit=crop&w=1000&q=80';

export async function fetchMediaInventory(filters = {}) {
  const params = new URLSearchParams();
  Object.entries(filters).forEach(([key, value]) => {
    if (value !== null && value !== undefined && value !== '') {
      params.set(key, value);
    }
  });

  const query = params.toString();
  const json = await apiFetch(`/media-inventory${query ? `?${query}` : ''}`);
  return json.data ?? [];
}

export async function fetchMediaInventoryBySlug(slug) {
  const json = await apiFetch(`/media-inventory/${slug}`);
  return json.data ?? null;
}

/**
 * Maps the API's nested inventory shape onto the flat card/cart shape the
 * rest of the UI (built against the static MEDIA_DATABASE mock) expects.
 * Deliberately doesn't fabricate `location`/`rating` — the API has no real
 * data for either (they used to be hardcoded to 'Pan-India' and 4.8 for
 * every single item), so they're left out entirely rather than shown as if
 * they meant something.
 */
export function normalizeInventoryItem(item) {
  const isAvailable = Boolean(item.price?.available);
  const price = isAvailable ? item.price.final_price : 0;
  // The API itself locks pricing for unauthenticated requests (no numeric
  // fields at all) — trusting that flag rather than re-deriving it from
  // frontend auth state keeps every consumer in sync with what the server
  // actually decided, even if client-side auth state is briefly stale.
  const priceLocked = Boolean(item.price?.locked);

  return {
    id: item.id,
    slug: item.slug,
    title: item.title,
    category: item.category?.name ?? 'Media Inventory',
    // Genuinely null (not a category-name fallback) when the listing has no
    // subcategory, so callers can tell "no subcategory" apart from "one
    // that happens to share the category's name" and skip rendering it.
    subCategory: item.subcategory?.name || null,
    price,
    priceLocked,
    // Breakdown behind `price` (already tax-inclusive) — lets pages like the
    // cart show what was discounted and taxed instead of re-deriving it with
    // guessed rates. Undefined (not 0) when unavailable, so callers can tell
    // "no pricing data" apart from "legitimately zero".
    listPrice: isAvailable ? item.price.list_price : undefined,
    discountAmount: isAvailable ? item.price.discount_amount : undefined,
    taxPercentage: isAvailable ? item.price.tax_percentage : undefined,
    taxAmount: isAvailable ? item.price.tax_amount : undefined,
    priceUnit: '',
    frequency: item.frequency ? { id: item.frequency.id, name: item.frequency.name } : null,
    impressions: item.frequency?.name ? `${item.frequency.name} Frequency` : 'On Request',
    image: item.cover_image_url || item.image_url || FALLBACK_IMAGE,
    specs: item.key_insights?.length
      ? item.key_insights.slice(0, 4).map((insight) => `${insight.label}: ${insight.value}`)
      : item.short_description
        ? [item.short_description]
        : [],
  };
}

export { FALLBACK_IMAGE };
