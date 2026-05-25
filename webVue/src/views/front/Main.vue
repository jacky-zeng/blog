<template>
  <div class="main-page">
    <div class="content">
      <p class="subtitle">业精于勤荒于嬉 行成于思毁于随</p>
      <h1 class="title">我是<span class="name">曾彦琪</span></h1>
      <div class="typing-container">
        <p class="typing-text">{{ displayedText }}</p>
        <span class="cursor" :class="{ blink: !isTyping }">|</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const textList = [
  '一名可爱的程序员',
  '一位PHP开发者',
  '一位Go开发者',
  '工作专注高效,认真负责,自驱能力强',
  '会使用VibeCoding进行全栈开发',
  '能独立部署运维服务器'
]

const displayedText = ref('')
const isTyping = ref(true)
let currentTextIndex = 0
let currentCharIndex = 0
let typingTimer = null
let pauseTimer = null

const typeNextChar = () => {
  if (currentTextIndex >= textList.length) {
    currentTextIndex = 0
    currentCharIndex = 0
    displayedText.value = ''
    typeNextChar()
    return
  }

  const currentText = textList[currentTextIndex]

  if (currentCharIndex < currentText.length) {
    displayedText.value += currentText[currentCharIndex]
    currentCharIndex++
    isTyping.value = true
    typingTimer = setTimeout(typeNextChar, 150)
  } else {
    isTyping.value = false
    pauseTimer = setTimeout(() => {
      currentTextIndex++
      currentCharIndex = 0
      displayedText.value = ''
      isTyping.value = true
      typeNextChar()
    }, 1200)
  }
}

const cleanup = () => {
  if (typingTimer) clearTimeout(typingTimer)
  if (pauseTimer) clearTimeout(pauseTimer)
}

onMounted(() => {
  typeNextChar()
})

import { onUnmounted } from 'vue'
onUnmounted(() => {
  cleanup()
})
</script>

<style scoped>
.main-page {
  height: 70vh;
  background-color: #000;
  display: flex;
  justify-content: center;
  align-items: center;
  position: relative;
  overflow: hidden;
}

.content {
  text-align: center;
}

.subtitle {
  font-size: 16px;
  color: #999;
  margin: 0 0 20px 0;
}

.title {
  font-size: 48px;
  color: #989db9;
  margin: 0 0 30px 0;
  font-weight: bold;
}

.name {
    font-size: 61px;
    color: #fff;
}

.typing-container {
  display: inline-flex;
  align-items: center;
  font-size: 18px;
  color: #e3e3f8;
  min-height: 24px;
}

.typing-text {
  margin: 0;
  white-space: nowrap;
}

.cursor {
  color: #fff;
  font-weight: bold;
  animation: cursor-blink 0.7s infinite;
}

.cursor.blink {
  animation: none;
}

@keyframes cursor-blink {
  0%, 50% {
    opacity: 1;
  }
  51%, 100% {
    opacity: 0;
  }
}
</style>