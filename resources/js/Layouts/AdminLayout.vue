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
                                    <i class="nav-icon fas fa-box"></i>
                                    <p>Products</p>
                                </Link>
                            </li>
                            <li class="nav-item">
                                <Link :href="route('admin.categories.index')" class="nav-link" :class="{ active: $page.component.startsWith('Admin/Categories') }">
                                    <i class="nav-icon fas fa-tags"></i>
                                    <p>Categories</p>
                                </Link>
                            </li>

                            <li class="nav-header text-uppercase text-xs font-bold text-muted tracking-widest mt-4">Operations</li>
                            <li class="nav-item">
                                <Link :href="route('admin.sales.index')" class="nav-link" :class="{ active: $page.component.startsWith('Admin/Sales') }">
                                    <i class="nav-icon fas fa-shopping-cart"></i>
                                    <p>Sales Records</p>
                                </Link>
                            </li>
                            <li class="nav-item">
                                <Link :href="route('admin.purchases.index')" class="nav-link" :class="{ active: $page.component.startsWith('Admin/Purchases') }">
                                    <i class="nav-icon fas fa-truck-loading"></i>
                                    <p>Purchases</p>
                                </Link>
                            </li>
                            <li class="nav-item">
                                <Link :href="route('admin.suppliers.index')" class="nav-link" :class="{ active: $page.component.startsWith('Admin/Suppliers') }">
                                    <i class="nav-icon fas fa-id-badge"></i>
                                    <p>Suppliers</p>
                                </Link>
                            </li>

                            <li class="nav-header text-uppercase text-xs font-bold text-muted tracking-widest mt-4">Reports</li>
                            <li class="nav-item">
                                <Link :href="route('admin.reports.index')" class="nav-link" :class="{ active: $page.component.startsWith('Admin/Reports') }">
                                    <i class="nav-icon fas fa-chart-line"></i>
                                    <p>Analytics Dashboard</p>
                                </Link>
                            </li>
                            <li class="nav-item">
                                <Link :href="route('admin.expenses.index')" class="nav-link" :class="{ active: $page.component.startsWith('Admin/Expenses') }">
                                    <i class="nav-icon fas fa-receipt"></i>
                                    <p>Expenses</p>
                                </Link>
                            </li>

                            <li class="nav-header text-uppercase text-xs font-bold text-muted tracking-widest mt-4">Site Manager</li>
                            <li class="nav-item">
                                <Link :href="route('admin.menus.index')" class="nav-link" :class="{ active: $page.component.startsWith('Admin/Menus') }">
                                    <i class="nav-icon fas fa-stream"></i>
                                    <p>Navigation</p>
                                </Link>
                            </li>
                            <li class="nav-item">
                                <Link :href="route('admin.pages.index')" class="nav-link" :class="{ active: $page.component.startsWith('Admin/Pages') }">
                                    <i class="nav-icon fas fa-file-invoice"></i>
                                    <p>Static Pages</p>
                                </Link>
                            </li>
                            <li class="nav-item">
                                <Link :href="route('admin.sliders.index')" class="nav-link" :class="{ active: $page.component === 'Admin/Sliders/Index' }">
                                    <i class="nav-icon fas fa-image"></i>
                                    <p>Carousel Sliders</p>
                                </Link>
                            </li>

                            <li class="nav-header text-uppercase text-xs font-bold text-muted tracking-widest mt-4">Settings</li>
                            <li class="nav-item">
                                <Link :href="route('admin.settings.index')" class="nav-link" :class="{ active: $page.component.startsWith('Admin/Settings') }">
                                    <i class="nav-icon fas fa-cog"></i>
                                    <p>Global Settings</p>
                                </Link>
                            </li>
                            <li class="nav-item mb-5">
                                <Link :href="route('admin.roles.index')" class="nav-link" :class="{ active: $page.component.startsWith('Admin/Roles') }">
                                    <i class="nav-icon fas fa-user-lock"></i>
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
    --primary: #4f46e5;
    --primary-light: #eef2ff;
    --dark: #1e293b;
    --light-gray: #f8fafc;
    --border: #e2e8f0;
}

body {
    font-family: 'Inter', sans-serif !important;
    color: var(--dark);
    -webkit-font-smoothing: antialiased;
}

.h-min-screen { min-height: 100vh !important; }
.bg-light-gray { background-color: var(--light-gray) !important; }

/* Sidebar Reset */
.main-sidebar { background-color: #ffffff !important; box-shadow: none !important; }
.brand-link { border-bottom: 1px solid var(--border) !important; }
.brand-text { font-weight: 700 !important; font-size: 0.95rem; }

.nav-sidebar .nav-link {
    border-radius: 8px;
    margin-bottom: 2px;
    padding: 0.6rem 1rem;
    font-size: 0.825rem;
    font-weight: 500;
    color: #475569 !important;
}

.nav-sidebar .nav-link:hover {
    background-color: #f1f5f9;
    color: var(--primary) !important;
}

.nav-sidebar .nav-link.active {
    background-color: var(--primary) !important;
    color: #ffffff !important;
    box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
}

.bg-primary-soft {
    background-color: var(--primary-light) !important;
    color: var(--primary) !important;
}

/* Navbar */
.main-header { box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05) !important; padding: 0.5rem 1rem; }
.navbar-nav .nav-link { color: #64748b; font-size: 0.875rem; }

/* Custom Overrides */
.card {
    border: 1px solid var(--border) !important;
    border-radius: 12px !important;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
}

.card-header { background-color: transparent; border-bottom: 1px solid var(--border); padding: 1.25rem; }
.card-title { font-weight: 600; font-size: 0.95rem; }

.btn {
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.875rem;
    padding: 0.5rem 1.25rem;
    transition: all 0.2s;
}

.btn-primary { background-color: var(--primary); border-color: var(--primary); }
.btn-primary:hover { background-color: #4338ca; }

.badge {
    padding: 0.4em 0.8em;
    font-weight: 600;
    border-radius: 6px;
}

/* AdminLTE Nav UI Overrides */
.sidebar-light-indigo .nav-sidebar > .nav-item > .nav-link.active {
    background-color: var(--primary) !important;
}

.nav-header { padding: 1.5rem 1rem 0.5rem !important; }

/* Scrollbar */
::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
::-webkit-scrollbar-track { background: transparent; }
</style>


