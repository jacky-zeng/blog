<template>
  <div class="article-form-page">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>{{ isEdit ? '编辑文章' : '新增文章' }}</span>
          <el-button @click="$router.back()">返回</el-button>
        </div>
      </template>

      <el-form :model="form" :rules="rules" ref="formRef" label-width="100px">
        <el-form-item label="文章标题" prop="title">
          <el-input v-model="form.title" placeholder="请输入文章标题" />
        </el-form-item>
        
        <el-form-item label="文章摘要" prop="summary">
          <el-input v-model="form.summary" type="textarea" :rows="3" placeholder="请输入文章摘要" />
        </el-form-item>
        
        <el-form-item label="文章内容" prop="content">
          <div ref="editorRef" class="ai-editor-wrapper"></div>
        </el-form-item>
        
        <el-form-item label="分类" prop="category_id">
          <el-select v-model="form.category_id" placeholder="请选择分类" style="width: 100%">
            <el-option v-for="cat in categories" :key="cat.id" :label="cat.name" :value="cat.id" />
          </el-select>
        </el-form-item>
        
        <el-form-item label="标签">
          <el-select
            v-model="form.tags"
            multiple
            filterable
            allow-create
            placeholder="请选择或输入标签"
            style="width: 100%"
          >
            <el-option v-for="tag in tags" :key="tag.id" :label="tag.name" :value="tag.name" />
          </el-select>
        </el-form-item>
        
        <el-form-item label="封面图">
          <el-upload
            class="cover-uploader"
            :action="uploadUrl"
            :show-file-list="false"
            :on-success="handleUploadSuccess"
            :before-upload="beforeUpload"
          >
            <img v-if="form.cover_image" :src="form.cover_image" class="cover-image" />
            <el-icon v-else class="cover-uploader-icon"><Plus /></el-icon>
          </el-upload>
        </el-form-item>
        
        <el-form-item label="SEO标题">
          <el-input v-model="form.seo_title" placeholder="请输入SEO标题（可选）" />
        </el-form-item>
        
        <el-form-item label="SEO描述">
          <el-input v-model="form.seo_description" type="textarea" :rows="3" placeholder="请输入SEO描述（可选）" />
        </el-form-item>
        
        <el-form-item label="状态">
          <el-radio-group v-model="form.status">
            <el-radio :value="0">草稿</el-radio>
            <el-radio :value="1">发布</el-radio>
          </el-radio-group>
        </el-form-item>
        
        <el-form-item>
          <el-button type="primary" @click="handleSubmit" :loading="loading">
            {{ isEdit ? '更新' : '创建' }}
          </el-button>
          <el-button @click="$router.back()">取消</el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import { AiEditor } from 'aieditor'
import 'aieditor/dist/style.css'
import request from '@/utils/request'

const route = useRoute()
const router = useRouter()

const formRef = ref(null)
const editorRef = ref(null)
const loading = ref(false)
const categories = ref([])
const tags = ref([])
const uploadUrl = '/api/upload'

let aiEditor = null

const isEdit = computed(() => !!route.params.id)

const form = ref({
  title: '',
  summary: '',
  content: '',
  category_id: null,
  tags: [],
  cover_image: '',
  seo_title: '',
  seo_description: '',
  status: 0
})

const rules = {
  title: [
    { required: true, message: '请输入文章标题', trigger: 'blur' }
  ],
  content: [
    { required: true, message: '请输入文章内容', trigger: 'blur' }
  ],
  category_id: [
    { required: true, message: '请选择分类', trigger: 'change' }
  ]
}

const loadCategories = async () => {
  try {
    const res = await request.get('/admin/categories')
    if (res.code === 200) {
      categories.value = res.data
    }
  } catch (error) {
    ElMessage.error('加载分类列表失败')
  }
}

const loadTags = async () => {
  try {
    const res = await request.get('/admin/tags')
    if (res.code === 200) {
      tags.value = res.data
    }
  } catch (error) {
    ElMessage.error('加载标签列表失败')
  }
}

const loadArticle = async () => {
  try {
    const res = await request.get(`/admin/articles/${route.params.id}`)
    if (res.code === 200) {
      const article = res.data
      form.value = {
        title: article.title,
        summary: article.summary,
        content: article.content,
        category_id: article.category_id,
        tags: article.tags.map(t => t.name),
        cover_image: article.cover_image,
        seo_title: article.seo_title,
        seo_description: article.seo_description,
        status: article.status
      }
      
      if (aiEditor && article.content) {
        aiEditor.setContent(article.content)
      }
    }
  } catch (error) {
    ElMessage.error('加载文章失败')
  }
}

const handleSubmit = async () => {
  if (!formRef.value) return
  
  if (aiEditor) {
    form.value.content = aiEditor.getHtml()
  }
  
  await formRef.value.validate(async (valid) => {
    if (valid) {
      loading.value = true
      try {
        const url = isEdit.value ? `/admin/articles/${route.params.id}` : '/admin/articles'
        const method = isEdit.value ? 'put' : 'post'
        
        const res = await request[method](url, form.value)
        if (res.code === 200) {
          ElMessage.success(isEdit.value ? '更新成功' : '创建成功')
          router.push('/admin/articles')
        }
      } catch (error) {
        ElMessage.error(isEdit.value ? '更新失败' : '创建失败')
      } finally {
        loading.value = false
      }
    }
  })
}

const handleUploadSuccess = (response) => {
  if (response.code === 200) {
    form.value.cover_image = response.data.url
    ElMessage.success('上传成功')
  } else {
    ElMessage.error('上传失败')
  }
}

const beforeUpload = (file) => {
  const isImage = file.type.startsWith('image/')
  const isLt2M = file.size / 1024 / 1024 < 2

  if (!isImage) {
    ElMessage.error('只能上传图片文件!')
    return false
  }
  if (!isLt2M) {
    ElMessage.error('图片大小不能超过 2MB!')
    return false
  }
  return true
}

const customImageUploader = async (file, uploadUrl, headers, formName) => {
  const formData = new FormData()
  formData.append(formName, file)
  
  const response = await fetch(uploadUrl, {
    method: 'POST',
    headers: {
      ...headers,
      'Authorization': `Bearer ${localStorage.getItem('token') || ''}`
    },
    body: formData
  })
  
  const result = await response.json()
  
  if (result && result.code === 200 && result.data && result.data.url) {
    //const baseUrl = window.location.origin
    //const fullUrl = result.data.url.startsWith('http') ? result.data.url : baseUrl + result.data.url
    return { 
      errorCode: 0,
      data: {
        src: result.data.url
      }
    }
  }
  
  return {
    errorCode: -1,
    message: '上传失败'
  }
}

onMounted(() => {
  loadCategories()
  loadTags()
  
  aiEditor = new AiEditor({
    element: editorRef.value,
    placeholder: '请输入文章内容...',
    content: form.value.content || '',
    toolbarExcludeKeys: ["ai"],
    image: {
      uploadUrl: '/api/upload',
      uploadFormName: 'file',
      allowBase64: false,
      uploader: customImageUploader
    }
  })
  
  if (isEdit.value) {
    loadArticle()
  }
})

onUnmounted(() => {
  if (aiEditor) {
    aiEditor.destroy()
    aiEditor = null
  }
})
</script>

<style scoped>
.article-form-page {
  padding: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: bold;
}

.ai-editor-wrapper {
  height: 500px;
  border: 1px solid #e4e7ed;
  border-radius: 4px;
}

.cover-uploader {
  border: 1px dashed #d9d9d9;
  border-radius: 6px;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition: all 0.3s;
}

.cover-uploader:hover {
  border-color: #409EFF;
}

.cover-uploader-icon {
  font-size: 28px;
  color: #8c939d;
  width: 178px;
  height: 178px;
  line-height: 178px;
  text-align: center;
}

.cover-image {
  width: 178px;
  height: 178px;
  display: block;
  object-fit: cover;
}
</style>