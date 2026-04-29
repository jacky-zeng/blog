<template>
  <el-container class="front-layout">
    <el-header class="header">
      <div class="header-content">
        <div class="logo" @click="$router.push('/')">
          {{ siteInfo.site_name }}
        </div>
        <el-menu
          :default-active="activeMenu"
          mode="horizontal"
          router
          class="nav-menu"
        >
          <el-menu-item index="/">首页</el-menu-item>
          <el-menu-item index="/archive">归档</el-menu-item>
          <el-menu-item index="/about">关于</el-menu-item>
        </el-menu>
      </div>
    </el-header>
    <el-main class="main-content">
      <router-view />
    </el-main>
    <el-footer class="footer">
      <div class="footer-content">
        <p>{{ siteInfo.site_name }} - {{ siteInfo.site_subtitle }}</p>
        <p v-if="siteInfo.icp">备案号：{{ siteInfo.icp }}</p>
        <p>&copy; {{ new Date().getFullYear() }} {{ siteInfo.site_name }}</p>
      </div>
    </el-footer>
  </el-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { ElMessage } from 'element-plus'
import request from '@/utils/request'

const route = useRoute()
const activeMenu = computed(() => route.path)
const siteInfo = ref({
  site_name: '我的博客',
  site_subtitle: '记录生活，分享技术',
  logo: '',
  icp: '',
  author_name: '博主',
  author_bio: '',
  social_links: {}
})

const loadSiteInfo = async () => {
  try {
    const res = await request.get('/site/info')
    if (res.code === 200) {
      siteInfo.value = res.data
    }
  } catch (error) {
    ElMessage.error('加载网站信息失败')
  }
}

onMounted(() => {
  loadSiteInfo()
})
</script>

<style scoped>
.front-layout {
  min-height: 100vh;
}

.header {
  background-color: #fff;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  padding: 0 20px;
}

.header-content {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 60px;
}

.logo {
  font-size: 24px;
  font-weight: bold;
  color: #333;
  cursor: pointer;
}

.nav-menu {
  border-bottom: none;
}

.main-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
  background-color: #f5f7fa;
}

.footer {
  background-color: #333;
  color: #fff;
  text-align: center;
  padding: 30px 0;
}

.footer-content p {
  margin: 5px 0;
  color: #999;
}
</style>
