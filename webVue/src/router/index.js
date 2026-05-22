import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/admin/login',
    name: 'Login',
    component: () => import('@/views/admin/Login.vue')
  },
  {
    path: '/admin',
    component: () => import('@/layouts/AdminLayout.vue'),
    redirect: '/admin/dashboard',
    children: [
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('@/views/admin/Dashboard.vue')
      },
      {
        path: 'articles',
        name: 'Articles',
        component: () => import('@/views/admin/Articles.vue')
      },
      {
        path: 'articles/create',
        name: 'ArticleCreate',
        component: () => import('@/views/admin/ArticleForm.vue')
      },
      {
        path: 'articles/:id/edit',
        name: 'ArticleEdit',
        component: () => import('@/views/admin/ArticleForm.vue')
      },
      {
        path: 'categories',
        name: 'Categories',
        component: () => import('@/views/admin/Categories.vue')
      },
      {
        path: 'tags',
        name: 'Tags',
        component: () => import('@/views/admin/Tags.vue')
      },
      {
        path: 'comments',
        name: 'Comments',
        component: () => import('@/views/admin/Comments.vue')
      },
      {
        path: 'settings',
        name: 'Settings',
        component: () => import('@/views/admin/Settings.vue')
      }
    ]
  },
  {
    path: '/',
    component: () => import('@/layouts/FrontLayout.vue'),
    children: [
      {
        path: '',
        name: 'Main',
        component: () => import('@/views/front/Main.vue')
      },
      {
        path: 'blog',
        name: 'Blog',
        component: () => import('@/views/front/Home.vue')
      },
      {
        path: 'article/:slug',
        name: 'ArticleDetail',
        component: () => import('@/views/front/ArticleDetail.vue')
      },
      {
        path: 'category/:slug',
        name: 'Category',
        component: () => import('@/views/front/Category.vue')
      },
      {
        path: 'tag/:slug',
        name: 'Tag',
        component: () => import('@/views/front/Tag.vue')
      },
      {
        path: 'archive',
        name: 'Archive',
        component: () => import('@/views/front/Archive.vue')
      },
      {
        path: 'about',
        name: 'About',
        component: () => import('@/views/front/About.vue')
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  
  if (to.path.startsWith('/admin') && to.path !== '/admin/login' && !token) {
    next('/admin/login')
  } else if (to.path === '/admin/login' && token) {
    next('/admin/dashboard')
  } else {
    next()
  }
})

export default router
