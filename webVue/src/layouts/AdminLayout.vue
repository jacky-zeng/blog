<template>
  <div class="admin-layout-container">
    <div class="admin-layout-wrapper" :style="{ transform: `scale(${zoomPercent / 100})`, transformOrigin: 'top left', width: `${100 / (zoomPercent / 100)}vw`, height: `${100 / (zoomPercent / 100)}vh` }">
      <el-container class="admin-layout">
        <el-aside :width="isCollapse ? '64px' : '200px'" class="sidebar">
          <div class="logo">
            <h3 v-if="!isCollapse">博客管理</h3>
            <h3 v-else>博客</h3>
          </div>
          <el-menu
            :default-active="activeMenu"
            :collapse="isCollapse"
            router
            background-color="#304156"
            text-color="#bfcbd9"
            active-text-color="#409EFF"
          >
            <el-menu-item index="/admin/dashboard">
              <el-icon><DataAnalysis /></el-icon>
              <template #title>Dashboard</template>
            </el-menu-item>
            <el-menu-item index="/admin/articles">
              <el-icon><Document /></el-icon>
              <template #title>文章管理</template>
            </el-menu-item>
            <el-menu-item index="/admin/categories">
              <el-icon><Folder /></el-icon>
              <template #title>分类管理</template>
            </el-menu-item>
            <el-menu-item index="/admin/tags">
              <el-icon><PriceTag /></el-icon>
              <template #title>标签管理</template>
            </el-menu-item>
            <el-menu-item index="/admin/comments">
              <el-icon><ChatDotRound /></el-icon>
              <template #title>评论管理</template>
            </el-menu-item>
            <el-menu-item index="/admin/settings">
              <el-icon><Setting /></el-icon>
              <template #title>系统设置</template>
            </el-menu-item>
          </el-menu>
        </el-aside>
        <el-container>
          <el-header class="header">
            <div class="header-content">
              <div class="header-left">
                <el-icon class="collapse-icon" @click="toggleCollapse">
                  <Fold v-if="!isCollapse" />
                  <Expand v-else />
                </el-icon>
                <div class="breadcrumb">
                  <el-breadcrumb separator="/">
                    <el-breadcrumb-item :to="{ path: '/admin' }">首页</el-breadcrumb-item>
                    <el-breadcrumb-item>{{ currentPage }}</el-breadcrumb-item>
                  </el-breadcrumb>
                </div>
              </div>
              <div class="user-info">
                <el-dropdown @command="handleCommand">
                  <span class="el-dropdown-link">
                    {{ userStore.user.nickname || userStore.user.username }}
                    <el-icon class="el-icon--right"><arrow-down /></el-icon>
                  </span>
                  <template #dropdown>
                    <el-dropdown-menu>
                      <el-dropdown-item command="changePassword">修改密码</el-dropdown-item>
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
    </div>

    <el-dialog v-model="passwordDialogVisible" title="修改密码" width="400px">
      <el-form :model="passwordForm" :rules="passwordRules" ref="passwordFormRef" label-width="100px">
        <el-form-item label="原密码" prop="oldPassword">
          <el-input v-model="passwordForm.oldPassword" type="password" placeholder="请输入原密码" show-password />
        </el-form-item>
        <el-form-item label="新密码" prop="newPassword">
          <el-input v-model="passwordForm.newPassword" type="password" placeholder="请输入新密码" show-password />
        </el-form-item>
        <el-form-item label="确认密码" prop="confirmPassword">
          <el-input v-model="passwordForm.confirmPassword" type="password" placeholder="请再次输入新密码" show-password />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="passwordDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleChangePassword">确定</el-button>
      </template>
    </el-dialog>

    <div class="zoom-control">
      <button class="zoom-btn zoom-in" @click="zoomIn" :disabled="zoomPercent >= 100" title="放大">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="12" y1="5" x2="12" y2="19"/>
          <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
      </button>
      <div class="zoom-display">{{ zoomPercent }}%</div>
      <button class="zoom-btn zoom-out" @click="zoomOut" :disabled="zoomPercent <= 10" title="缩小">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Fold, Expand } from '@element-plus/icons-vue'
import { useUserStore } from '@/stores/user'
import request from '@/utils/request'

const route = useRoute()
const router = useRouter()
const userStore = useUserStore()

const isCollapse = ref(false)

const zoomPercent = ref(100)

const zoomIn = () => {
  if (zoomPercent.value < 100) {
    zoomPercent.value = Math.min(zoomPercent.value + 10, 100)
  }
}

const zoomOut = () => {
  if (zoomPercent.value > 10) {
    zoomPercent.value = Math.max(zoomPercent.value - 10, 10)
  }
}

watch(zoomPercent, (newVal) => {
  localStorage.setItem('admin_zoom_percent', String(newVal))
})

onMounted(() => {
  const saved = localStorage.getItem('admin_zoom_percent')
  if (saved) {
    const val = parseInt(saved)
    if (val >= 10 && val <= 100) {
      zoomPercent.value = val
    }
  }
})

const toggleCollapse = () => {
  isCollapse.value = !isCollapse.value
}

const passwordDialogVisible = ref(false)
const passwordFormRef = ref(null)
const passwordForm = reactive({
  oldPassword: '',
  newPassword: '',
  confirmPassword: ''
})

const validateConfirmPassword = (rule, value, callback) => {
  if (value === '') {
    callback(new Error('请再次输入新密码'))
  } else if (value !== passwordForm.newPassword) {
    callback(new Error('两次输入的新密码不一致'))
  } else {
    callback()
  }
}

const passwordRules = {
  oldPassword: [
    { required: true, message: '请输入原密码', trigger: 'blur' }
  ],
  newPassword: [
    { required: true, message: '请输入新密码', trigger: 'blur' },
    { min: 6, message: '新密码长度不能少于6位', trigger: 'blur' }
  ],
  confirmPassword: [
    { required: true, validator: validateConfirmPassword, trigger: 'blur' }
  ]
}

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
  } else if (command === 'changePassword') {
    passwordDialogVisible.value = true
    passwordForm.oldPassword = ''
    passwordForm.newPassword = ''
    passwordForm.confirmPassword = ''
  }
}

const handleChangePassword = async () => {
  if (!passwordFormRef.value) return
  
  await passwordFormRef.value.validate(async (valid) => {
    if (valid) {
      try {
        await request.post('/admin/change-password', {
          old_password: passwordForm.oldPassword,
          new_password: passwordForm.newPassword,
          confirm_password: passwordForm.confirmPassword
        })
        ElMessage.success('密码修改成功')
        passwordDialogVisible.value = false
      } catch (error) {
        ElMessage.error(error.message || '密码修改失败')
      }
    }
  })
}
</script>

<style scoped>
.admin-layout-container {
  width: 100vw;
  height: 100vh;
  overflow: hidden;
  position: relative;
}

.admin-layout-wrapper {
  width: 100%;
  height: 100%;
  overflow: auto;
  transform-origin: top left;
}

.admin-layout {
  height: 100%;
}

.sidebar {
  background-color: #304156;
  overflow-x: hidden;
  transition: width 0.3s;
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
  transition: all 0.3s;
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

.header-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.collapse-icon {
  font-size: 20px;
  cursor: pointer;
  color: #333;
  transition: transform 0.3s;
}

.collapse-icon:hover {
  color: #409EFF;
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

.zoom-control {
  position: fixed;
  right: 24px;
  top: 50%;
  transform: translateY(-50%);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  z-index: 9999;
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  padding: 16px 12px;
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
  border: 1px solid rgba(255, 255, 255, 0.6);
}

.zoom-btn {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  border: 1px solid rgba(255, 255, 255, 0.8);
  background: rgba(245, 247, 250, 0.9);
  color: #5a6a7a;
  cursor: pointer;
  transition: all 0.3s ease;
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}

.zoom-btn:hover:not(:disabled) {
  background: rgba(64, 158, 255, 0.95);
  color: #fff;
  border-color: rgba(64, 158, 255, 0.95);
  box-shadow: 0 4px 16px rgba(64, 158, 255, 0.4);
  transform: scale(1.05);
}

.zoom-btn:active:not(:disabled) {
  transform: scale(0.95);
}

.zoom-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.zoom-btn svg {
  width: 18px;
  height: 18px;
}

.zoom-display {
  font-size: 14px;
  font-weight: 600;
  color: #5a6a7a;
  min-width: 50px;
  text-align: center;
}
</style>
