<template>
  <div class="article-detail-page">
    <el-row :gutter="20">
      <el-col :span="18">
        <el-card v-if="article" class="article-card">
          <h1 class="article-title">{{ article.title }}</h1>
          
          <div class="article-meta">
            <el-tag size="small">{{ article.category?.name }}</el-tag>
            <span class="meta-item">
              <el-icon><Calendar /></el-icon>
              {{ formatDate(article.created_at) }}
            </span>
            <span class="meta-item">
              <el-icon><View /></el-icon>
              {{ article.view_count }}
            </span>
          </div>

          <div v-if="article.cover_image" class="article-cover">
            <img :src="article.cover_image" :alt="article.title" />
          </div>

          <div class="article-content" v-html="article.content"></div>

          <div v-if="article.tags && article.tags.length > 0" class="article-tags">
            <span>标签：</span>
            <el-tag v-for="tag in article.tags" :key="tag.id" size="small" type="info">
              {{ tag.name }}
            </el-tag>
          </div>

          <el-divider>评论区</el-divider>

          <div class="comments-section">
            <div v-if="comments.length > 0">
              <div v-for="comment in comments" :key="comment.id" class="comment-item">
                <div class="comment-header">
                  <span class="comment-author">{{ comment.nickname }}</span>
                  <span class="comment-time">{{ formatDate(comment.created_at) }}</span>
                </div>
                <div class="comment-content">{{ comment.content }}</div>
              </div>
            </div>
            <el-empty v-else description="暂无评论" />

            <el-form v-if="commentEnabled" :model="commentForm" :rules="commentRules" ref="commentFormRef" class="comment-form">
              <el-form-item prop="nickname">
                <el-input v-model="commentForm.nickname" placeholder="昵称" />
              </el-form-item>
              <el-form-item prop="email">
                <el-input v-model="commentForm.email" placeholder="邮箱" />
              </el-form-item>
              <el-form-item prop="content">
                <el-input v-model="commentForm.content" type="textarea" :rows="4" placeholder="发表评论..." />
              </el-form-item>
              <el-form-item>
                <el-button type="primary" @click="handleSubmitComment" :loading="commentLoading">
                  提交评论
                </el-button>
              </el-form-item>
            </el-form>
            <div v-else class="comment-disabled">
              <el-alert title="评论已关闭" type="info" :closable="false" />
            </div>
          </div>
        </el-card>
        <el-empty v-else description="文章不存在" />
      </el-col>

      <el-col :span="6">
        <el-card class="sidebar-card">
          <template #header>
            <span>相关文章</span>
          </template>
          <div v-if="relatedArticles.length > 0">
            <div v-for="art in relatedArticles" :key="art.id" class="related-article" @click="$router.push(`/article/${art.slug}`)">
              {{ art.title }}
            </div>
          </div>
          <el-empty v-else description="暂无相关文章" :image-size="60" />
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { ElMessage } from 'element-plus'
import { Calendar, View } from '@element-plus/icons-vue'
import request from '@/utils/request'

const route = useRoute()

const article = ref(null)
const comments = ref([])
const relatedArticles = ref([])
const commentEnabled = ref(false)
const commentLoading = ref(false)
const commentFormRef = ref(null)

const commentForm = ref({
  nickname: '',
  email: '',
  content: ''
})

const commentRules = {
  nickname: [
    { required: true, message: '请输入昵称', trigger: 'blur' }
  ],
  email: [
    { required: true, message: '请输入邮箱', trigger: 'blur' },
    { type: 'email', message: '请输入正确的邮箱格式', trigger: 'blur' }
  ],
  content: [
    { required: true, message: '请输入评论内容', trigger: 'blur' }
  ]
}

const loadArticle = async () => {
  try {
    const res = await request.get(`/articles/${route.params.slug}`)
    if (res.code === 200) {
      article.value = res.data
    }
  } catch (error) {
    ElMessage.error('加载文章失败')
  }
}

const loadComments = async () => {
  try {
    const res = await request.get(`/articles/${route.params.slug}/comments`)
    if (res.code === 200) {
      comments.value = res.data
    }
  } catch (error) {
    ElMessage.error('加载评论失败')
  }
}

const loadRelatedArticles = async () => {
  try {
    const res = await request.get('/articles', {
      params: {
        category_id: article.value?.category_id,
        page_size: 5
      }
    })
    if (res.code === 200) {
      relatedArticles.value = res.data.data.filter(a => a.id !== article.value?.id)
    }
  } catch (error) {
    console.error('加载相关文章失败')
  }
}

const loadSettings = async () => {
  try {
    const res = await request.get('/settings')
    if (res.code === 200) {
      commentEnabled.value = parseInt(res.data.comment_enabled) === 1
    }
  } catch (error) {
    console.error('加载设置失败')
  }
}

const handleSubmitComment = async () => {
  if (!commentFormRef.value) return
  
  await commentFormRef.value.validate(async (valid) => {
    if (valid) {
      commentLoading.value = true
      try {
        const res = await request.post(`/articles/${route.params.slug}/comments`, commentForm.value)
        if (res.code === 200) {
          ElMessage.success('评论提交成功，等待审核')
          commentForm.value = { nickname: '', email: '', content: '' }
          loadComments()
        }
      } catch (error) {
        ElMessage.error('提交评论失败')
      } finally {
        commentLoading.value = false
      }
    }
  })
}

const formatDate = (dateStr) => {
  const date = new Date(dateStr)
  return date.toLocaleString('zh-CN')
}

onMounted(() => {
  loadArticle()
  loadComments()
  loadRelatedArticles()
  loadSettings()
})

watch(() => route.params.slug, () => {
  article.value = null
  comments.value = []
  relatedArticles.value = []
  loadArticle()
  loadComments()
  loadRelatedArticles()
})
</script>

<style scoped>
.article-detail-page {
  padding: 20px;
}

.article-card {
  margin-bottom: 20px;
}

.article-title {
  font-size: 28px;
  margin: 0 0 20px 0;
  color: #333;
}

.article-meta {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 20px;
  color: #909399;
  font-size: 14px;
  padding-bottom: 20px;
  border-bottom: 1px solid #f0f0f0;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 4px;
}

.article-cover {
  margin-bottom: 20px;
}

.article-cover img {
  width: 100%;
  border-radius: 8px;
}

.article-content {
  line-height: 1.8;
  color: #333;
  font-size: 16px;
  margin-bottom: 20px;
}

.article-tags {
  display: flex;
  gap: 8px;
  align-items: center;
  margin-bottom: 20px;
}

.comments-section {
  margin-top: 30px;
}

.comment-item {
  padding: 15px;
  border: 1px solid #f0f0f0;
  border-radius: 8px;
  margin-bottom: 15px;
  background-color: #fafafa;
}

.comment-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
  font-size: 14px;
}

.comment-author {
  font-weight: bold;
  color: #333;
}

.comment-time {
  color: #999;
}

.comment-content {
  color: #666;
  line-height: 1.6;
}

.comment-form {
  margin-top: 20px;
  padding: 20px;
  background-color: #f5f7fa;
  border-radius: 8px;
}

.comment-disabled {
  margin-top: 20px;
}

.sidebar-card {
  margin-bottom: 20px;
}

.related-article {
  padding: 10px 0;
  cursor: pointer;
  border-bottom: 1px solid #f0f0f0;
  transition: all 0.3s;
}

.related-article:hover {
  color: #409EFF;
  padding-left: 10px;
}

.related-article:last-child {
  border-bottom: none;
}
</style>
