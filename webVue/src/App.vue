<template>
  <div class="app-container">
    <div class="global-bg" v-if="isHomePage"></div>
    <router-view />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const isHomePage = computed(() => route.path === '/')
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
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
