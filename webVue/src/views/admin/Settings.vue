<template>
  <div class="settings-page">
    <el-card>
      <template #header>
        <span>系统设置</span>
      </template>

      <el-form :model="form" label-width="120px">
        <el-divider content-position="left">博客设置</el-divider>
        
        <el-form-item label="网站名称">
          <el-input v-model="form.site_name" placeholder="请输入网站名称" />
        </el-form-item>
        
        <el-form-item label="网站副标题">
          <el-input v-model="form.site_subtitle" placeholder="请输入网站副标题" />
        </el-form-item>
        
        <el-form-item label="网站Logo">
          <el-input v-model="form.logo" placeholder="请输入Logo URL" />
        </el-form-item>
        
        <el-form-item label="备案号">
          <el-input v-model="form.icp" placeholder="请输入备案号" />
        </el-form-item>

        <el-divider content-position="left">作者设置</el-divider>
        
        <el-form-item label="作者名称">
          <el-input v-model="form.author_name" placeholder="请输入作者名称" />
        </el-form-item>
        
        <el-form-item label="作者简介">
          <el-input v-model="form.author_bio" type="textarea" :rows="3" placeholder="请输入作者简介" />
        </el-form-item>

        <el-divider content-position="left">评论设置</el-divider>
        
        <el-form-item label="开启评论">
          <el-switch v-model="form.comment_enabled" :active-value="1" :inactive-value="0" />
        </el-form-item>
        
        <el-form-item label="评论审核">
          <el-switch v-model="form.comment_audit" :active-value="1" :inactive-value="0" />
        </el-form-item>

        <el-form-item>
          <el-button type="primary" @click="handleSubmit" :loading="loading">
            保存设置
          </el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import request from '@/utils/request'

const loading = ref(false)

const form = ref({
  site_name: '',
  site_subtitle: '',
  logo: '',
  icp: '',
  author_name: '',
  author_bio: '',
  comment_enabled: 1,
  comment_audit: 1
})

const loadSettings = async () => {
  try {
    const res = await request.get('/admin/settings')
    if (res.code === 200) {
      const data = res.data
      Object.assign(form.value, data)
      // 将字符串类型的开关值转换为数字
      if (data.comment_enabled !== undefined) {
        form.value.comment_enabled = parseInt(data.comment_enabled) || 0
      }
      if (data.comment_audit !== undefined) {
        form.value.comment_audit = parseInt(data.comment_audit) || 0
      }
    }
  } catch (error) {
    ElMessage.error('加载设置失败')
  }
}

const handleSubmit = async () => {
  loading.value = true
  try {
    const res = await request.put('/admin/settings', form.value)
    if (res.code === 200) {
      ElMessage.success('保存成功')
    }
  } catch (error) {
    ElMessage.error('保存失败')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadSettings()
})
</script>

<style scoped>
.settings-page {
  padding: 20px;
}
</style>
