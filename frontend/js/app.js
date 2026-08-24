/**
 * Media Dekho - Interactive Application Logic
 * Supports index.html (Homepage), category.html (Category Page), listing.html (Listing Detail Page), login.html & signup.html
 */

// Laravel API base — point this at your deployed backend for production.
const API_BASE_URL = 'http://127.0.0.1:8000/api';

function escapeHtml(value) {
  const div = document.createElement('div');
  div.textContent = value;
  return div.innerHTML;
}

// --- Sample Dataset of Media Advertising Options ---
const MEDIA_DATABASE = [
  // ARCHITECT & INTERIORS INDIA (SPECIFIC LISTING)
  {
    id: 'mag-arch-1',
    title: 'Architect and Interiors India Full Page Color Ad Spot',
    vertical: 'offline',
    category: 'Magazine Advertising',
    subCategory: 'Business & Finance',
    location: 'Pan-India',
    city: 'Pan-India',
    price: 125000,
    priceUnit: 'per insertion',
    impressions: '180K+ Architects',
    rating: 4.9,
    badge: 'Verified Owner Rate',
    image: 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=1000&q=80',
    specs: ['Full Page Glossy 4C', '45,000 Copies Circulation', 'Monthly Trade Print']
  },
  {
    id: 'mag-arch-ifc',
    title: 'Architect and Interiors India Inside Front Cover (IFC)',
    vertical: 'offline',
    category: 'Magazine Advertising',
    subCategory: 'Business & Finance',
    location: 'Pan-India',
    city: 'Pan-India',
    price: 240000,
    priceUnit: 'per issue',
    impressions: '180K+ Readers',
    rating: 5.0,
    badge: 'Prime Front Cover',
    image: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1000&q=80',
    specs: ['Page 2 Prime Spot', 'High Executive Recall', 'Monthly Trade Print']
  },
  {
    id: 'mag-arch-bc',
    title: 'Architect and Interiors India Back Cover (BC)',
    vertical: 'offline',
    category: 'Magazine Advertising',
    subCategory: 'Business & Finance',
    location: 'Pan-India',
    city: 'Pan-India',
    price: 310000,
    priceUnit: 'per issue',
    impressions: '180K+ Readers',
    rating: 5.0,
    badge: 'Exterior Back Cover',
    image: 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1000&q=80',
    specs: ['Outer Back Cover Placement', 'Maximum Eye Contact', 'Monthly Trade Print']
  },
  {
    id: 'mag-arch-dps',
    title: 'Architect and Interiors India Double Page Spread (DPS)',
    vertical: 'offline',
    category: 'Magazine Advertising',
    subCategory: 'Business & Finance',
    location: 'Pan-India',
    city: 'Pan-India',
    price: 280000,
    priceUnit: 'per issue',
    impressions: '180K+ Readers',
    rating: 4.8,
    badge: 'Center Spread',
    image: 'https://images.unsplash.com/photo-1600566753376-12c8ab7fb75b?auto=format&fit=crop&w=1000&q=80',
    specs: ['2-Page Center Spread', 'Panoramic Visual', 'Monthly Trade Print']
  },
  {
    id: 'mag-arch-half',
    title: 'Architect and Interiors India Half Page Color Ad',
    vertical: 'offline',
    category: 'Magazine Advertising',
    subCategory: 'Business & Finance',
    location: 'Pan-India',
    city: 'Pan-India',
    price: 75000,
    priceUnit: 'per issue',
    impressions: '180K+ Readers',
    rating: 4.7,
    badge: 'Budget Option',
    image: 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=1000&q=80',
    specs: ['Half Page Spot', 'Standard Reach', 'Monthly Trade Print']
  },

  // MAGAZINE CATEGORY OPTIONS
  {
    id: 'mag-1',
    title: 'Vogue India Full Page Color Advertisement Spot',
    vertical: 'offline',
    category: 'Magazine Advertising',
    subCategory: 'Fashion & Lifestyle',
    location: 'Pan-India',
    city: 'Pan-India',
    price: 350000,
    priceUnit: 'per insertion',
    impressions: '1.2M+ Circulation',
    rating: 5.0,
    badge: 'Luxury Top Pick',
    image: 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=800&q=80',
    specs: ['Full Page Glossy 4C', 'High HNI Readership', 'Issue Date: 1st of Month']
  },
  {
    id: 'mag-2',
    title: 'Forbes India Inside Front Cover (IFC) Full Page',
    vertical: 'offline',
    category: 'Magazine Advertising',
    subCategory: 'Business & Finance',
    location: 'Pan-India',
    city: 'Pan-India',
    price: 480000,
    priceUnit: 'per issue',
    impressions: '850K+ Executives',
    rating: 4.9,
    badge: 'Executive Reach',
    image: 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?auto=format&fit=crop&w=800&q=80',
    specs: ['Prime IFC Position', 'CXO Audience', 'Bi-Weekly Edition']
  },
  {
    id: 'mag-3',
    title: 'Air India In-Flight Magazine Double Page Spread',
    vertical: 'offline',
    category: 'Magazine Advertising',
    subCategory: 'In-Flight Magazines',
    location: 'Pan-India & International',
    city: 'Mumbai',
    price: 290000,
    priceUnit: 'per month',
    impressions: '3.5M+ Passengers',
    rating: 4.8,
    badge: 'Captive Audience',
    image: 'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?auto=format&fit=crop&w=800&q=80',
    specs: ['Double Page Center Spread', 'Premium Travelers', 'Seat-pocket Placement']
  },

  // OTHER VERTICALS & CATEGORIES
  {
    id: 'off-1',
    title: 'Mumbai T2 Airport International Arrivals Digital Screen',
    vertical: 'offline',
    category: 'Airport Advertising',
    subCategory: 'Transit & Metro',
    location: 'Mumbai, Maharashtra',
    city: 'Mumbai',
    price: 150000,
    priceUnit: 'per week',
    impressions: '2.5M+ / mo',
    rating: 4.9,
    badge: 'High Impact',
    image: 'https://images.unsplash.com/photo-1542296332-2e4473faf563?auto=format&fit=crop&w=800&q=80',
    specs: ['100% HD LED', '15 Sec Loop', '24/7 Visibility']
  },
  {
    id: 'off-2',
    title: 'Delhi Metro Line-2 Premium Full Train Wrap',
    vertical: 'offline',
    category: 'Transit & Metro',
    subCategory: 'Transit & Metro',
    location: 'Delhi NCR',
    city: 'Delhi',
    price: 280000,
    priceUnit: 'per month',
    impressions: '4.2M+ / mo',
    rating: 4.8,
    badge: 'Trending',
    image: 'https://images.unsplash.com/photo-1517649763962-0c623266010b?auto=format&fit=crop&w=800&q=80',
    specs: ['Pan-Delhi Reach', 'Full Exterior Vinyl', 'High Frequency Commuters']
  },
  {
    id: 'dig-1',
    title: 'Swiggy & Zomato In-App Homepage Header Banner',
    vertical: 'digital',
    category: 'App Takeover',
    subCategory: 'Tech & Gaming',
    location: 'Pan India Metros',
    city: 'Pan-India',
    price: 210000,
    priceUnit: 'per campaign',
    impressions: '6.5M+ / day',
    rating: 5.0,
    badge: 'Top Conversions',
    image: 'https://images.unsplash.com/photo-1526304640581-d334cdbbf45e?auto=format&fit=crop&w=800&q=80',
    specs: ['Clickable CTA', 'Hyper-local Targeting', 'Direct Foodie Reach']
  }
];

// --- State Management ---
let currentVertical = 'all';
let activeCategoryFilter = 'Magazine Advertising';
let searchKeyword = '';
let maxPriceLimit = 600000;
let selectedSubCats = [];
let selectedCities = [];
let activeSortOption = 'popular';
let viewMode = 'grid';
let campaignCart = JSON.parse(localStorage.getItem('ep_cart') || '[]');

// --- DOM Initialization ---
document.addEventListener('DOMContentLoaded', () => {
  const urlParams = new URLSearchParams(window.location.search);
  const catParam = urlParams.get('cat');
  if (catParam) {
    activeCategoryFilter = catParam;
  }

  initCategorySliderMenu();
  initVerticalTabs();
  initSidebarFilters();
  initSortingAndLayoutControls();
  initFaqAccordions();
  renderMediaCards();
  initEstimatorCalculator();
  initSearchModal();
  initCartDrawer();
  initCartPage();
  initInquiryModal();
  updateCartBadge();
  initScrollEffects();
});

// Helper function for listing.html to add specific tariff options
function toggleListingCart(id) {
  toggleCartItem(id);
}

// --- FAQ Accordion Logic ---
function initFaqAccordions() {
  const faqItems = document.querySelectorAll('.faq-item');
  faqItems.forEach(item => {
    const header = item.querySelector('.faq-header');
    if (header) {
      header.addEventListener('click', () => {
        const isActive = item.classList.contains('active');
        faqItems.forEach(i => i.classList.remove('active'));
        if (!isActive) {
          item.classList.add('active');
        }
      });
    }
  });
}

// --- 1. Category Slider Menu ---
function initCategorySliderMenu() {
  const track = document.getElementById('cat-slider-track');
  const leftBtn = document.getElementById('cat-slider-left');
  const rightBtn = document.getElementById('cat-slider-right');

  if (leftBtn && track) {
    leftBtn.addEventListener('click', () => {
      track.scrollBy({ left: -250, behavior: 'smooth' });
    });
  }

  if (rightBtn && track) {
    rightBtn.addEventListener('click', () => {
      track.scrollBy({ left: 250, behavior: 'smooth' });
    });
  }

  // Bind click/active-state behavior for whatever pills are on the page
  // right now (the static markup shipped in the HTML) so the nav works
  // immediately, before the live fetch below has a chance to resolve.
  bindCategoryMenuItems();

  loadLiveCategoriesIntoSlider();
}

// Wires up active-state + click handling for the current set of
// `.cat-menu-item` elements. Called once for the static markup on page
// load, and again after loadLiveCategoriesIntoSlider() replaces that
// markup with live data, so it always binds to what's actually in the DOM.
function bindCategoryMenuItems() {
  const items = document.querySelectorAll('.cat-menu-item');

  items.forEach(btn => {
    if (!btn.dataset.cat) return;

    if (btn.dataset.cat === activeCategoryFilter) {
      items.forEach(i => i.classList.remove('active'));
      btn.classList.add('active');
    }

    btn.addEventListener('click', (e) => {
      if (window.location.pathname.includes('category.html')) {
        e.preventDefault();
        items.forEach(i => i.classList.remove('active'));
        btn.classList.add('active');

        activeCategoryFilter = btn.dataset.cat;
        updateCategoryPageHeader(activeCategoryFilter);
        renderMediaCards();
      }
    });
  });
}

// Fetches categories from the Laravel Category API and, if any come back,
// replaces the static pill markup with live ones. On category.html the
// pills are in-page filter buttons; everywhere else they're links to
// category.html?cat=... — same shape the static markup already used, just
// generated from real data instead of hardcoded. If the fetch fails or the
// table is empty, the static markup already in the page is left alone, so
// the nav never goes blank.
function loadLiveCategoriesIntoSlider() {
  const track = document.getElementById('cat-slider-track');
  if (!track) return;

  const isCategoryPage = window.location.pathname.includes('category.html');

  fetch(`${API_BASE_URL}/categories`, { headers: { Accept: 'application/json' } })
    .then(res => (res.ok ? res.json() : Promise.reject(new Error(`Request failed with status ${res.status}`))))
    .then(json => {
      const categories = json.data || [];
      if (categories.length === 0) return;

      const pillsHtml = isCategoryPage
        ? [
            `<button class="cat-menu-item font-semibold text-xs px-4 py-2 rounded-full border transition" data-cat="all">All Media</button>`,
            ...categories.map(c => `<button class="cat-menu-item font-semibold text-xs px-4 py-2 rounded-full border transition" data-cat="${escapeHtml(c.name)}">${escapeHtml(c.name)}</button>`)
          ]
        : [
            `<a href="category.html?cat=all" class="cat-menu-item font-semibold text-xs px-3.5 py-1.5 rounded-full border transition" data-cat="all">All Verticals</a>`,
            ...categories.map(c => `<a href="category.html?cat=${encodeURIComponent(c.name)}" class="cat-menu-item font-semibold text-xs px-3.5 py-1.5 rounded-full border transition" data-cat="${escapeHtml(c.name)}">${escapeHtml(c.name)}</a>`)
          ];

      track.innerHTML = pillsHtml.join('');
      bindCategoryMenuItems();
    })
    .catch(() => {
      // Static fallback markup already in the page stays as-is.
    });
}

function updateCategoryPageHeader(category) {
  const breadcrumb = document.getElementById('breadcrumb-current');
  const title = document.getElementById('category-page-title');
  const desc = document.getElementById('category-page-desc');

  if (!title) return;

  if (category === 'all') {
    if (breadcrumb) breadcrumb.textContent = 'All Media';
    title.textContent = 'All Media Advertising Options & Owner Rates';
    if (desc) desc.textContent = 'Explore India\'s largest catalog of 300,000+ advertising spots across Magazine, Airport, Metro Transit, Digital, Sports, and Corporate Gifting.';
  } else {
    if (breadcrumb) breadcrumb.textContent = category;
    title.textContent = `${category} Agency & Owner Rates`;
    if (desc) desc.textContent = `Browse transparent direct media rates for ${category} with verified audience analytics, locations, and instant campaign execution.`;
  }
}

// --- 2. Vertical Tabs (for index.html) ---
function initVerticalTabs() {
  const tabButtons = document.querySelectorAll('.vertical-tab-btn');
  tabButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      tabButtons.forEach(b => {
        b.classList.remove('active', 'bg-brand-red', 'text-white', 'border-brand-red');
        b.classList.add('bg-white', 'text-slate-700', 'border-slate-200');
      });
      btn.classList.remove('bg-white', 'text-slate-700', 'border-slate-200');
      btn.classList.add('active', 'bg-brand-red', 'text-white', 'border-brand-red');

      currentVertical = btn.dataset.vertical;
      renderMediaCards();
    });
  });
}

// --- 3. Sidebar Filter Engine (for category.html) ---
function initSidebarFilters() {
  const searchInput = document.getElementById('sidebar-search-input');
  const priceRange = document.getElementById('sidebar-price-range');
  const priceValDisplay = document.getElementById('price-filter-val');
  const applyBtn = document.getElementById('apply-filters-btn');
  const resetBtn = document.getElementById('reset-filters-btn');

  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      searchKeyword = e.target.value.toLowerCase().trim();
      renderMediaCards();
    });
  }

  if (priceRange) {
    priceRange.addEventListener('input', (e) => {
      maxPriceLimit = parseInt(e.target.value);
      if (priceValDisplay) priceValDisplay.textContent = `₹${maxPriceLimit.toLocaleString('en-IN')}`;
      renderMediaCards();
    });
  }

  if (applyBtn) {
    applyBtn.addEventListener('click', () => {
      collectCheckboxFilters();
      renderMediaCards();
      showToast('Filters applied successfully!', 'info');
    });
  }

  if (resetBtn) {
    resetBtn.addEventListener('click', () => {
      searchKeyword = '';
      maxPriceLimit = 600000;
      selectedSubCats = [];
      selectedCities = [];
      
      if (searchInput) searchInput.value = '';
      if (priceRange) priceRange.value = 600000;
      if (priceValDisplay) priceValDisplay.textContent = '₹600,000';

      const checkboxes = document.querySelectorAll('.filter-cb');
      checkboxes.forEach(cb => cb.checked = true);

      renderMediaCards();
      showToast('Filters reset to default', 'info');
    });
  }
}

function collectCheckboxFilters() {
  selectedSubCats = [];
  selectedCities = [];

  const subCatCheckboxes = document.querySelectorAll('.filter-cb[data-type="subcat"]:checked');
  subCatCheckboxes.forEach(cb => selectedSubCats.push(cb.value));

  const cityCheckboxes = document.querySelectorAll('.filter-cb[data-type="city"]:checked');
  cityCheckboxes.forEach(cb => selectedCities.push(cb.value));
}

// --- 4. Sorting & Layout Controls ---
function initSortingAndLayoutControls() {
  const sortSelect = document.getElementById('sort-by-select');
  const gridBtn = document.getElementById('view-grid-btn');
  const listBtn = document.getElementById('view-list-btn');

  if (sortSelect) {
    sortSelect.addEventListener('change', (e) => {
      activeSortOption = e.target.value;
      renderMediaCards();
    });
  }

  if (gridBtn && listBtn) {
    gridBtn.addEventListener('click', () => {
      viewMode = 'grid';
      gridBtn.classList.add('text-brand-red', 'bg-white', 'shadow-sm');
      gridBtn.classList.remove('text-slate-500');
      listBtn.classList.remove('text-brand-red', 'bg-white', 'shadow-sm');
      listBtn.classList.add('text-slate-500');
      renderMediaCards();
    });

    listBtn.addEventListener('click', () => {
      viewMode = 'list';
      listBtn.classList.add('text-brand-red', 'bg-white', 'shadow-sm');
      listBtn.classList.remove('text-slate-500');
      gridBtn.classList.remove('text-brand-red', 'bg-white', 'shadow-sm');
      gridBtn.classList.add('text-slate-500');
      renderMediaCards();
    });
  }
}

// --- 5. Render Filtered & Sorted Cards Grid ---
function renderMediaCards() {
  const container = document.getElementById('media-cards-container');
  const countText = document.getElementById('results-count-text');
  const statOptionsCount = document.getElementById('stat-options-count');

  if (!container) return;

  const isCategoryPage = window.location.pathname.includes('category.html');
  collectCheckboxFilters();

  let filtered = MEDIA_DATABASE.filter(item => {
    if (!isCategoryPage) {
      if (currentVertical !== 'all' && item.vertical !== currentVertical) return false;
      return true;
    }

    if (activeCategoryFilter !== 'all' && item.category !== activeCategoryFilter) {
      return false;
    }
    if (searchKeyword && !item.title.toLowerCase().includes(searchKeyword) && !item.location.toLowerCase().includes(searchKeyword)) {
      return false;
    }
    if (item.price > maxPriceLimit) {
      return false;
    }
    if (selectedSubCats.length > 0 && item.subCategory && !selectedSubCats.includes(item.subCategory)) {
      return false;
    }
    if (selectedCities.length > 0 && item.city && !selectedCities.includes(item.city)) {
      return false;
    }
    return true;
  });

  if (activeSortOption === 'price-low') {
    filtered.sort((a, b) => a.price - b.price);
  } else if (activeSortOption === 'price-high') {
    filtered.sort((a, b) => b.price - a.price);
  } else if (activeSortOption === 'rating') {
    filtered.sort((a, b) => b.rating - a.rating);
  }

  if (countText) countText.textContent = filtered.length;
  if (statOptionsCount) statOptionsCount.textContent = `${filtered.length} Options`;

  if (filtered.length === 0) {
    container.innerHTML = `
      <div class="col-span-full py-16 text-center text-slate-500 glass-card rounded-2xl">
        <i class="fa-solid fa-filter-circle-xmark text-4xl text-brand-red mb-3 block"></i>
        <p class="text-lg font-semibold text-slate-800">No media options match your active criteria.</p>
        <p class="text-sm text-slate-500 mt-1">Try selecting another category or resetting filters.</p>
      </div>
    `;
    return;
  }

  if (isCategoryPage && viewMode === 'list') {
    container.className = 'flex flex-col gap-4';
  } else if (isCategoryPage) {
    container.className = 'grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6';
  } else {
    container.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6';
  }

  container.innerHTML = filtered.map(item => {
    const isCarted = campaignCart.some(c => c.id === item.id);

    if (isCategoryPage && viewMode === 'list') {
      return `
        <div class="glass-card rounded-2xl overflow-hidden bg-white border border-slate-200 p-4 flex flex-col sm:flex-row items-center gap-5 hover:shadow-lg transition">
          <a href="listing.html" class="block w-full sm:w-48 h-36 flex-shrink-0 overflow-hidden rounded-xl">
            <img src="${item.image}" class="w-full h-full object-cover hover:scale-105 transition">
          </a>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
              <span class="bg-red-50 text-brand-red border border-red-100 text-[10px] uppercase font-bold px-2 py-0.5 rounded-full">
                ${item.category}
              </span>
              <span class="text-xs text-slate-500 font-medium">• ${item.location}</span>
            </div>
            <a href="listing.html" class="font-bold text-base text-slate-900 mb-2 truncate block hover:text-brand-red transition">${item.title}</a>
            <div class="flex flex-wrap gap-1.5 mb-3">
              ${item.specs.map(spec => `<span class="bg-slate-100 text-slate-600 text-[10px] px-2 py-0.5 rounded"><i class="fa-solid fa-check text-brand-red text-[8px] mr-1"></i>${spec}</span>`).join('')}
            </div>
          </div>
          <div class="text-right sm:border-l sm:border-slate-100 sm:pl-5 flex-shrink-0 w-full sm:w-auto flex sm:flex-col justify-between items-center sm:items-end">
            <div>
              <span class="text-[10px] text-slate-400 font-bold uppercase block">Owner Rate</span>
              <div class="text-lg font-black text-slate-900 font-outfit">₹${item.price.toLocaleString('en-IN')}</div>
            </div>
            <button onclick="toggleCartItem('${item.id}')" class="mt-2 px-4 py-2 rounded-xl text-xs font-bold ${isCarted ? 'bg-emerald-600 text-white' : 'bg-brand-red hover:bg-brand-red-dark text-white'}">
              ${isCarted ? 'Added' : 'Add to Plan'}
            </button>
          </div>
        </div>
      `;
    }

    return `
      <div class="glass-card rounded-2xl overflow-hidden group flex flex-col justify-between h-full bg-white border border-slate-200 shadow-sm hover:shadow-xl transition-all duration-300">
        <div>
          <!-- Card Image Container -->
          <a href="listing.html" class="relative h-48 overflow-hidden block">
            <img src="${item.image}" alt="${item.title}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent"></div>
            
            <!-- Category Badge -->
            <span class="absolute top-3 left-3 bg-white/90 backdrop-blur-md text-slate-900 text-xs px-2.5 py-1 rounded-full font-bold shadow">
              ${item.category}
            </span>
            
            <!-- Highlight Badge -->
            <span class="absolute top-3 right-3 bg-brand-red text-white text-[10px] uppercase font-bold tracking-wider px-2.5 py-1 rounded-full shadow-lg">
              ${item.badge}
            </span>
            
            <div class="absolute bottom-3 left-3 right-3 flex justify-between items-center text-xs text-white font-medium">
              <span class="flex items-center gap-1"><i class="fa-solid fa-location-dot text-red-400"></i> ${item.location}</span>
              <span class="flex items-center gap-1 bg-brand-red text-white px-2 py-0.5 rounded font-bold">
                <i class="fa-solid fa-star text-white"></i> ${item.rating}
              </span>
            </div>
          </a>

          <!-- Card Content -->
          <div class="p-5">
            <a href="listing.html" class="font-bold text-lg text-slate-900 group-hover:text-brand-red transition-colors line-clamp-2 mb-3 leading-snug block">
              ${item.title}
            </a>

            <!-- Specs tags -->
            <div class="flex flex-wrap gap-1.5 mb-4">
              ${item.specs.map(spec => `
                <span class="bg-slate-100 border border-slate-200 text-slate-600 text-[11px] px-2 py-0.5 rounded font-medium">
                  <i class="fa-solid fa-circle-check text-brand-red text-[9px] mr-1"></i>${spec}
                </span>
              `).join('')}
            </div>
          </div>
        </div>

        <!-- Card Footer -->
        <div class="px-5 pb-5 pt-3 border-t border-slate-100 flex justify-between items-center mt-auto bg-slate-50/50">
          <div>
            <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold block">Estimated Starting</span>
            <div class="text-xl font-extrabold text-slate-900 font-outfit">
              ₹${item.price.toLocaleString('en-IN')} <span class="text-xs text-slate-500 font-normal font-inter">/ ${item.priceUnit}</span>
            </div>
          </div>

          <button 
            onclick="toggleCartItem('${item.id}')"
            class="px-4 py-2.5 rounded-xl font-bold text-xs flex items-center gap-1.5 transition-all duration-300 ${
              isCarted 
                ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg shadow-emerald-600/20' 
                : 'bg-brand-red hover:bg-brand-red-dark text-white shadow-lg shadow-brand-red/25 hover:scale-105'
            }">
            <i class="fa-solid ${isCarted ? 'fa-check' : 'fa-plus'}"></i>
            ${isCarted ? 'Added' : 'Add to Plan'}
          </button>
        </div>
      </div>
    `;
  }).join('');
}

// --- 6. Toggle Cart / Plan Items ---
function toggleCartItem(id) {
  const item = MEDIA_DATABASE.find(m => m.id === id);
  if (!item) return;

  const index = campaignCart.findIndex(c => c.id === id);
  if (index > -1) {
    campaignCart.splice(index, 1);
    showToast(`Removed "${item.title}" from plan`, 'info');
  } else {
    campaignCart.push(item);
    showToast(`Added "${item.title}" to campaign plan!`, 'success');
  }

  localStorage.setItem('ep_cart', JSON.stringify(campaignCart));
  updateCartBadge();
  renderMediaCards();
  renderCartDrawerItems();
  initCartPage();
}

function updateCartBadge() {
  const badges = document.querySelectorAll('.cart-count-badge');
  badges.forEach(b => {
    b.textContent = campaignCart.length;
    if (campaignCart.length > 0) {
      b.classList.remove('hidden');
    } else {
      b.classList.add('hidden');
    }
  });

  const totalBarAmount = document.getElementById('sticky-bar-total');
  if (totalBarAmount) {
    const total = campaignCart.reduce((sum, item) => sum + item.price, 0);
    totalBarAmount.textContent = `₹${total.toLocaleString('en-IN')}`;
  }
}

// --- 7. Interactive Ad Campaign Estimator Calculator ---
function initEstimatorCalculator() {
  const impressionsSlider = document.getElementById('est-impressions');
  const durationSlider = document.getElementById('est-duration');
  const impressionsVal = document.getElementById('val-impressions');
  const durationVal = document.getElementById('val-duration');

  const calcReach = document.getElementById('calc-reach');
  const calcBudget = document.getElementById('calc-budget');
  const calcCPM = document.getElementById('calc-cpm');

  if (!impressionsSlider || !durationSlider) return;

  function updateEstimator() {
    const impressions = parseInt(impressionsSlider.value);
    const duration = parseInt(durationSlider.value);

    impressionsVal.textContent = (impressions >= 1000000) 
      ? `${(impressions / 1000000).toFixed(1)} Million`
      : `${(impressions / 1000).toFixed(0)}K`;

    durationVal.textContent = `${duration} Days`;

    let channelMultiplier = 1.0;
    const selectedChannels = document.querySelectorAll('.est-channel-cb:checked');
    selectedChannels.forEach(cb => {
      channelMultiplier += parseFloat(cb.dataset.multiplier || 0);
    });

    const baseCPM = 85;
    const calculatedBudget = Math.round((impressions / 1000) * baseCPM * channelMultiplier * (1 + (duration / 100)));
    const estimatedReach = Math.round(impressions * 0.72);
    const effectiveCPM = (calculatedBudget / (impressions / 1000)).toFixed(1);

    calcReach.textContent = estimatedReach >= 1000000 
      ? `${(estimatedReach / 1000000).toFixed(2)}M People`
      : `${(estimatedReach / 1000).toFixed(0)}K People`;

    calcBudget.textContent = `₹${calculatedBudget.toLocaleString('en-IN')}`;
    calcCPM.textContent = `₹${effectiveCPM}`;
  }

  impressionsSlider.addEventListener('input', updateEstimator);
  durationSlider.addEventListener('input', updateEstimator);
  
  const checkboxes = document.querySelectorAll('.est-channel-cb');
  checkboxes.forEach(cb => cb.addEventListener('change', updateEstimator));

  updateEstimator();
}

// --- 8. Smart Search Modal ---
function initSearchModal() {
  const searchModal = document.getElementById('search-modal');
  const searchInput = document.getElementById('modal-search-input');
  const searchResults = document.getElementById('search-results-list');
  const searchTriggers = document.querySelectorAll('.open-search-modal');
  const closeSearchBtn = document.getElementById('close-search-modal');

  if (!searchModal) return;

  function openModal() {
    searchModal.classList.remove('hidden');
    searchModal.classList.add('flex');
    if (searchInput) searchInput.focus();
    performSearch('');
  }

  function closeModal() {
    searchModal.classList.add('hidden');
    searchModal.classList.remove('flex');
  }

  searchTriggers.forEach(btn => btn.addEventListener('click', openModal));
  if (closeSearchBtn) closeSearchBtn.addEventListener('click', closeModal);

  document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
      e.preventDefault();
      openModal();
    }
    if (e.key === 'Escape' && !searchModal.classList.contains('hidden')) {
      closeModal();
    }
  });

  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      performSearch(e.target.value.toLowerCase().trim());
    });
  }

  function performSearch(query) {
    if (!searchResults) return;

    const matches = MEDIA_DATABASE.filter(item => 
      item.title.toLowerCase().includes(query) ||
      item.location.toLowerCase().includes(query) ||
      item.category.toLowerCase().includes(query)
    );

    if (matches.length === 0) {
      searchResults.innerHTML = `
        <div class="py-8 text-center text-slate-400">
          <i class="fa-solid fa-magnifying-glass text-3xl mb-2 text-brand-red"></i>
          <p class="font-medium text-slate-600">No media matching "${query}"</p>
        </div>
      `;
      return;
    }

    searchResults.innerHTML = matches.map(item => `
      <div class="p-3 hover:bg-slate-100 rounded-xl transition flex items-center justify-between cursor-pointer border border-slate-100" onclick="selectSearchMedia('${item.id}')">
        <div class="flex items-center gap-3">
          <img src="${item.image}" class="w-12 h-12 rounded-lg object-cover">
          <div>
            <h4 class="font-bold text-slate-900 text-sm line-clamp-1">${item.title}</h4>
            <p class="text-xs text-slate-500 font-medium">${item.category} • ${item.location}</p>
          </div>
        </div>
        <div class="text-right">
          <span class="text-sm font-extrabold text-brand-red font-outfit">₹${item.price.toLocaleString('en-IN')}</span>
          <span class="text-[10px] text-slate-400 block">${item.priceUnit}</span>
        </div>
      </div>
    `).join('');
  }
}

function selectSearchMedia(id) {
  const searchModal = document.getElementById('search-modal');
  if (searchModal) {
    searchModal.classList.add('hidden');
    searchModal.classList.remove('flex');
  }
  toggleCartItem(id);
}

// --- 9. Cart Drawer Management ---
function initCartDrawer() {
  const drawer = document.getElementById('cart-drawer');
  const openBtns = document.querySelectorAll('.open-cart-drawer');
  const closeBtn = document.getElementById('close-cart-drawer');

  if (!drawer) return;

  function openDrawer() {
    renderCartDrawerItems();
    drawer.classList.remove('translate-x-full');
  }

  function closeDrawer() {
    drawer.classList.add('translate-x-full');
  }

  openBtns.forEach(btn => btn.addEventListener('click', openDrawer));
  if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
}

function renderCartDrawerItems() {
  const drawerContainer = document.getElementById('cart-drawer-items');
  const drawerTotal = document.getElementById('cart-drawer-total');
  if (!drawerContainer) return;

  if (campaignCart.length === 0) {
    drawerContainer.innerHTML = `
      <div class="py-20 text-center text-slate-400">
        <i class="fa-solid fa-basket-shopping text-5xl text-slate-300 mb-4 block"></i>
        <h4 class="text-lg font-bold text-slate-800 mb-1">Your Campaign Basket is Empty</h4>
        <p class="text-xs text-slate-500 max-w-xs mx-auto">Browse our 300,000+ media options across Offline, Digital, Sports & Gifting to build your plan.</p>
      </div>
    `;
    if (drawerTotal) drawerTotal.textContent = '₹0';
    return;
  }

  const total = campaignCart.reduce((sum, item) => sum + item.price, 0);
  if (drawerTotal) drawerTotal.textContent = `₹${total.toLocaleString('en-IN')}`;

  drawerContainer.innerHTML = campaignCart.map(item => `
    <div class="bg-slate-50 p-3 rounded-xl flex items-center justify-between gap-3 border border-slate-200 shadow-sm">
      <img src="${item.image}" class="w-14 h-14 rounded-lg object-cover">
      <div class="flex-1 min-w-0">
        <h4 class="text-xs font-bold text-slate-900 truncate">${item.title}</h4>
        <span class="text-[10px] text-slate-500 font-medium block">${item.category} • ${item.location}</span>
        <span class="text-xs font-extrabold text-brand-red font-outfit">₹${item.price.toLocaleString('en-IN')} <span class="font-normal text-slate-500 text-[10px]">${item.priceUnit}</span></span>
      </div>
      <button onclick="toggleCartItem('${item.id}')" class="text-slate-400 hover:text-red-600 p-2 text-sm transition">
        <i class="fa-solid fa-trash-can"></i>
      </button>
    </div>
  `).join('');
}

// --- 10. Lead Inquiry Modal ---
function initInquiryModal() {
  const modal = document.getElementById('inquiry-modal');
  const triggers = document.querySelectorAll('.open-inquiry-modal');
  const closeBtn = document.getElementById('close-inquiry-modal');
  const form = document.getElementById('inquiry-form');

  if (!modal) return;

  function openModal() {
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  }

  function closeModal() {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  }

  triggers.forEach(btn => btn.addEventListener('click', openModal));
  if (closeBtn) closeBtn.addEventListener('click', closeModal);

  if (form) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      closeModal();
      showToast('Thank you! Our Media Buying Strategist will call you within 15 minutes with a custom proposal.', 'success');
      form.reset();
    });
  }
}

// --- 11. Toast Notifications ---
function showToast(message, type = 'info') {
  const container = document.getElementById('toast-container');
  if (!container) return;

  const toast = document.createElement('div');
  const bgColor = type === 'success' ? 'bg-emerald-600' : 'bg-brand-red';
  const icon = type === 'success' ? 'fa-circle-check' : 'fa-circle-info';

  toast.className = `${bgColor} text-white text-xs md:text-sm font-semibold px-4 py-3 rounded-xl shadow-2xl flex items-center gap-2.5 transition-all duration-500 transform translate-y-5 opacity-0`;
  toast.innerHTML = `<i class="fa-solid ${icon} text-base"></i> <span>${message}</span>`;

  container.appendChild(toast);

  setTimeout(() => {
    toast.classList.remove('translate-y-5', 'opacity-0');
  }, 10);

  setTimeout(() => {
    toast.classList.add('opacity-0', 'translate-y-2');
    setTimeout(() => toast.remove(), 500);
  }, 4000);
}

// --- 12. Sticky Bar & Scroll Dynamics ---
function initScrollEffects() {
  const bottomBar = document.getElementById('sticky-bottom-bar');

  window.addEventListener('scroll', () => {
    if (!bottomBar) return;
    if (window.scrollY > 300) {
      bottomBar.classList.remove('hide');
    } else {
      bottomBar.classList.add('hide');
    }
  });
}

// --- 13. Standalone Cart Page (cart.html) Logic ---
let isCouponApplied = false;

function initCartPage() {
  const cartContainer = document.getElementById('cart-page-items-list');
  const emptyState = document.getElementById('cart-page-empty-state');
  const countText = document.getElementById('cart-item-count-text');

  if (!cartContainer) return;

  if (countText) countText.textContent = campaignCart.length;

  if (campaignCart.length === 0) {
    cartContainer.innerHTML = '';
    if (emptyState) emptyState.classList.remove('hidden');
    updateCartPageTotals();
    return;
  }

  if (emptyState) emptyState.classList.add('hidden');

  cartContainer.innerHTML = campaignCart.map(item => {
    const qty = item.quantity || 1;
    const itemSubtotal = item.price * qty;

    return `
      <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5 transition hover:shadow-md">
        
        <div class="flex items-center gap-4 min-w-0 flex-1">
          <a href="listing.html" class="w-20 h-20 rounded-2xl overflow-hidden flex-shrink-0 bg-slate-100 border border-slate-200 block">
            <img src="${item.image}" alt="${item.title}" class="w-full h-full object-cover">
          </a>

          <div class="space-y-1 min-w-0">
            <div class="flex items-center gap-2">
              <span class="bg-red-50 text-brand-red text-[10px] uppercase font-extrabold px-2.5 py-0.5 rounded-full border border-red-100">
                ${item.category}
              </span>
              <span class="text-xs text-slate-500 font-medium">${item.location}</span>
            </div>

            <a href="listing.html" class="font-outfit font-bold text-base text-slate-900 truncate block hover:text-brand-red transition">
              ${item.title}
            </a>

            <div class="flex items-center gap-2 text-xs text-slate-500">
              <span>Card Rate: <strong class="text-slate-800 font-outfit">₹${item.price.toLocaleString('en-IN')}</strong> / ${item.priceUnit}</span>
            </div>
          </div>
        </div>

        <!-- Quantity Counter & Total -->
        <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-100">
          
          <div class="flex items-center gap-2">
            <span class="text-[10px] text-slate-400 font-bold uppercase hidden sm:inline">Insertions:</span>
            <div class="flex items-center border border-slate-200 rounded-xl bg-slate-50 p-1">
              <button onclick="updateCartQuantity('${item.id}', -1)" class="w-7 h-7 rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-brand-red hover:text-white flex items-center justify-center text-xs font-bold transition">
                <i class="fa-solid fa-minus text-[10px]"></i>
              </button>
              <span class="w-8 text-center font-outfit font-extrabold text-sm text-slate-900">${qty}</span>
              <button onclick="updateCartQuantity('${item.id}', 1)" class="w-7 h-7 rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-brand-red hover:text-white flex items-center justify-center text-xs font-bold transition">
                <i class="fa-solid fa-plus text-[10px]"></i>
              </button>
            </div>
          </div>

          <div class="text-right">
            <span class="text-[10px] text-slate-400 font-bold uppercase block">Item Subtotal</span>
            <span class="font-outfit font-black text-lg text-slate-900">₹${itemSubtotal.toLocaleString('en-IN')}</span>
          </div>

          <button onclick="toggleCartItem('${item.id}')" class="text-slate-400 hover:text-brand-red p-2 text-base transition">
            <i class="fa-solid fa-trash-can"></i>
          </button>

        </div>

      </div>
    `;
  }).join('');

  updateCartPageTotals();
}

function updateCartQuantity(id, change) {
  const item = campaignCart.find(c => c.id === id);
  if (!item) return;

  item.quantity = (item.quantity || 1) + change;

  if (item.quantity <= 0) {
    toggleCartItem(id);
    return;
  }

  localStorage.setItem('ep_cart', JSON.stringify(campaignCart));
  initCartPage();
}

function clearCartPage() {
  campaignCart = [];
  localStorage.setItem('ep_cart', JSON.stringify(campaignCart));
  updateCartBadge();
  renderCartDrawerItems();
  initCartPage();
  showToast('Cleared all items from campaign plan', 'info');
}

function applyCouponCode() {
  const input = document.getElementById('coupon-input');
  const msg = document.getElementById('coupon-msg');
  if (!input) return;

  const code = input.value.trim().toUpperCase();

  if (code === 'DEKHO10') {
    isCouponApplied = true;
    if (msg) {
      msg.innerHTML = '<span class="text-emerald-600 font-bold">✓ Coupon DEKHO10 applied successfully! 10% discount added.</span>';
    }
    showToast('Promo Code DEKHO10 applied! 10% discount added to your plan.', 'success');
  } else {
    isCouponApplied = false;
    if (msg) {
      msg.innerHTML = '<span class="text-brand-red font-bold">✕ Invalid coupon code. Try DEKHO10 for 10% OFF.</span>';
    }
    showToast('Invalid coupon code. Try DEKHO10.', 'info');
  }

  updateCartPageTotals();
}

function updateCartPageTotals() {
  const subtotalEl = document.getElementById('summary-media-subtotal');
  const boostersEl = document.getElementById('summary-boosters-total');
  const gstEl = document.getElementById('summary-gst-total');
  const grandTotalEl = document.getElementById('summary-grand-total');
  const discountRow = document.getElementById('summary-discount-row');
  const discountVal = document.getElementById('summary-discount-val');

  if (!subtotalEl) return;

  const mediaSubtotal = campaignCart.reduce((sum, item) => sum + (item.price * (item.quantity || 1)), 0);

  let boostersTotal = 0;
  const boosterCBs = document.querySelectorAll('.booster-checkbox:checked');
  boosterCBs.forEach(cb => {
    boostersTotal += parseInt(cb.dataset.price || 0, 10);
  });

  let discount = 0;
  if (isCouponApplied && mediaSubtotal > 0) {
    discount = Math.round(mediaSubtotal * 0.10);
    if (discountRow) discountRow.classList.remove('hidden');
    if (discountRow) discountRow.classList.add('flex');
    if (discountVal) discountVal.textContent = `-₹${discount.toLocaleString('en-IN')}`;
  } else {
    if (discountRow) discountRow.classList.add('hidden');
    if (discountRow) discountRow.classList.remove('flex');
  }

  const netTaxable = (mediaSubtotal - discount) + boostersTotal;
  const gst = Math.round(netTaxable * 0.18);
  const grandTotal = netTaxable + gst;

  if (subtotalEl) subtotalEl.textContent = `₹${mediaSubtotal.toLocaleString('en-IN')}`;
  if (boostersEl) boostersEl.textContent = `₹${boostersTotal.toLocaleString('en-IN')}`;
  if (gstEl) gstEl.textContent = `₹${gst.toLocaleString('en-IN')}`;
  if (grandTotalEl) grandTotalEl.textContent = `₹${grandTotal.toLocaleString('en-IN')}`;
}

