<template>
  <div class="archive-page">
    <el-card>
      <template #header>
        <span>文章归档</span>
      </template>

      <div v-if="Object.keys(archiveData).length > 0">
        <div v-for="(articles, date) in archiveData" :key="date" class="archive-group">
          <h3 class="archive-date">{{ date }}</h3>
          <div v-for="article in articles" :key="article.id" class="archive-article" @click="$router.push(`/article/${article.slug}`)">
            <span class="article-title">{{ article.title }}</span>
            <span class="article-day">{{ getDay(article.created_at) }}</span>
          </div>
        </div>
      </div>
      <el-empty v-else description="暂无归档数据" />
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import request from '@/utils/request'

const archiveData = ref({})

const loadArchive = async () => {
  try {
    const res = await request.get('/archive')
    if (res.code === 200) {
      archiveData.value = res.data
    }
  } catch (error) {
    ElMessage.error('加载归档失败')
  }
}

const getDay = (dateStr) => {
  const date = new Date(dateStr)
  return date.getDate()
}

onMounted(() => {
  loadArchive()
})
</script>

<style scoped>
.archive-page {
  padding: 20px;
}

.archive-group {
  margin-bottom: 30px;
}

.archive-date {
  font-size: 18px;
  font-weight: bold;
  color: #333;
  margin-bottom: 15px;
  padding-bottom: 10px;
  border-bottom: 2px solid #409EFF;
}

.archive-article {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 15px;
  margin-bottom: 8px;
  background-color: #f5f7fa;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.3s;
}

.archive-article:hover {
  background-color: #e6f7ff;
  transform: translateX(5px);
}

.article-title {
  flex: 1;
  font-weight: 500;
  color: #333;
}

.article-day {
  font-size: 20px;
  font-weight: bold;
  color: #409EFF;
  min-width: 40px;
  text-align: center;
}
</style>
