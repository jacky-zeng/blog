<template>
  <div class="about-page">
    <el-card>
      <template #header>
        <span>关于我</span>
      </template>

      <div class="about-content">
        <div class="author-info">
          <h2 class="author-name">{{ siteInfo.author_name }}</h2>
          <p class="author-bio">{{ siteInfo.author_bio }}</p>
        </div>

        <el-divider>联系方式</el-divider>

        <div class="contact-info">
          <div v-if="siteInfo.social_links" v-for="(link, name) in siteInfo.social_links" :key="name" class="contact-item">
            <el-icon><Link /></el-icon>
            <span>{{ name }}：</span>
            <a :href="link" target="_blank">{{ link }}</a>
          </div>
        </div>

        <el-divider>博客统计</el-divider>

        <div class="stats-info">
          <div class="stat-item">
            <el-icon><Document /></el-icon>
            <span>文章总数：{{ stats.total_articles || 0 }}</span>
          </div>
          <div class="stat-item">
            <el-icon><Folder /></el-icon>
            <span>分类数量：{{ stats.total_categories || 0 }}</span>
          </div>
          <div class="stat-item">
            <el-icon><PriceTag /></el-icon>
            <span>标签数量：{{ stats.total_tags || 0 }}</span>
          </div>
          <div class="stat-item">
            <el-icon><View /></el-icon>
            <span>总访问量：{{ stats.total_views || 0 }}</span>
          </div>
        </div>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Document, Folder, PriceTag, View, Link } from '@element-plus/icons-vue'
import request from '@/utils/request'

const siteInfo = ref({
  site_name: '我的博客',
  site_subtitle: '记录生活，分享技术',
  logo: '',
  icp: '',
  author_name: '博主',
  author_bio: '',
  social_links: {}
})

const stats = ref({
  total_articles: 0,
  total_categories: 0,
  total_tags: 0,
  total_views: 0
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

const loadStats = async () => {
  try {
    const res = await request.get('/admin/dashboard')
    if (res.code === 200) {
      stats.value = res.data.stats
    }
  } catch (error) {
    console.error('加载统计信息失败')
  }
}

onMounted(() => {
  loadSiteInfo()
  loadStats()
})
</script>

<style scoped>
.about-page {
  padding: 20px;
}

.about-content {
  max-width: 800px;
  margin: 0 auto;
}

.author-info {
  text-align: center;
  padding: 30px 0;
}

.author-name {
  font-size: 28px;
  color: #333;
  margin: 0 0 15px 0;
}

.author-bio {
  font-size: 16px;
  color: #666;
  line-height: 1.8;
}

.contact-info {
  padding: 20px 0;
}

.contact-item {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 15px;
  font-size: 16px;
  color: #333;
}

.contact-item a {
  color: #409EFF;
  text-decoration: none;
}

.contact-item a:hover {
  text-decoration: underline;
}

.stats-info {
  padding: 20px 0;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 15px;
  font-size: 16px;
  color: #333;
}
</style>
