<script setup>
import { ref } from 'vue';
import { Link, Head, usePage } from '@inertiajs/vue3';

const isSidebarOpen = ref(true);
const page = usePage();

function toggleSidebar() {
    isSidebarOpen.value = !isSidebarOpen.value;
}
</script>

<template>
    <div class="hold-transition sidebar-mini layout-fixed" :class="{ 'sidebar-collapse': !isSidebarOpen }">
        <Head>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
            <link v-if="$page.props.webSettings?.favicon" rel="icon" :href="$page.props.webSettings.favicon">
        </Head>

        <div class="wrapper">
            <!-- Navbar -->
            <nav class="main-header navbar navbar-expand navbar-white navbar-light border-bottom sticky-top">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="#" @click.prevent="toggleSidebar"><i class="fas fa-bars text-muted"></i></a>
                    </li>
                    <li class="nav-item d-none d-sm-inline-block">
                        <Link :href="route('home')" class="nav-link text-sm font-medium"><i class="fas fa-external-link-alt mr-1"></i> Storefront</Link>
                    </li>
                </ul>

                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown px-3">
                        <div class="d-flex align-items-center">
                            <div class="text-right mr-3 d-none d-sm-block">
                                <span class="d-block text-xs font-semibold text-primary">{{ $page.props.auth.user.name }}</span>
                                <span class="text-xs text-muted">Administrator</span>
                            </div>
                            <img src="https://ui-avatars.com/api/?background=6366f1&color=fff&name=Admin" class="rounded-lg shadow-sm" style="width: 32px;" alt="User Avatar">
                        </div>
                    </li>
                    <li class="nav-item border-left ml-2">
                        <Link :href="route('logout')" method="post" as="button" class="nav-link text-danger border-0 bg-transparent">
                            <i class="fas fa-sign-out-alt"></i>
                        </Link>
                    </li>
                </ul>
            </nav>

            <!-- Sidebar -->
            <aside class="main-sidebar sidebar-light-indigo elevation-0 border-right">
                <Link :href="route('dashboard')" class="brand-link border-bottom py-3 px-4">
                    <img :src="$page.props.webSettings?.logo || '/images/godiva/logo.png'" alt="Logo" class="brand-image" style="float: none; max-height: 30px;">
                    <span class="brand-text font-bold ml-2 text-dark">{{ $page.props.webSettings?.site_name || 'ERP Admin' }}</span>
                </Link>

                <div class="sidebar">
                    <nav class="mt-4 px-2">
                        <ul class="nav nav-pills nav-sidebar flex-column nav-flat" data-widget="treeview" role="menu">
                            <li class="nav-item">
                                <Link :href="route('dashboard')" class="nav-link" :class="{ active: $page.component === 'Dashboard' }">
                                    <i class="nav-icon fas fa-th-large"></i>
                                    <p>Dashboard</p>
                                </Link>
                            </li>
                            
                            <li class="nav-item mt-3">
                                <Link :href="route('admin.pos.index')" class="nav-link bg-primary-soft" :class="{ active: $page.component === 'Admin/POS/Index' }">
                                    <i class="nav-icon fas fa-bolt"></i>
                                    <p class="font-semibold">Quick POS</p>
                                </Link>
                            </li>

                            <li class="nav-header text-uppercase text-xs font-bold text-muted tracking-widest mt-4">Inventory</li>
                            <li class="nav-item">
                                <Link :href="route('admin.products.index')" class="nav-link" :class="{ active: $page.component.startsWith('Admin/Products') }">
                                    <i class="nav-icon fas fa-box text-emerald-500 shadow-icon"></i>
                                    <p>Products</p>
                                </Link>
                            </li>
                            <li class="nav-item">
                                <Link :href="route('admin.categories.index')" class="nav-link" :class="{ active: $page.component.startsWith('Admin/Categories') }">
                                    <i class="nav-icon fas fa-tags text-emerald-400"></i>
                                    <p>Categories</p>
                                </Link>
                            </li>
                            <li class="nav-item">
                                <Link :href="route('admin.brands.index')" class="nav-link" :class="{ active: $page.component.startsWith('Admin/Brands') }">
                                    <i class="nav-icon fas fa-copyright text-emerald-300"></i>
                                    <p>Brands</p>
                                </Link>
                            </li>
                            <li class="nav-item">
                                <Link :href="route('admin.units.index')" class="nav-link" :class="{ active: $page.component.startsWith('Admin/Units') }">
                                    <i class="nav-icon fas fa-balance-scale text-emerald-200"></i>
                                    <p>Units</p>
                                </Link>
                            </li>

                            <li class="nav-header text-uppercase text-xs font-bold text-muted tracking-widest mt-4">Operations</li>
                            <li class="nav-item">
                                <Link :href="route('admin.sales.index')" class="nav-link" :class="{ active: $page.component.startsWith('Admin/Sales') }">
                                    <i class="nav-icon fas fa-shopping-cart text-blue-400"></i>
                                    <p>Sales Records</p>
                                </Link>
                            </li>
                            <li class="nav-item">
                                <Link :href="route('admin.purchases.index')" class="nav-link" :class="{ active: $page.component.startsWith('Admin/Purchases') }">
                                    <i class="nav-icon fas fa-truck-loading text-blue-500"></i>
                                    <p>Purchases</p>
                                </Link>
                            </li>
                            <li class="nav-item">
                                <Link :href="route('admin.suppliers.index')" class="nav-link" :class="{ active: $page.component.startsWith('Admin/Suppliers') }">
                                    <i class="nav-icon fas fa-id-badge text-blue-300"></i>
                                    <p>Suppliers</p>
                                </Link>
                            </li>

                            <li class="nav-header text-uppercase text-xs font-bold text-muted tracking-widest mt-4">Reports</li>
                            <li class="nav-item">
                                <Link :href="route('admin.reports.index')" class="nav-link" :class="{ active: $page.component.startsWith('Admin/Reports') }">
                                    <i class="nav-icon fas fa-chart-line text-amber-400"></i>
                                    <p>Analytics Dashboard</p>
                                </Link>
                            </li>
                            <li class="nav-item">
                                <Link :href="route('admin.expenses.index')" class="nav-link" :class="{ active: $page.component.startsWith('Admin/Expenses') }">
                                    <i class="nav-icon fas fa-receipt text-rose-400"></i>
                                    <p>Expenses</p>
                                </Link>
                            </li>

                            <li class="nav-header text-uppercase text-xs font-bold text-muted tracking-widest mt-4">Site Manager</li>
                            <li class="nav-item">
                                <Link :href="route('admin.menus.index')" class="nav-link" :class="{ active: $page.component.startsWith('Admin/Menus') }">
                                    <i class="nav-icon fas fa-stream text-indigo-400"></i>
                                    <p>Navigation</p>
                                </Link>
                            </li>
                            <li class="nav-item">
                                <Link :href="route('admin.pages.index')" class="nav-link" :class="{ active: $page.component.startsWith('Admin/Pages') }">
                                    <i class="nav-icon fas fa-file-invoice text-indigo-500"></i>
                                    <p>Static Pages</p>
                                </Link>
                            </li>
                            <li class="nav-item">
                                <Link :href="route('admin.sliders.index')" class="nav-link" :class="{ active: $page.component === 'Admin/Sliders/Index' }">
                                    <i class="nav-icon fas fa-image text-cyan-400"></i>
                                    <p>Carousel Sliders</p>
                                </Link>
                            </li>

                            <li class="nav-header text-uppercase text-xs font-bold text-muted tracking-widest mt-4">Settings</li>
                            <li class="nav-item">
                                <Link :href="route('admin.settings.index')" class="nav-link" :class="{ active: $page.component.startsWith('Admin/Settings') }">
                                    <i class="nav-icon fas fa-cog text-slate-400"></i>
                                    <p>Global Settings</p>
                                </Link>
                            </li>
                            <li class="nav-item mb-5">
                                <Link :href="route('admin.roles.index')" class="nav-link" :class="{ active: $page.component.startsWith('Admin/Roles') }">
                                    <i class="nav-icon fas fa-user-lock text-slate-500"></i>
                                    <p>User Permissions</p>
                                </Link>
                            </li>
                        </ul>
                    </nav>
                </div>
            </aside>

            <!-- Content Wrapper -->
            <div class="content-wrapper bg-light-gray h-min-screen">
                <main class="p-4">
                    <slot />
                </main>
            </div>

            <!-- Footer -->
            <footer class="main-footer text-xs py-3 border-top bg-white">
                <div class="float-right text-muted">Version 2.0.1</div>
                <span class="text-muted font-medium">© 2026 Admin Portal. Powering {{ $page.props.webSettings?.site_name || 'Business' }}</span>
            </footer>
        </div>
    </div>
</template>

<style>
:root {
    --primary: #6366f1;
    --primary-hover: #4f46e5;
    --primary-soft: #f5f3ff;
    --dark: #0f172a;
    --sidebar-bg: #1e293b;
    --light-gray: #f8fafc;
    --border: #f1f5f9;
    --gradient-primary: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
}

body {
    font-family: 'Inter', sans-serif !important;
    background-color: var(--light-gray);
    color: #475569;
    -webkit-font-smoothing: antialiased;
}

.h-min-screen { min-height: 100vh !important; }
.bg-light-gray { background-color: var(--light-gray) !important; }

/* Sidebar Premium Overhaul */
.main-sidebar { 
    background: var(--sidebar-bg) !important;
    border-right: none !important;
    box-shadow: 10px 0 30px rgba(0, 0, 0, 0.05) !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.brand-link { 
    border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important; 
    padding: 1.5rem 1rem !important;
}

.brand-text { 
    color: #ffffff !important;
    letter-spacing: 0.05rem;
}

.sidebar { padding: 0 0.75rem; }

.nav-sidebar .nav-link {
    border-radius: 12px;
    margin-bottom: 4px;
    padding: 0.75rem 1rem;
    font-size: 0.85rem;
    font-weight: 500;
    color: #94a3b8 !important;
    transition: all 0.2s ease;
}

.nav-sidebar .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.05);
    color: #ffffff !important;
    transform: translateX(4px);
}

.nav-sidebar .nav-link.active {
    background: var(--gradient-primary) !important;
    color: #ffffff !important;
    box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3) !important;
}

.nav-icon {
    font-size: 1rem !important;
    width: 1.5rem !important;
    text-align: center;
    margin-right: 0.75rem !important;
}

.nav-header {
    color: #64748b !important;
    font-size: 0.7rem !important;
    padding: 1.75rem 1rem 0.75rem !important;
    letter-spacing: 0.1rem;
}

/* Navbar */
.main-header { 
    background-color: rgba(255, 255, 255, 0.8) !important;
    backdrop-filter: blur(10px);
    border-bottom: 1px solid var(--border) !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02) !important;
}

/* Page Components */
.card {
    border: none !important;
    border-radius: 16px !important;
    box-shadow: 0 4px 20px 0 rgba(0, 0, 0, 0.03) !important;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover { transform: translateY(-2px); box-shadow: 0 10px 30px 0 rgba(0, 0, 0, 0.06) !important; }

.card-header { 
    background-color: transparent !important;
    border-bottom: 1px solid var(--border) !important;
    padding: 1.5rem !important;
}

.card-title { font-weight: 700 !important; color: var(--dark); font-size: 1rem !important; }

.btn {
    border-radius: 10px !important;
    font-weight: 600 !important;
    padding: 0.6rem 1.5rem !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

.btn-primary { 
    background: var(--gradient-primary) !important;
    border: none !important;
    box-shadow: 0 4px 10px rgba(99, 102, 241, 0.2) !important;
}

.btn-primary:hover { 
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4) !important;
    transform: translateY(-1px);
}

/* Premium Table Global Support */
.table-valign-middle td, 
.table-valign-middle th {
    vertical-align: middle !important;
}

.text-xs { font-size: 0.75rem !important; }
.text-sm { font-size: 0.85rem !important; }
.font-semibold { font-weight: 600 !important; }
.font-bold { font-weight: 700 !important; }

/* Custom Badge Refinements */
.badge {
    border-radius: 6px;
    padding: 0.4em 0.7em;
    font-weight: 600;
    font-size: 0.75rem;
    letter-spacing: 0.02em;
}

.badge-success { background-color: #ecfdf5 !important; color: #059669 !important; border: 1px solid rgba(5, 150, 105, 0.1); }
.badge-danger { background-color: #fef2f2 !important; color: #dc2626 !important; border: 1px solid rgba(220, 38, 38, 0.1); }
.badge-info { background-color: #eff6ff !important; color: #2563eb !important; border: 1px solid rgba(37, 99, 235, 0.1); }
.badge-warning { background-color: #fffbeb !important; color: #d97706 !important; border: 1px solid rgba(217, 119, 6, 0.1); }
.badge-secondary { background-color: #f8fafc !important; color: #64748b !important; border: 1px solid rgba(100, 116, 139, 0.1); }

/* Premium Card Outline */
.card-outline.card-primary { border-top: 3px solid var(--primary) !important; }

/* Shadow Icons */
.shadow-icon {
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
}
</style>


