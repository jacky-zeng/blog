<template>
  <div class="resume-page">
    <el-row :gutter="20">
      <el-col :span="6" v-if="!isMobile">
        <el-card class="sidebar-card">
          <template #header>
            <span>基本信息</span>
          </template>
          <div class="avatar-container">
            <img src="/images/avatar.png" alt="头像" class="avatar-circle" />
          </div>
          <div class="info-item">
            <span class="label">姓名：</span>
            <span>曾彦琪</span>
          </div>
          <div class="info-item">
            <span class="label">性别：</span>
            <span>男</span>
          </div>
          <div class="info-item">
            <span class="label">工作年限：</span>
            <span>13年</span>
          </div>
          <div class="info-item">
            <span class="label">求职意向：</span>
            <span>PHP/全栈</span>
          </div>
          <div class="info-item">
            <span class="label">期望城市：</span>
            <span>上海</span>
          </div>
          <div class="info-item">
            <span class="label">期望薪资：</span>
            <span>25-30K</span>
          </div>
          <div class="info-item">
            <span class="label">联系电话：</span>
            <span>15900493964</span>
          </div>
        </el-card>

        <el-card class="sidebar-card" style="margin-top: 20px;">
          <template #header>
            <span>技术技能</span>
          </template>
          <div class="skill-category">
            <div class="skill-title">后端语言 & 框架</div>
            <div class="skill-tags">
              <el-tag size="small">PHP</el-tag>
              <el-tag size="small">Laravel</el-tag>
              <el-tag size="small">Yii</el-tag>
              <el-tag size="small">Yaf</el-tag>
              <el-tag size="small">ThinkPHP</el-tag>
              <el-tag size="small">Hyperf</el-tag>
              <el-tag size="small">Go</el-tag>
              <el-tag size="small">C# / .NET</el-tag>
            </div>
          </div>
          <div class="skill-category">
            <div class="skill-title">数据库 & 缓存</div>
            <div class="skill-tags">
              <el-tag size="small">MySQL</el-tag>
              <el-tag size="small">Redis</el-tag>
              <el-tag size="small">SQL Server</el-tag>
            </div>
          </div>
          <div class="skill-category">
            <div class="skill-title">中间件 & 基础设施</div>
            <div class="skill-tags">
              <el-tag size="small">Nginx</el-tag>
              <el-tag size="small">RabbitMQ</el-tag>
              <el-tag size="small">阿里云MQ</el-tag>
              <el-tag size="small">ElasticSearch</el-tag>
            </div>
          </div>
          <div class="skill-category">
            <div class="skill-title">架构 & 方法论</div>
            <div class="skill-tags">
              <el-tag size="small">高并发系统设计</el-tag>
              <el-tag size="small">微服务架构</el-tag>
              <el-tag size="small">分布式系统</el-tag>
              <el-tag size="small">RESTful API</el-tag>
            </div>
          </div>
          <div class="skill-category">
            <div class="skill-title">业务领域 & 集成</div>
            <div class="skill-tags">
              <el-tag size="small">电商交易系统</el-tag>
              <el-tag size="small">微信生态</el-tag>
              <el-tag size="small">支付宝生态</el-tag>
              <el-tag size="small">IoT硬件对接</el-tag>
            </div>
          </div>
          <div class="skill-category">
            <div class="skill-title">运维 & DevOps</div>
            <div class="skill-tags">
              <el-tag size="small">Linux部署</el-tag>
              <el-tag size="small">性能监控</el-tag>
              <el-tag size="small">故障分析与解决</el-tag>
              <el-tag size="small">慢查询分析</el-tag>
            </div>
          </div>
        </el-card>
      </el-col>

      <el-col :span="isMobile ? 24 : 18">
        <el-card v-if="isMobile" class="main-card">
          <template #header>
            <span>基本信息</span>
          </template>
          <div class="avatar-container-mobile">
            <img src="/images/avatar.png" alt="头像" class="avatar-circle-mobile" />
          </div>
          <div class="mobile-info-grid">
            <div class="info-item">
              <span class="label">姓名：</span>
              <span>曾彦琪</span>
            </div>
            <div class="info-item">
              <span class="label">性别：</span>
              <span>男</span>
            </div>
            <div class="info-item">
              <span class="label">工作年限：</span>
              <span>13年</span>
            </div>
            <div class="info-item">
              <span class="label">求职：</span>
              <span>PHP/全栈</span>
            </div>
            <div class="info-item">
              <span class="label">期望城市：</span>
              <span>上海</span>
            </div>
            <div class="info-item">
              <span class="label">期望薪资：</span>
              <span>25-30K</span>
            </div>
            <div class="info-item">
              <span class="label">联系电话：</span>
              <span>15900493964</span>
            </div>
          </div>
        </el-card>
        <br/>
        <el-card class="main-card">
          <template #header>
            <div class="card-header">
              <span>个人优势</span>
            </div>
          </template>
          <div class="advantage-item">
            <span class="highlight">10年后端开发经验，5年后端架构经验</span>，深耕电商交易与营销系统，对订单、支付、商品、会员等核心域拥有深刻认知；熟悉<span class="highlight">高并发、负载均衡架构设计</span>，具备从0到1搭建百万~千万级用户平台的能力。
          </div>
          <div class="advantage-item">
            主导<span class="highlight">千万级用户、日活200万</span>电商平台的核心系统架构演进，通过<span class="highlight">分库分表、索引优化、Redis多级缓存及异步队列</span>等策略，使系统稳定支撑峰值流量洪峰，保障核心交易链路高可用。
          </div>
          <div class="advantage-item">
            具备<span class="highlight">跨团队技术规划与项目管理能力</span>，曾担任PHP开发主管，带领团队从零搭建完整商城系统，个人贡献超80%核心代码；通过技术方案评审、代码规范制定与内部分享，持续提升团队整体交付质量。
          </div>
          <div class="advantage-item">
            善于<span class="highlight">通过技术驱动业务增长</span>——搭建营销引擎（满减/拼团/优惠券/分销），支撑灵活多变的运营策略。
          </div>
        </el-card>

        <el-card class="main-card" style="margin-top: 20px;">
          <template #header>
            <div class="card-header">
              <span>工作经历</span>
            </div>
          </template>
          
          <div class="job-item">
            <div class="job-header">
              <h3 class="company">上海亿思正网络技术有限公司</h3>
              <el-tag size="small" type="info">资深PHP开发工程师</el-tag>
              <span class="date">2021.10 — 至今</span>
            </div>
            <div class="project-name">易思政云管理后台 & 多端API平台 & 内部大后台全栈开发</div>
            <ul class="job-desc">
              <li>主导基于<span class="highlight">Yaf框架</span>的后台管理系统架构设计与核心开发，为PC端、微信公众号及微信小程序统一提供接口和文档，实现一套服务支撑三端接入，公司内部使用的大后台则由我全栈开发。</li>
              <li>独立负责线上服务器环境部署及运维（<span class="highlight">Nginx + PHP + MySQL + Redis + RabbitMq</span>），建立标准化的持续集成与发布流程，保障服务稳定运行。</li>
              <li>为客户搭建<span class="highlight">Ai Agent工作流</span>，根据客户提供的文档生成知识库。</li>
              <li>作为技术侧核心对接人，与合作方公司进行<span class="highlight">跨组织业务讨论与技术方案设计</span>，完成多系统间的数据与接口集成，推动业务快速落地。</li>
            </ul>
          </div>

          <div class="job-item">
            <div class="job-header">
              <h3 class="company">上海优动网络科技有限公司</h3>
              <el-tag size="small" type="info">高级PHP研发工程师</el-tag>
              <span class="date">2021.01 — 2021.09</span>
            </div>
            <div class="project-name">今日爆团小程序 — 电商平台</div>
            <ul class="job-desc">
              <li>现有存量业务基于<span class="highlight">Yii2.0</span>框架迭代落地，负责日常迭代维护与线上问题缺陷修复工作。</li>
              <li>负责后端架构部分模块的<span class="highlight">docker微服务开发</span>：使用<span class="highlight">Hyperf</span>拆分营销计算、优惠引擎、订单推送等模块为独立微服务，降低系统耦合度，提升模块可维护性与扩展性。</li>
              <li>负责<span class="highlight">海淘跨境业务</span>全链路开发：设计海淘商品税费计算模型，串联订单流转、<span class="highlight">阿里云MQ异步推送微信报关</span>、商家订单拉取与发货接口，打通跨境购物完整闭环。</li>
              <li><span class="highlight">高并发架构优化</span>：面对<span class="highlight">千万级用户、日活200万</span>的业务体量，采用数据库分库分表方案设计与索引优化，将热数据迁移至Redis多级缓存，统计类业务下沉至定时任务异步处理，大幅降低数据库负载，保障业务高峰期系统响应。</li>
            </ul>
          </div>

          <div class="job-item">
            <div class="job-header">
              <h3 class="company">上海米宅实业有限公司</h3>
              <el-tag size="small" type="info">高级PHP研发工程师</el-tag>
              <span class="date">2018.04 — 2021.01</span>
            </div>
            <div class="project-name">米域智能空间系统 — O2O智慧办公平台</div>
            <ul class="job-desc">
              <li>主导基于<span class="highlight">Laravel</span>框架的后端架构设计与全栈开发（Admin-LTE + MySQL + Redis），构建集空间管理、会员运营、订单交易于一体的智慧办公SaaS平台。</li>
              <li><span class="highlight">多端统一API</span>：为APP端及微信小程序设计并输出标准化接口，支撑移动端办公场景全覆盖。</li>
              <li><span class="highlight">IoT智能硬件集成</span>：主导<span class="highlight">蓝牙/人脸门禁</span>系统对接、<span class="highlight">云打印</span>服务集成及网络管理模块开发，打通物理空间与数字系统的交互链路，实现办公场景智能化。</li>
            </ul>
          </div>

          <div class="job-item">
            <div class="job-header">
              <h3 class="company">上海酷少网络科技有限公司</h3>
              <el-tag size="small" type="info">高级PHP开发工程师</el-tag>
              <span class="date">2017.04 — 2018.03</span>
            </div>
            <div class="project-name">B2B2C电商平台</div>
            <ul class="job-desc">
              <li>主导核心交易链路开发：<span class="highlight">购物车、订单、支付、营销引擎</span>及Admin后台，担任关键模块主程，深度参与系统架构决策。</li>
              <li><span class="highlight">秒杀/抢购场景</span>：设计基于<span class="highlight">Redis + 队列任务</span>的抢购方案，通过异步削峰与库存预扣机制，平稳支撑高并发抢购流量。</li>
            </ul>
          </div>

          <div class="job-item">
            <div class="job-header">
              <h3 class="company">上海未已网络技术有限公司</h3>
              <el-tag size="small" type="info">PHP开发主管</el-tag>
              <span class="date">2016.06 — 2017.04</span>
            </div>
            <div class="project-name">Laravel商城系统 — 从0到1搭建（创业项目）</div>
            <ul class="job-desc">
              <li><span class="highlight">技术选型与架构规划</span>：主导Laravel商城的技术选型、框架搭建与核心架构设计，制定团队开发规范与代码标准。</li>
              <li><span class="highlight">团队管理与交付</span>：带领团队完成项目攻坚，个人贡献超80%核心功能代码，涵盖会员、商品、订单、支付、报表、日志、售后七大核心模块。</li>
              <li><span class="highlight">数据分析系统</span>：集成Highcharts实现商品销售趋势、PV/UV流量趋势、访客地域分布等可视化看板。</li>
            </ul>
          </div>

          <div class="job-item">
            <div class="job-header">
              <h3 class="company">上海蚁城网络科技有限公司</h3>
              <el-tag size="small" type="info">PHP开发工程师</el-tag>
              <span class="date">2015.08 — 2016.06</span>
            </div>
            <div class="project-name">Ecstore商城功能扩展</div>
            <ul class="job-desc">
              <li>对ecstore商城原有项目的维护及功能新增。</li>
              <li>开发新功能插件：<span class="highlight">跨境包</span>、<span class="highlight">微信绑定</span>、<span class="highlight">会员卡</span>、<span class="highlight">拼团</span>、<span class="highlight">微分销</span>。</li>
            </ul>
          </div>

          <div class="job-item">
            <div class="job-header">
              <h3 class="company">广州市福思特科技有限公司</h3>
              <el-tag size="small" type="info">.NET开发工程师</el-tag>
              <span class="date">2013.07 — 2015.08</span>
            </div>
            <div class="project-name">财务类教学模拟实训平台</div>
            <ul class="job-desc">
              <li>参与税收实务、审计之友、出纳实务、财务管理等<span class="highlight">多款教学软件</span>的维护与功能升级，使用<span class="highlight">C# + ASP.NET三层架构 + SQL Server</span>。</li>
              <li>主导前端界面现代化改造：使用<span class="highlight">EasyUI</span>替换老旧界面，提升用户体验。</li>
            </ul>
          </div>
        </el-card>

        <el-card class="main-card" style="margin-top: 20px;">
          <template #header>
            <div class="card-header">
              <span>教育经历</span>
            </div>
          </template>
          <div class="edu-item">
            <span class="edu-school">江西财经大学</span>
            <span class="edu-major">软件工程 · 本科</span>
            <span class="edu-date">2009 — 2013</span>
          </div>
          <div class="edu-cards">
            <div class="card-item">
              <img src="/images/ka1.png" alt="校友卡正面" class="edu-card" />
              <span class="card-label">校友卡正面</span>
            </div>
            <div class="card-item">
              <img src="/images/ka2.png" alt="校友卡背面" class="edu-card" />
              <span class="card-label">校友卡背面</span>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ElMessage } from 'element-plus'

const isMobile = ref(false)

onMounted(() => {
  isMobile.value = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)
})
</script>

<style scoped>
.resume-page {
    padding: 20px;
    background-color: #ffffff;
    min-height: calc(100vh - 60px);
    position: absolute;
    top: 60px;
    left: 0;
    right: 0;
    bottom: 0;
}

@media (max-width: 768px) {
  .resume-page {
    padding: 1px;
  }
}

.main-card,
.sidebar-card {
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.card-header {
  font-weight: bold;
  font-size: 16px;
  color: #333;
}

.highlight {
  color: #2d4a8e;
  font-weight: 600;
}

.advantage-item {
  padding: 12px 0;
  border-bottom: 1px solid #f0f0f0;
  line-height: 1.8;
  color: #666;
  font-size: 14px;
}

.advantage-item:last-child {
  border-bottom: none;
}

.job-item {
  padding: 20px 0;
  border-bottom: 1px solid #f0f0f0;
}

.job-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.job-header {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 10px;
}

.company {
  font-size: 16px;
  font-weight: 600;
  color: #333;
  margin: 0;
}

.date {
  font-size: 14px;
  color: #999;
  margin-left: auto;
}

.project-name {
  color: #1a1a2e;
  font-weight: 500;
  margin-bottom: 12px;
  font-size: 14px;
}

.job-desc {
  margin: 0;
  padding-left: 20px;
  color: #666;
  font-size: 14px;
  line-height: 1.8;
}

.job-desc li {
  margin-bottom: 8px;
}

.job-desc li:last-child {
  margin-bottom: 0;
}

.edu-item {
  display: flex;
  align-items: center;
  gap: 20px;
  flex-wrap: wrap;
  color: #666;
  font-size: 14px;
}

.edu-school {
  font-weight: 600;
  color: #333;
}

.edu-major {
  color: #666;
}

.edu-date {
  color: #999;
}

.info-item {
  padding: 8px 0;
  border-bottom: 1px solid #f0f0f0;
  font-size: 14px;
  color: #666;
}

.info-item:last-child {
  border-bottom: none;
}

.info-item .label {
  color: #999;
}

.skill-category {
  margin-bottom: 16px;
}

.skill-category:last-child {
  margin-bottom: 0;
}

.skill-title {
  font-weight: 600;
  color: #333;
  margin-bottom: 8px;
  font-size: 14px;
}

.skill-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.mobile-info-card {
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.mobile-info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0;
}

.avatar-container {
  display: flex;
  justify-content: center;
  margin-bottom: 16px;
}

.avatar-circle {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #f0f0f0;
}

.avatar-container-mobile {
  display: flex;
  justify-content: center;
  margin-bottom: 16px;
}

.avatar-circle-mobile {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #f0f0f0;
}

.edu-cards {
  display: flex;
  justify-content: center;
  gap: 20px;
  margin-top: 20px;
  flex-wrap: wrap;
}

.card-item {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.edu-card {
  max-width: 200px;
  max-height: 150px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  object-fit: contain;
}

.card-label {
  font-size: 12px;
  color: #999;
  margin-top: 8px;
}

@media (max-width: 768px) {
  .edu-card {
    max-width: 200px;
    max-height: 145px;
  }
  
  .edu-cards {
    gap: 10px;
  }
}
</style>
