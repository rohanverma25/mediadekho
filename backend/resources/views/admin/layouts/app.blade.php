<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Media Dekho Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.11/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <link href="{{ asset('css/admin/theme.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body>
    @php
        $navInventoryActive = request()->routeIs('admin.categories.*', 'admin.subcategories.*', 'admin.media-inventory.*', 'admin.frequencies.*', 'admin.languages.*');
        $navCmsActive = request()->routeIs('admin.faqs.*', 'admin.blogs.*', 'admin.news.*', 'admin.awards.*', 'admin.jobs.*', 'admin.client-logos.*', 'admin.industries.*', 'admin.stats.*', 'admin.videos.*', 'admin.announcements.*', 'admin.page-meta.*');
        $navEnquiriesActive = request()->routeIs('admin.leads.*', 'admin.award-nominations.*', 'admin.job-applications.*');
        $navOrdersActive = request()->routeIs('admin.customers.*', 'admin.orders.*');
    @endphp
    <div class="admin-sidebar d-flex flex-column" id="adminSidebar">
        <a href="{{ route('admin.dashboard') }}" class="brand">Media Dekho</a>
        <nav class="nav flex-column py-2">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            {{-- MEDIA INVENTORY --}}
            <a href="#navGroupInventory" class="nav-link nav-group-toggle {{ $navInventoryActive ? 'active' : '' }}" data-bs-toggle="collapse" role="button" aria-expanded="{{ $navInventoryActive ? 'true' : 'false' }}" aria-controls="navGroupInventory">
                <span><i class="bi bi-collection-play"></i> Media Inventory</span>
                <i class="bi bi-chevron-down nav-group-caret"></i>
            </a>
            <div class="collapse {{ $navInventoryActive ? 'show' : '' }}" id="navGroupInventory">
                <div class="nav flex-column nav-group">
                    <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                        <i class="bi bi-diagram-3"></i> Category
                    </a>
                    <a href="{{ route('admin.subcategories.index') }}" class="nav-link {{ request()->routeIs('admin.subcategories.*') ? 'active' : '' }}">
                        <i class="bi bi-diagram-2"></i> Sub Category
                    </a>
                    <a href="{{ route('admin.media-inventory.index') }}" class="nav-link {{ request()->routeIs('admin.media-inventory.*') ? 'active' : '' }}">
                        <i class="bi bi-collection"></i> Inventory
                    </a>
                    <a href="{{ route('admin.frequencies.index') }}" class="nav-link {{ request()->routeIs('admin.frequencies.*') ? 'active' : '' }}">
                        <i class="bi bi-arrow-repeat"></i> Frequency
                    </a>
                    <a href="{{ route('admin.languages.index') }}" class="nav-link {{ request()->routeIs('admin.languages.*') ? 'active' : '' }}">
                        <i class="bi bi-translate"></i> Language
                    </a>
                </div>
            </div>

            {{-- CMS --}}
            <a href="#navGroupCms" class="nav-link nav-group-toggle {{ $navCmsActive ? 'active' : '' }}" data-bs-toggle="collapse" role="button" aria-expanded="{{ $navCmsActive ? 'true' : 'false' }}" aria-controls="navGroupCms">
                <span><i class="bi bi-layout-text-window"></i> CMS</span>
                <i class="bi bi-chevron-down nav-group-caret"></i>
            </a>
            <div class="collapse {{ $navCmsActive ? 'show' : '' }}" id="navGroupCms">
                <div class="nav flex-column nav-group">
                    <a href="{{ route('admin.faqs.index') }}" class="nav-link {{ request()->routeIs('admin.faqs.*') ? 'active' : '' }}">
                        <i class="bi bi-question-circle"></i> FAQs
                    </a>
                    <a href="{{ route('admin.blogs.index') }}" class="nav-link {{ request()->routeIs('admin.blogs.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text"></i> Blogs
                    </a>
                    <a href="{{ route('admin.news.index') }}" class="nav-link {{ request()->routeIs('admin.news.*') ? 'active' : '' }}">
                        <i class="bi bi-newspaper"></i> News
                    </a>
                    <a href="{{ route('admin.awards.index') }}" class="nav-link {{ request()->routeIs('admin.awards.*') ? 'active' : '' }}">
                        <i class="bi bi-trophy"></i> Awards
                    </a>
                    <a href="{{ route('admin.jobs.index') }}" class="nav-link {{ request()->routeIs('admin.jobs.*') ? 'active' : '' }}">
                        <i class="bi bi-briefcase"></i> Careers
                    </a>
                    <a href="{{ route('admin.client-logos.index') }}" class="nav-link {{ request()->routeIs('admin.client-logos.*') ? 'active' : '' }}">
                        <i class="bi bi-badge-tm"></i> Client Logos
                    </a>
                    <a href="{{ route('admin.industries.index') }}" class="nav-link {{ request()->routeIs('admin.industries.*') ? 'active' : '' }}">
                        <i class="bi bi-buildings"></i> Industries
                    </a>
                    <a href="{{ route('admin.stats.index') }}" class="nav-link {{ request()->routeIs('admin.stats.*') ? 'active' : '' }}">
                        <i class="bi bi-bar-chart-line"></i> Stats
                    </a>
                    <a href="{{ route('admin.videos.index') }}" class="nav-link {{ request()->routeIs('admin.videos.*') ? 'active' : '' }}">
                        <i class="bi bi-youtube"></i> Videos
                    </a>
                    <a href="{{ route('admin.announcements.index') }}" class="nav-link {{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}">
                        <i class="bi bi-megaphone"></i> Announcements
                    </a>
                    <a href="{{ route('admin.page-meta.index') }}" class="nav-link {{ request()->routeIs('admin.page-meta.*') ? 'active' : '' }}">
                        <i class="bi bi-tags"></i> Meta Tags
                    </a>
                </div>
            </div>

            {{-- ENQUIRIES --}}
            <a href="#navGroupEnquiries" class="nav-link nav-group-toggle {{ $navEnquiriesActive ? 'active' : '' }}" data-bs-toggle="collapse" role="button" aria-expanded="{{ $navEnquiriesActive ? 'true' : 'false' }}" aria-controls="navGroupEnquiries">
                <span><i class="bi bi-inboxes"></i> Enquiries</span>
                <i class="bi bi-chevron-down nav-group-caret"></i>
            </a>
            <div class="collapse {{ $navEnquiriesActive ? 'show' : '' }}" id="navGroupEnquiries">
                <div class="nav flex-column nav-group">
                    <a href="{{ route('admin.leads.index') }}" class="nav-link {{ request()->routeIs('admin.leads.*') ? 'active' : '' }}">
                        <i class="bi bi-envelope-open"></i> Contact Leads
                    </a>
                    <a href="{{ route('admin.award-nominations.index') }}" class="nav-link {{ request()->routeIs('admin.award-nominations.*') ? 'active' : '' }}">
                        <i class="bi bi-person-check"></i> Award Nominations
                    </a>
                    <a href="{{ route('admin.job-applications.index') }}" class="nav-link {{ request()->routeIs('admin.job-applications.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-person"></i> Job Applications
                    </a>
                </div>
            </div>

            {{-- ORDERS --}}
            <a href="#navGroupOrders" class="nav-link nav-group-toggle {{ $navOrdersActive ? 'active' : '' }}" data-bs-toggle="collapse" role="button" aria-expanded="{{ $navOrdersActive ? 'true' : 'false' }}" aria-controls="navGroupOrders">
                <span><i class="bi bi-receipt"></i> Orders</span>
                <i class="bi bi-chevron-down nav-group-caret"></i>
            </a>
            <div class="collapse {{ $navOrdersActive ? 'show' : '' }}" id="navGroupOrders">
                <div class="nav flex-column nav-group">
                    <a href="{{ route('admin.customers.index') }}" class="nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i> Customers
                    </a>
                    <a href="{{ route('admin.orders.index') }}" class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                        <i class="bi bi-receipt"></i> Orders
                    </a>
                </div>
            </div>

            <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <i class="bi bi-gear"></i> Settings
            </a>
            <a href="{{ route('admin.activity-log.index') }}" class="nav-link {{ request()->routeIs('admin.activity-log.*') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i> Activity Log
            </a>
        </nav>
    </div>

    <div class="admin-main">
        <nav class="admin-topbar navbar navbar-expand px-3 py-2">
            <button class="btn btn-sm btn-outline-secondary d-lg-none me-2" id="sidebarToggle" type="button">
                <i class="bi bi-list"></i>
            </button>
            <span class="navbar-text fw-semibold">@yield('title', 'Dashboard')</span>
            <div class="ms-auto d-flex align-items-center gap-3">
                <span class="text-muted small">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>
        </nav>

        <main class="p-4">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.11/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.11/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        document.getElementById('sidebarToggle')?.addEventListener('click', function () {
            document.getElementById('adminSidebar').classList.toggle('show');
        });
    </script>

    @stack('scripts')
</body>
</html>
