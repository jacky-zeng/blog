<template>
  <div class="app-container">
    <div class="global-bg" v-if="isHomePage && !isMobile"></div>
    <router-view />
  </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const isHomePage = computed(() => route.path === '/')
const isMobile = ref(false)

onMounted(() => {
  isMobile.value = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)
})
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html, body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

@media (max-width: 768px) {
  html, body {
    overflow-x: hidden;
    width: 100%;
  }
}

.app-container {
  position: relative;
  min-height: 100vh;
}

.global-bg {
  position: fixed;
  top: 0;
  right: 0;
  width: 800px;
  height: 800px;
  background-image: url('/storage/bg/bg.png');
  background-size: contain;
  background-repeat: no-repeat;
  background-position: top right;
  z-index: 0;
  pointer-events: none;
}

.router-view {
  position: relative;
  z-index: 1;
}
</style>
