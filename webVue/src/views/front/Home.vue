<template>
  <div class="home-page">
    <el-row :gutter="20">
      <el-col :span="6">
        <div class="sidebar-sticky">
          <el-card class="sidebar-card">
            <template #header>
              <span>分类</span>
            </template>
            <div v-if="categories.length > 0">
              <div v-for="cat in categories" :key="cat.id" class="category-item" @click="$router.push(`/category/${cat.slug}`)">
                <span class="category-name">{{ cat.name }}</span>
                <el-badge :value="cat.articles_count" class="category-count" />
              </div>
            </div>
            <el-empty v-else description="暂无分类" :image-size="60" />
          </el-card>

          <el-card class="sidebar-card" style="margin-top: 20px;">
            <template #header>
              <span>标签云</span>
            </template>
            <div v-if="tags.length > 0" class="tags-cloud">
              <el-tag v-for="tag in tags" :key="tag.id" size="small" @click="$router.push(`/tag/${tag.slug}`)">
                {{ tag.name }}
              </el-tag>
            </div>
            <el-empty v-else description="暂无标签" :image-size="60" />
          </el-card>
        </div>
      </el-col>

      <el-col :span="18">
        <el-card class="articles-card">
          <template #header>
            <div class="card-header">
              <span>最新文章</span>
            </div>
          </template>
          
          <div v-if="articles.length > 0">
            <div v-for="article in articles" :key="article.id" class="article-item">
              <h3 class="article-title" @click="$router.push(`/article/${article.slug || article.id}`)">
                {{ article.title }}
              </h3>
              <div class="article-meta">
                <el-tag size="small">{{ article.category?.name }}</el-tag>
                <span class="meta-item">
                  <el-icon><Calendar /></el-icon>
                  {{ formatDate(article.created_at) }}
                </span>
              </div>
              <p class="article-summary">{{ article.summary || article.content.substring(0, 200) + '...' }}</p>
              <div class="article-tags" v-if="article.tags && article.tags.length > 0">
                <el-tag v-for="tag in article.tags" :key="tag.id" size="small" type="info">
                  {{ tag.name }}
                </el-tag>
              </div>
            </div>
          </div>
          <el-empty v-else description="暂无文章" />

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
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { Calendar } from '@element-plus/icons-vue'
import request from '@/utils/request'

const router = useRouter()

const articles = ref([])
const categories = ref([])
const tags = ref([])
const currentPage = ref(1)
const pageSize = ref(10)
const total = ref(0)
const loadArticles = async () => {
  try {
    const res = await request.get('/articles', {
      params: {
        page: currentPage.value,
        page_size: pageSize.value
      }
    })
    if (res.code === 200) {
      articles.value = res.data.data
      total.value = res.data.total
    }
  } catch (error) {
    ElMessage.error('加载文章失败')
  }
}

const loadCategories = async () => {
  try {
    const res = await request.get('/categories')
    if (res.code === 200) {
      categories.value = res.data
    }
  } catch (error) {
    ElMessage.error('加载分类失败')
  }
}

const loadTags = async () => {
  try {
    const res = await request.get('/tags')
    if (res.code === 200) {
      tags.value = res.data
    }
  } catch (error) {
    ElMessage.error('加载标签失败')
  }
}

const handleCurrentChange = (val) => {
  currentPage.value = val
  loadArticles()
}

const formatDate = (dateStr) => {
  const date = new Date(dateStr)
  return date.toLocaleDateString('zh-CN')
}

onMounted(() => {
  loadArticles()
  loadCategories()
  loadTags()
})
</script>

<style scoped>
.home-page {
  padding: 20px;
  background-color: #ffffff;
  min-height: calc(100vh - 60px);
  position: absolute;
  top: 60px;
  left: 0;
  right: 0;
  bottom: 0;
}

.articles-card {
  margin-bottom: 20px;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.card-header {
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
  margin-bottom: 12px;
  font-size: 14px;
}

.article-tags {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.sidebar-sticky {
  position: sticky;
  top: 80px;
}

.sidebar-card {
  margin-bottom: 20px;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.category-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  cursor: pointer;
  border-bottom: 1px solid #f0f0f0;
  transition: all 0.3s;
}

.category-item:hover {
  background-color: #fff8f0;
  padding-left: 10px;
}

.category-name {
  font-weight: 500;
  color: #333;
}

.category-count {
  flex-shrink: 0;
}

.tags-cloud {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.tags-cloud .el-tag {
  cursor: pointer;
  transition: all 0.3s;
  background-color: #fff;
  border-color: #ff6600;
  color: #ff6600;
}

.tags-cloud .el-tag:hover {
  background-color: #fff8f0;
}

:deep(.el-tag) {
  border-radius: 4px;
}

:deep(.articles-card .el-tag) {
  background-color: #fff8f0;
  border-color: #ffd5b3;
  color: #ff6600;
}
</style>
