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
import 'aieditor/dist/style.css'
import hljs from 'highlight.js/lib/core'
import javascript from 'highlight.js/lib/languages/javascript'
import typescript from 'highlight.js/lib/languages/typescript'
import python from 'highlight.js/lib/languages/python'
import java from 'highlight.js/lib/languages/java'
import php from 'highlight.js/lib/languages/php'
import go from 'highlight.js/lib/languages/go'
import rust from 'highlight.js/lib/languages/rust'
import cpp from 'highlight.js/lib/languages/cpp'
import csharp from 'highlight.js/lib/languages/csharp'
import ruby from 'highlight.js/lib/languages/ruby'
import sql from 'highlight.js/lib/languages/sql'
import json from 'highlight.js/lib/languages/json'
import html from 'highlight.js/lib/languages/xml'
import css from 'highlight.js/lib/languages/css'
import markdown from 'highlight.js/lib/languages/markdown'
import shell from 'highlight.js/lib/languages/shell'
import 'highlight.js/styles/atom-one-dark.css'

hljs.registerLanguage('javascript', javascript)
hljs.registerLanguage('typescript', typescript)
hljs.registerLanguage('python', python)
hljs.registerLanguage('java', java)
hljs.registerLanguage('php', php)
hljs.registerLanguage('go', go)
hljs.registerLanguage('rust', rust)
hljs.registerLanguage('cpp', cpp)
hljs.registerLanguage('csharp', csharp)
hljs.registerLanguage('ruby', ruby)
hljs.registerLanguage('sql', sql)
hljs.registerLanguage('json', json)
hljs.registerLanguage('html', html)
hljs.registerLanguage('css', css)
hljs.registerLanguage('markdown', markdown)
hljs.registerLanguage('shell', shell)

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
      setTimeout(() => {
        highlightCodeBlocks()
      }, 100)
    }
  } catch (error) {
    ElMessage.error('加载文章失败')
  }
}

const highlightCodeBlocks = () => {
  const articleContent = document.querySelector('.article-content')
  if (!articleContent) {
    console.log('articleContent not found')
    return
  }
  
  const pres = articleContent.querySelectorAll('pre')
  console.log('Found pre elements:', pres.length)
  
  pres.forEach((pre) => {
    let code = pre.querySelector('code')
    if (!code) {
      const text = pre.textContent || ''
      code = document.createElement('code')
      code.textContent = text
      pre.innerHTML = ''
      pre.appendChild(code)
    }
    
    let language = 'text'
    
    if (code.classList.length > 0) {
      const classArray = Array.from(code.classList)
      const langClass = classArray.find(cls => cls.startsWith('language-') || cls.startsWith('lang-'))
      if (langClass) {
        language = langClass.replace(/^(language-|lang-)/, '')
      }
    } else if (pre.classList.length > 0) {
      const classArray = Array.from(pre.classList)
      const langClass = classArray.find(cls => cls.startsWith('language-') || cls.startsWith('lang-'))
      if (langClass) {
        language = langClass.replace(/^(language-|lang-)/, '')
      }
    } else if (pre.getAttribute('data-language')) {
      language = pre.getAttribute('data-language')
    } else if (pre.getAttribute('data-lang')) {
      language = pre.getAttribute('data-lang')
    }
    
    console.log('Processing pre code block, language:', language)
    
    if (hljs.getLanguage(language)) {
      const result = hljs.highlight(code.textContent || '', { language: language })
      code.innerHTML = result.value
      code.classList.add('hljs')
      pre.classList.add('hljs')
    }
    
    pre.style.backgroundColor = '#1a1a2e'
    pre.style.padding = '16px'
    pre.style.borderRadius = '8px'
    pre.style.overflowX = 'auto'
  })
  
  const standaloneCodes = articleContent.querySelectorAll('code:not(pre code)')
  console.log('Found standalone code elements:', standaloneCodes.length)
  
  standaloneCodes.forEach((code) => {
    let language = 'text'
    
    if (code.classList.length > 0) {
      const classArray = Array.from(code.classList)
      const langClass = classArray.find(cls => cls.startsWith('language-') || cls.startsWith('lang-'))
      if (langClass) {
        language = langClass.replace(/^(language-|lang-)/, '')
      }
    }
    
    console.log('Processing standalone code, class:', code.className, 'language:', language)
    
    if (hljs.getLanguage(language)) {
      const result = hljs.highlight(code.textContent || '', { language: language })
      code.innerHTML = result.value
      code.classList.add('hljs')
    }
    
    const pre = document.createElement('pre')
    pre.classList.add('hljs')
    pre.style.backgroundColor = '#1a1a2e'
    pre.style.padding = '16px'
    pre.style.borderRadius = '8px'
    pre.style.overflowX = 'auto'
    pre.style.margin = '16px 0'
    
    code.parentNode.insertBefore(pre, code)
    pre.appendChild(code)
  })
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

.article-content :deep(table) {
  width: 100%;
  border-collapse: collapse;
  margin: 16px 0;
  font-size: 14px;
}

.article-content :deep(th),
.article-content :deep(td) {
  border: 1px solid #e4e7ed;
  padding: 8px 12px;
  text-align: left;
}

.article-content :deep(th) {
  background-color: #f5f7fa;
  font-weight: 600;
}

.article-content :deep(tr:nth-child(even)) {
  background-color: #fafafa;
}

.article-content :deep(tr:hover) {
  background-color: #f5f7fa;
}



.article-content :deep(blockquote) {
  border-left: 4px solid #409EFF;
  padding: 12px 16px;
  margin: 16px 0;
  background-color: #f5f7fa;
  color: #666;
  font-style: italic;
}

.article-content :deep(h1),
.article-content :deep(h2),
.article-content :deep(h3),
.article-content :deep(h4),
.article-content :deep(h5),
.article-content :deep(h6) {
  font-weight: 600;
  margin: 16px 0 8px 0;
  color: #333;
}

.article-content :deep(h1) { font-size: 24px; }
.article-content :deep(h2) { font-size: 20px; }
.article-content :deep(h3) { font-size: 18px; }
.article-content :deep(h4) { font-size: 16px; }
.article-content :deep(h5) { font-size: 14px; }
.article-content :deep(h6) { font-size: 14px; }

.article-content :deep(p) {
  margin: 12px 0;
}

.article-content :deep(ul),
.article-content :deep(ol) {
  padding-left: 24px;
  margin: 12px 0;
}

.article-content :deep(li) {
  margin: 6px 0;
}

.article-content :deep(a) {
  color: #409EFF;
  text-decoration: none;
}

.article-content :deep(a:hover) {
  text-decoration: underline;
}

.article-content :deep(img) {
  max-width: 100%;
  height: auto;
  border-radius: 8px;
  margin: 8px 0;
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
