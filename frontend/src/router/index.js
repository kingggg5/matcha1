import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

// Public Views
import Home from '../views/Home.vue'
import Products from '../views/Products.vue'
import ProductDetail from '../views/ProductDetail.vue'
import Cart from '../views/Cart.vue'
import Login from '../views/Login.vue'
import Register from '../views/Register.vue'

import Checkout from '../views/Checkout.vue'

// Admin Views
import AdminDashboard from '../views/admin/Dashboard.vue'
import AdminProducts from '../views/admin/ProductList.vue'
import AdminProductForm from '../views/admin/ProductForm.vue'
import AdminCategories from '../views/admin/CategoryList.vue'
import AdminCategoryForm from '../views/admin/CategoryForm.vue'

const routes = [
    // Public Routes
    {
        path: '/',
        name: 'Home',
        component: Home
    },
    {
        path: '/products',
        name: 'Products',
        component: Products
    },
    {
        path: '/products/:id',
        name: 'ProductDetail',
        component: ProductDetail
    },
    {
        path: '/cart',
        name: 'Cart',
        component: Cart
    },
    {
        path: '/checkout',
        name: 'Checkout',
        component: Checkout,
        meta: { requiresAuth: true }
    },
    {
        path: '/login',
        name: 'Login',
        component: Login
    },
    {
        path: '/register',
        name: 'Register',
        component: Register
    },
    {
        path: '/wishlist',
        name: 'Wishlist',
        component: () => import('../views/Wishlist.vue')
    },
    {
        path: '/my-orders',
        name: 'MyOrders',
        component: () => import('../views/MyOrders.vue')
    },

    // Admin Routes
    {
        path: '/admin',
        name: 'AdminDashboard',
        component: AdminDashboard,
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/admin/products',
        name: 'AdminProducts',
        component: AdminProducts,
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/admin/products/new',
        name: 'AdminProductNew',
        component: AdminProductForm,
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/admin/products/:id/edit',
        name: 'AdminProductEdit',
        component: AdminProductForm,
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/admin/categories',
        name: 'AdminCategories',
        component: AdminCategories,
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/admin/categories/new',
        name: 'AdminCategoryNew',
        component: AdminCategoryForm,
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/admin/categories/:id/edit',
        name: 'AdminCategoryEdit',
        component: AdminCategoryForm,
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/admin/orders',
        name: 'AdminOrders',
        component: () => import('../views/admin/OrderList.vue'),
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/admin/coupons',
        name: 'AdminCoupons',
        component: () => import('../views/admin/CouponList.vue'),
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/admin/coupons/new',
        name: 'AdminCouponNew',
        component: () => import('../views/admin/CouponForm.vue'),
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/admin/coupons/:id/edit',
        name: 'AdminCouponEdit',
        component: () => import('../views/admin/CouponForm.vue'),
        meta: { requiresAuth: true, requiresAdmin: true }
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition
        }
        return { top: 0 }
    }
})

// Navigation Guard
router.beforeEach((to, from, next) => {
    const authStore = useAuthStore()

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next({ name: 'Login', query: { redirect: to.fullPath } })
    } else if (to.meta.requiresAdmin && !authStore.isAdmin) {
        next({ name: 'Home' })
    } else {
        next()
    }
})

export default router
