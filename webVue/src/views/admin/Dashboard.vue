<template>
  <div class="dashboard">
    <el-row :gutter="20" class="stats-row">
      <el-col :span="6" v-for="stat in stats" :key="stat.key">
        <el-card class="stat-card" @click="handleStatClick(stat.path)">
          <div class="stat-content">
            <div class="stat-icon" :style="{ background: stat.color }">
              <el-icon :size="24"><component :is="stat.icon" /></el-icon>
            </div>
            <div class="stat-info">
              <div class="stat-value">{{ stat.value }}</div>
              <div class="stat-label">{{ stat.label }}</div>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <el-row :gutter="20" class="content-row">
      <el-col :span="12">
        <el-card class="chart-card">
          <template #header>
            <div class="card-header">
              <span>分类统计</span>
            </div>
          </template>
          <div v-if="categoryStats.length > 0">
            <div v-for="cat in categoryStats" :key="cat.id" class="category-item">
              <div class="category-name">{{ cat.name }}</div>
              <el-progress :percentage="getPercentage(cat.articles_count)" :color="getRandomColor()" />
              <div class="category-count">{{ cat.articles_count }} 篇</div>
            </div>
          </div>
          <el-empty v-else description="暂无分类数据" />
        </el-card>
      </el-col>
      <el-col :span="12">
        <el-card class="chart-card">
          <template #header>
            <div class="card-header">
              <span>热门文章 TOP5</span>
            </div>
          </template>
          <div v-if="hotArticles.length > 0">
            <div v-for="article in hotArticles" :key="article.id" class="article-item">
              <div class="article-title">{{ article.title }}</div>
              <div class="article-views">{{ article.view_count }} 阅读</div>
            </div>
          </div>
          <el-empty v-else description="暂无文章数据" />
        </el-card>
      </el-col>
    </el-row>

    <el-row :gutter="20" class="content-row">
      <el-col :span="24">
        <el-card class="list-card">
          <template #header>
            <div class="card-header">
              <span>最近发布的文章</span>
              <el-button type="primary" size="small" @click="$router.push('/admin/articles')">
                查看全部
              </el-button>
            </div>
          </template>
          <el-table :data="recentArticles" style="width: 100%">
            <el-table-column prop="title" label="标题" />
            <el-table-column prop="category.name" label="分类" width="120" />
            <el-table-column prop="view_count" label="阅读量" width="100" />
            <el-table-column prop="created_at" label="发布时间" width="180" />
            <el-table-column label="操作" width="120">
              <template #default="{ row }">
                <el-button type="primary" size="small" @click="$router.push(`/admin/articles/${row.id}/edit`)">
                  编辑
                </el-button>
              </template>
            </el-table-column>
          </el-table>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import request from '@/utils/request'
import { Document, Folder, PriceTag, ChatDotRound, View, Calendar, DataAnalysis } from '@element-plus/icons-vue'

const stats = ref([
  { key: 'total_articles', label: '文章总数', value: 0, icon: Document, color: '#409EFF', path: '/admin/articles' },
  { key: 'total_categories', label: '分类数', value: 0, icon: Folder, color: '#67C23A', path: '/admin/categories' },
  { key: 'total_comments', label: '评论数', value: 0, icon: ChatDotRound, color: '#E6A23C', path: '/admin/comments' },
  { key: 'total_views', label: '总访问量', value: 0, icon: View, color: '#F56C6C', path: '/admin/articles' }
])

const router = useRouter()

const handleStatClick = (path) => {
  router.push(path)
}

const categoryStats = ref([])
const hotArticles = ref([])
const recentArticles = ref([])

const loadData = async () => {
  try {
    const res = await request.get('/admin/dashboard')
    if (res.code === 200) {
      const { stats: dashboardStats, category_stats, hot_articles, recent_articles } = res.data
      
      stats.value[0].value = dashboardStats.total_articles
      stats.value[1].value = dashboardStats.total_categories
      stats.value[2].value = dashboardStats.total_comments
      stats.value[3].value = dashboardStats.total_views
      
      categoryStats.value = category_stats
      hotArticles.value = hot_articles
      recentArticles.value = recent_articles
    }
  } catch (error) {
    ElMessage.error('加载数据失败')
  }
}

const getPercentage = (count) => {
  const total = categoryStats.value.reduce((sum, cat) => sum + cat.articles_count, 0)
  return total > 0 ? Math.round((count / total) * 100) : 0
}

const getRandomColor = () => {
  const colors = ['#409EFF', '#67C23A', '#E6A23C', '#F56C6C', '#909399']
  return colors[Math.floor(Math.random() * colors.length)]
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.dashboard {
  padding: 20px;
}

.stats-row {
  margin-bottom: 20px;
}

.stat-card {
  cursor: pointer;
  transition: all 0.3s;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.stat-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
}

.stat-info {
  flex: 1;
  margin-left: 20px;
}

.stat-value {
  font-size: 28px;
  font-weight: bold;
  color: #333;
}

.stat-label {
  font-size: 14px;
  color: #909399;
  margin-top: 5px;
}

.content-row {
  margin-bottom: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: bold;
}

.category-item {
  margin-bottom: 15px;
}

.category-name {
  margin-bottom: 8px;
  font-weight: 500;
}

.category-count {
  text-align: right;
  font-size: 12px;
  color: #909399;
  margin-top: 5px;
}

.article-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 0;
  border-bottom: 1px solid #f0f0f0;
}

.article-item:last-child {
  border-bottom: none;
}

.article-title {
  flex: 1;
  font-weight: 500;
}

.article-views {
  color: #909399;
  font-size: 14px;
}
</style>
