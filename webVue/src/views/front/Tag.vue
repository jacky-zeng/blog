<template>
  <div class="tag-page">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>标签：{{ tag?.name }}</span>
          <el-tag>{{ tag?.articles_count || 0 }} 篇文章</el-tag>
        </div>
      </template>

      <div v-if="articles.length > 0">
        <div v-for="article in articles" :key="article.id" class="article-item">
          <h3 class="article-title" @click="$router.push(`/article/${article.slug}`)">
            {{ article.title }}
          </h3>
          <div class="article-meta">
            <el-tag size="small">{{ article.category?.name }}</el-tag>
            <span class="meta-item">
              <el-icon><Calendar /></el-icon>
              {{ formatDate(article.created_at) }}
            </span>
          </div>
          <p class="article-summary">{{ article.summary || article.content }}</p>
        </div>
      </div>
      <el-empty v-else description="该标签下暂无文章" />

      <el-pagination
        v-if="total > 0"
        v-model:current-page="currentPage"
        v-model:page-size="pageSize"
        :total="total"
        layout="total, prev, pager, next"
        @current-change="handleCurrentChange"
        style="margin-top: 20px; justify-content: center"
      />
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { ElMessage } from 'element-plus'
import { Calendar } from '@element-plus/icons-vue'
import request from '@/utils/request'

const route = useRoute()

const tag = ref(null)
const articles = ref([])
const currentPage = ref(1)
const pageSize = ref(10)
const total = ref(0)

const loadData = async () => {
  try {
    const res = await request.get(`/tag/${route.params.slug}`)
    if (res.code === 200) {
      tag.value = res.data.tag
      articles.value = res.data.articles.data
      total.value = res.data.articles.total
    }
  } catch (error) {
    ElMessage.error('加载数据失败')
  }
}

const handleCurrentChange = (val) => {
  currentPage.value = val
  loadData()
}

const formatDate = (dateStr) => {
  const date = new Date(dateStr)
  return date.toLocaleDateString('zh-CN')
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.tag-page {
  padding: 20px;
  background-color: #f5f5f5;
  min-height: calc(100vh - 60px);
  position: absolute;
  top: 60px;
  left: 0;
  right: 0;
  bottom: 0;
}

.el-card {
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: bold;
  font-size: 16px;
  color: #333;
}

.article-item {
  padding: 20px 0;
  border-bottom: 1px solid #f0f0f0;
}

.article-item:last-child {
  border-bottom: none;
}

.article-title {
  font-size: 20px;
  margin: 0 0 12px 0;
  cursor: pointer;
  color: #333;
  transition: color 0.3s;
  font-weight: 600;
}

.article-title:hover {
  color: #ff6600;
}

.article-meta {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 12px;
  color: #999;
  font-size: 14px;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 4px;
}

.article-summary {
  color: #666;
  line-height: 1.8;
  font-size: 14px;
}

:deep(.el-tag) {
  border-radius: 4px;
  background-color: #fff8f0;
  border-color: #ffd5b3;
  color: #ff6600;
}
</style>
