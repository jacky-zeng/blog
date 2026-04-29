<template>
  <el-container class="admin-layout">
    <el-aside width="200px" class="sidebar">
      <div class="logo">
        <h3>博客管理</h3>
      </div>
      <el-menu
        :default-active="activeMenu"
        router
        background-color="#304156"
        text-color="#bfcbd9"
        active-text-color="#409EFF"
      >
        <el-menu-item index="/admin/dashboard">
          <el-icon><DataAnalysis /></el-icon>
          <span>Dashboard</span>
        </el-menu-item>
        <el-menu-item index="/admin/articles">
          <el-icon><Document /></el-icon>
          <span>文章管理</span>
        </el-menu-item>
        <el-menu-item index="/admin/categories">
          <el-icon><Folder /></el-icon>
          <span>分类管理</span>
        </el-menu-item>
        <el-menu-item index="/admin/tags">
          <el-icon><PriceTag /></el-icon>
          <span>标签管理</span>
        </el-menu-item>
        <el-menu-item index="/admin/comments">
          <el-icon><ChatDotRound /></el-icon>
          <span>评论管理</span>
        </el-menu-item>
        <el-menu-item index="/admin/settings">
          <el-icon><Setting /></el-icon>
          <span>系统设置</span>
        </el-menu-item>
      </el-menu>
    </el-aside>
    <el-container>
      <el-header class="header">
        <div class="header-content">
          <div class="breadcrumb">
            <el-breadcrumb separator="/">
              <el-breadcrumb-item :to="{ path: '/admin' }">首页</el-breadcrumb-item>
              <el-breadcrumb-item>{{ currentPage }}</el-breadcrumb-item>
            </el-breadcrumb>
          </div>
          <div class="user-info">
            <el-dropdown @command="handleCommand">
              <span class="el-dropdown-link">
                {{ userStore.user.nickname || userStore.user.username }}
                <el-icon class="el-icon--right"><arrow-down /></el-icon>
              </span>
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item command="logout">退出登录</el-dropdown-item>
                </el-dropdown-menu>
              </template>
            </el-dropdown>
          </div>
        </div>
      </el-header>
      <el-main class="main-content">
        <router-view />
      </el-main>
    </el-container>
  </el-container>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { useUserStore } from '@/stores/user'
import request from '@/utils/request'

const route = useRoute()
const router = useRouter()
const userStore = useUserStore()

const activeMenu = computed(() => route.path)
const currentPage = computed(() => {
  const map = {
    '/admin/dashboard': 'Dashboard',
    '/admin/articles': '文章管理',
    '/admin/categories': '分类管理',
    '/admin/tags': '标签管理',
    '/admin/comments': '评论管理',
    '/admin/settings': '系统设置'
  }
  return map[route.path] || 'Dashboard'
})

const handleCommand = async (command) => {
  if (command === 'logout') {
    try {
      await ElMessageBox.confirm('确定要退出登录吗？', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      })
      await request.post('/admin/logout')
      userStore.logout()
      ElMessage.success('退出成功')
      router.push('/admin/login')
    } catch (error) {
      if (error !== 'cancel') {
        ElMessage.error('退出失败')
      }
    }
  }
}
</script>

<style scoped>
.admin-layout {
  height: 100vh;
}

.sidebar {
  background-color: #304156;
  overflow-x: hidden;
}

.logo {
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 18px;
  font-weight: bold;
  border-bottom: 1px solid #1f2d3d;
}

.header {
  background-color: #fff;
  border-bottom: 1px solid #e6e6e6;
  display: flex;
  align-items: center;
}

.header-content {
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.user-info {
  cursor: pointer;
}

.el-dropdown-link {
  display: flex;
  align-items: center;
  color: #333;
}

.main-content {
  background-color: #f0f2f5;
  padding: 20px;
}
</style>
