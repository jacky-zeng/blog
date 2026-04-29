<template>
  <div class="comments-page">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>评论管理</span>
        </div>
      </template>

      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="关键词">
          <el-input v-model="searchForm.keyword" placeholder="请输入关键词" clearable />
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="searchForm.status" placeholder="请选择状态" clearable>
            <el-option label="待审核" :value="0" />
            <el-option label="已通过" :value="1" />
            <el-option label="已拒绝" :value="2" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">搜索</el-button>
          <el-button @click="handleReset">重置</el-button>
        </el-form-item>
      </el-form>

      <el-table :data="comments" v-loading="loading" style="width: 100%">
        <el-table-column prop="nickname" label="评论者" width="120" />
        <el-table-column prop="article.title" label="文章" width="200" />
        <el-table-column prop="content" label="评论内容" show-overflow-tooltip />
        <el-table-column label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.status)">
              {{ getStatusText(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="评论时间" width="180" />
        <el-table-column label="操作" width="200" fixed="right">
          <template #default="{ row }">
            <el-button type="success" size="small" @click="handleApprove(row)" v-if="row.status === 0">
              通过
            </el-button>
            <el-button type="danger" size="small" @click="handleReject(row)" v-if="row.status === 0">
              拒绝
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
const comments = ref([])
const currentPage = ref(1)
const pageSize = ref(10)
const total = ref(0)

const searchForm = ref({
  keyword: '',
  status: null
})

const loadComments = async () => {
  loading.value = true
  try {
    const res = await request.get('/admin/comments', {
      params: {
        page: currentPage.value,
        page_size: pageSize.value,
        ...searchForm.value
      }
    })
    if (res.code === 200) {
      comments.value = res.data.data
      total.value = res.data.total
    }
  } catch (error) {
    ElMessage.error('加载评论列表失败')
  } finally {
    loading.value = false
  }
}

const handleSearch = () => {
  currentPage.value = 1
  loadComments()
}

const handleReset = () => {
  searchForm.value = {
    keyword: '',
    status: null
  }
  currentPage.value = 1
  loadComments()
}

const handleApprove = async (row) => {
  try {
    const res = await request.put(`/admin/comments/${row.id}`, { status: 1 })
    if (res.code === 200) {
      ElMessage.success('审核通过')
      loadComments()
    }
  } catch (error) {
    ElMessage.error('操作失败')
  }
}

const handleReject = async (row) => {
  try {
    const res = await request.put(`/admin/comments/${row.id}`, { status: 2 })
    if (res.code === 200) {
      ElMessage.success('已拒绝')
      loadComments()
    }
  } catch (error) {
    ElMessage.error('操作失败')
  }
}

const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm('确定要删除这条评论吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    
    const res = await request.delete(`/admin/comments/${row.id}`)
    if (res.code === 200) {
      ElMessage.success('删除成功')
      loadComments()
    }
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('删除失败')
    }
  }
}

const handleSizeChange = (val) => {
  pageSize.value = val
  loadComments()
}

const handleCurrentChange = (val) => {
  currentPage.value = val
  loadComments()
}

const getStatusType = (status) => {
  const map = { 0: 'warning', 1: 'success', 2: 'danger' }
  return map[status] || 'info'
}

const getStatusText = (status) => {
  const map = { 0: '待审核', 1: '已通过', 2: '已拒绝' }
  return map[status] || '未知'
}

onMounted(() => {
  loadComments()
})
</script>

<style scoped>
.comments-page {
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
