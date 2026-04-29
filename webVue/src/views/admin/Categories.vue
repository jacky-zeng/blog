<template>
  <div class="categories-page">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>分类管理</span>
          <el-button type="primary" @click="handleAdd">新增分类</el-button>
        </div>
      </template>

      <el-table :data="categories" v-loading="loading" style="width: 100%">
        <el-table-column prop="name" label="分类名称" />
        <el-table-column prop="slug" label="URL别名" />
        <el-table-column prop="sort_order" label="排序" width="100" />
        <el-table-column prop="articles_count" label="文章数" width="100" />
        <el-table-column prop="created_at" label="创建时间" width="180" />
        <el-table-column label="操作" width="200" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" size="small" @click="handleEdit(row)">
              编辑
            </el-button>
            <el-button type="danger" size="small" @click="handleDelete(row)">
              删除
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <el-dialog v-model="dialogVisible" :title="dialogTitle" width="500px">
      <el-form :model="form" :rules="rules" ref="formRef" label-width="100px">
        <el-form-item label="分类名称" prop="name">
          <el-input v-model="form.name" placeholder="请输入分类名称" />
        </el-form-item>
        <el-form-item label="URL别名" prop="slug">
          <el-input v-model="form.slug" placeholder="请输入URL别名" />
        </el-form-item>
        <el-form-item label="排序" prop="sort_order">
          <el-input-number v-model="form.sort_order" :min="0" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit" :loading="loading">
          确定
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import request from '@/utils/request'

const loading = ref(false)
const categories = ref([])
const dialogVisible = ref(false)
const dialogTitle = ref('新增分类')
const formRef = ref(null)
const isEdit = ref(false)
const currentId = ref(null)

const form = ref({
  name: '',
  slug: '',
  sort_order: 0
})

const rules = {
  name: [
    { required: true, message: '请输入分类名称', trigger: 'blur' }
  ],
  slug: [
    { required: true, message: '请输入URL别名', trigger: 'blur' }
  ]
}

const loadCategories = async () => {
  loading.value = true
  try {
    const res = await request.get('/admin/categories')
    if (res.code === 200) {
      categories.value = res.data
    }
  } catch (error) {
    ElMessage.error('加载分类列表失败')
  } finally {
    loading.value = false
  }
}

const handleAdd = () => {
  isEdit.value = false
  dialogTitle.value = '新增分类'
  form.value = { name: '', slug: '', sort_order: 0 }
  dialogVisible.value = true
}

const handleEdit = (row) => {
  isEdit.value = true
  dialogTitle.value = '编辑分类'
  currentId.value = row.id
  form.value = { name: row.name, slug: row.slug, sort_order: row.sort_order }
  dialogVisible.value = true
}

const handleSubmit = async () => {
  if (!formRef.value) return
  
  await formRef.value.validate(async (valid) => {
    if (valid) {
      loading.value = true
      try {
        const url = isEdit.value ? `/admin/categories/${currentId.value}` : '/admin/categories'
        const method = isEdit.value ? 'put' : 'post'
        
        const res = await request[method](url, form.value)
        if (res.code === 200) {
          ElMessage.success(isEdit.value ? '更新成功' : '创建成功')
          dialogVisible.value = false
          loadCategories()
        }
      } catch (error) {
        ElMessage.error(isEdit.value ? '更新失败' : '创建失败')
      } finally {
        loading.value = false
      }
    }
  })
}

const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm('确定要删除这个分类吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    
    const res = await request.delete(`/admin/categories/${row.id}`)
    if (res.code === 200) {
      ElMessage.success('删除成功')
      loadCategories()
    }
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error(error.message || '删除失败')
    }
  }
}

onMounted(() => {
  loadCategories()
})
</script>

<style scoped>
.categories-page {
  padding: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: bold;
}
</style>
