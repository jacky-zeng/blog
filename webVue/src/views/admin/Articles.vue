<template>
  <div class="articles-page">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>文章列表</span>
          <el-button type="primary" @click="$router.push('/admin/articles/create')">
            新增文章
          </el-button>
        </div>
      </template>
      
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="关键词">
          <el-input v-model="searchForm.keyword" placeholder="请输入标题" clearable />
        </el-form-item>
        <el-form-item label="分类">
          <el-select v-model="searchForm.category_id" placeholder="请选择分类" clearable>
            <el-option v-for="cat in categories" :key="cat.id" :label="cat.name" :value="cat.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="searchForm.status" placeholder="请选择状态" clearable>
            <el-option label="草稿" :value="0" />
            <el-option label="已发布" :value="1" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">搜索</el-button>
          <el-button @click="handleReset">重置</el-button>
        </el-form-item>
      </el-form>

      <el-table :data="articles" v-loading="loading" style="width: 100%">
        <el-table-column prop="title" label="标题" />
        <el-table-column prop="category.name" label="分类" width="120" />
        <el-table-column label="标签" width="150">
          <template #default="{ row }">
            <el-tag v-for="tag in row.tags" :key="tag.id" size="small" style="margin-right: 5px">
              {{ tag.name }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="row.status === 1 ? 'success' : 'info'">
              {{ row.status === 1 ? '已发布' : '草稿' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="view_count" label="阅读量" width="100" />
        <el-table-column prop="created_at" label="创建时间" width="180" />
        <el-table-column label="操作" width="200" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" size="small" @click="$router.push(`/admin/articles/${row.id}/edit`)">
              编辑
            </el-button>
            <el-button type="danger" size="small" @click="handleDelete(row)">
              删除
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <el-pagination
        v-model:current-page="currentPage"
        v-model:page-size="pageSize"
        :total="total"
        :page-sizes="[10, 20, 50, 100]"
        layout="total, sizes, prev, pager, next, jumper"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
        style="margin-top: 20px; justify-content: flex-end"
      />
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import request from '@/utils/request'

const loading = ref(false)
const articles = ref([])
const categories = ref([])
const currentPage = ref(1)
const pageSize = ref(10)
const total = ref(0)

const searchForm = ref({
  keyword: '',
  category_id: null,
  status: null
})

const loadArticles = async () => {
  loading.value = true
  try {
    const res = await request.get('/admin/articles', {
      params: {
        page: currentPage.value,
        page_size: pageSize.value,
        ...searchForm.value
      }
    })
    if (res.code === 200) {
      articles.value = res.data.data
      total.value = res.data.total
    }
  } catch (error) {
    ElMessage.error('加载文章列表失败')
  } finally {
    loading.value = false
  }
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

const handleSearch = () => {
  currentPage.value = 1
  loadArticles()
}

const handleReset = () => {
  searchForm.value = {
    keyword: '',
    category_id: null,
    status: null
  }
  currentPage.value = 1
  loadArticles()
}

const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm('确定要删除这篇文章吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    
    const res = await request.delete(`/admin/articles/${row.id}`)
    if (res.code === 200) {
      ElMessage.success('删除成功')
      loadArticles()
    }
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('删除失败')
    }
  }
}

const handleSizeChange = (val) => {
  pageSize.value = val
  loadArticles()
}

const handleCurrentChange = (val) => {
  currentPage.value = val
  loadArticles()
}

onMounted(() => {
  loadArticles()
  loadCategories()
})
</script>

<style scoped>
.articles-page {
  padding: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: bold;
}

.search-form {
  margin-bottom: 20px;
}
</style>
