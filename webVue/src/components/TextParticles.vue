<template>
  <div ref="containerRef" class="text-particles"></div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import * as THREE from 'three'

const containerRef = ref<HTMLDivElement>()

// ---- 单词配置 ----
const WORDS = [
  { text: 'swoole',   fontSize: 28, speed: 0.08, color: [0.70, 1.00, 1.00] },
  { text: 'PHP',      fontSize: 22, speed: 0.12, color: [0.90, 0.90, 1.00] },
  { text: 'Hyperf',   fontSize: 26, speed: 0.09, color: [1.00, 0.85, 0.85] },
  { text: 'mysql',    fontSize: 20, speed: 0.11, color: [0.50, 0.90, 1.00] },
  { text: 'docker',   fontSize: 22, speed: 0.10, color: [0.70, 0.95, 1.00] },
  { text: 'rabbitmq', fontSize: 18, speed: 0.07, color: [1.00, 0.85, 0.55] },
  { text: 'laravel',  fontSize: 20, speed: 0.09, color: [1.00, 0.75, 0.65] },
  { text: 'yaf',      fontSize: 26, speed: 0.14, color: [0.95, 0.80, 1.00] },
  { text: 'ThinkPHP', fontSize: 18, speed: 0.07, color: [0.85, 0.95, 1.00] },
  { text: 'Yii',      fontSize: 24, speed: 0.13, color: [0.80, 1.00, 1.00] },
  { text: 'Redis',    fontSize: 20, speed: 0.10, color: [1.00, 0.70, 0.65] },
  { text: 'css',      fontSize: 22, speed: 0.09, color: [0.30, 0.80, 1.00] },
  { text: 'vue',      fontSize: 24, speed: 0.11, color: [0.50, 0.95, 0.70] },
]

interface WordState {
  x: number, y: number, z: number
  width: number, height: number
  particleCount: number, startIndex: number
  speed: number
}

let scene: THREE.Scene
let camera: THREE.OrthographicCamera
let renderer: THREE.WebGLRenderer
let particles: THREE.Points
let clock: THREE.Clock
let rafId = 0
let wordStates: WordState[] = []
let origPos: Float32Array | null = null

const mouse = { x: 9999, y: 9999 }
let isMouseActive = false
let halfW = 8, halfH = 5

const PX = 2.0 // 一个 canvas 高度对应 2 个世界单位

onMounted(async () => {
  try {
    await nextTick()

    if (!containerRef.value) {
      console.warn('[TextParticles] containerRef is null')
      return
    }
    const w = containerRef.value.clientWidth
    const h = containerRef.value.clientHeight
    if (w === 0 || h === 0) {
      console.warn('[TextParticles] container has zero size', w, h)
    }

    initScene()
    generateWords()

    const totalP = (particles?.geometry?.attributes?.position?.count ?? 0)
    console.log(`[TextParticles] 已初始化，总粒子数: ${totalP}`)

    animate()
    window.addEventListener('mousemove', onMouseMove, { passive: true })
    window.addEventListener('mouseleave', onMouseLeave, { passive: true })
    window.addEventListener('resize', onResize)
  } catch (e) {
    console.error('[TextParticles] 初始化失败:', e)
  }
})

onUnmounted(() => {
  window.removeEventListener('mousemove', onMouseMove)
  window.removeEventListener('mouseleave', onMouseLeave)
  window.removeEventListener('resize', onResize)
  cancelAnimationFrame(rafId)
  renderer?.dispose()
  scene?.clear()
})

function initScene() {
  const el = containerRef.value!
  const w = el.clientWidth || 1920
  const h = el.clientHeight || 1080
  const aspect = w / h
  const viewSize = 10
  halfW = viewSize * aspect / 2
  halfH = viewSize / 2

  scene = new THREE.Scene()
  clock = new THREE.Clock()

  camera = new THREE.OrthographicCamera(-halfW, halfW, halfH, -halfH, 0.1, 100)
  camera.position.z = 10

  renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true })
  renderer.setSize(w, h)
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2))
  renderer.setClearColor(0x000000, 0)
  el.appendChild(renderer.domElement)
}

function sampleTextPixels(text: string, fontSize: number) {
  const cvsH = Math.max(fontSize * 1.8, 60)
  const cvsW = Math.max(text.length * fontSize * 0.85, 120)
  const canvas = document.createElement('canvas')
  canvas.width = Math.ceil(cvsW)
  canvas.height = Math.ceil(cvsH)
  const ctx = canvas.getContext('2d')!

  ctx.fillStyle = '#ffffff'
  ctx.font = `bold ${fontSize}px Arial, Helvetica, sans-serif`
  ctx.textAlign = 'center'
  ctx.textBaseline = 'middle'
  ctx.fillText(text, canvas.width / 2, canvas.height / 2)

  const { data, width, height } = ctx.getImageData(0, 0, canvas.width, canvas.height)
  const ratio = canvas.width / canvas.height

  const pts: number[] = []
  const step = 2

  for (let y = 0; y < height; y += step) {
    for (let x = 0; x < width; x += step) {
      if (data[(y * width + x) * 4] > 128) {
        pts.push((x / width - 0.5) * ratio * PX)  // x
        pts.push(-(y / height - 0.5) * PX)         // y
        pts.push((Math.random() - 0.5) * 0.3)      // z
      }
    }
  }

  return { positions: new Float32Array(pts), worldW: ratio * PX }
}

function generateWords() {
  const sampled = WORDS.map(w => ({
    config: w,
    data: sampleTextPixels(w.text, w.fontSize),
  }))

  const totalParticles = sampled.reduce((s, d) => s + d.data.positions.length / 3, 0)
  console.log(`[TextParticles] 总粒子数: ${totalParticles}`)

  const allPos = new Float32Array(totalParticles * 3)
  const allCol = new Float32Array(totalParticles * 3)
  let offset = 0
  wordStates = []

  // Y 方向均分布局
  const topY = halfH * 0.7
  const botY = -halfH * 0.7
  const slotH = (topY - botY) / sampled.length

  for (let i = 0; i < sampled.length; i++) {
    const s = sampled[i]
    const count = s.data.positions.length / 3
    const yPos = topY - slotH * (i + 0.5)
    const zPos = (Math.random() - 0.5) * 3
    const xInit = -halfW + Math.random() * halfW * 2

    wordStates.push({
      x: xInit, y: yPos, z: zPos,
      width: s.data.worldW, height: PX,
      particleCount: count, startIndex: offset / 3,
      speed: s.config.speed,
    })

    for (let j = 0; j < count; j++) {
      const pi = offset + j * 3
      // 相对坐标（相对单词中心）
      allPos[pi]     = s.data.positions[j * 3]
      allPos[pi + 1] = s.data.positions[j * 3 + 1]
      allPos[pi + 2] = s.data.positions[j * 3 + 2]

      const c = s.config.color
      const v = 0.08
      allCol[pi]     = Math.max(0, Math.min(1, c[0] + (Math.random() - 0.5) * v))
      allCol[pi + 1] = Math.max(0, Math.min(1, c[1] + (Math.random() - 0.5) * v))
      allCol[pi + 2] = Math.max(0, Math.min(1, c[2] + (Math.random() - 0.5) * v))
    }

    offset += count * 3
  }

  // 保存相对坐标
  origPos = new Float32Array(allPos)

  // 转为世界坐标
  for (const ws of wordStates) {
    for (let j = 0; j < ws.particleCount; j++) {
      const pi = (ws.startIndex + j) * 3
      allPos[pi]     += ws.x
      allPos[pi + 1] += ws.y
      allPos[pi + 2] += ws.z
    }
  }

  const geo = new THREE.BufferGeometry()
  geo.setAttribute('position', new THREE.BufferAttribute(allPos, 3))
  geo.setAttribute('color', new THREE.BufferAttribute(allCol, 3))

  // 粒子纹理（大发光圆点）
  const tc = document.createElement('canvas')
  tc.width = tc.height = 64
  const tctx = tc.getContext('2d')!
  const g = tctx.createRadialGradient(32, 32, 0, 32, 32, 32)
  g.addColorStop(0, 'rgba(255,255,255,1)')
  g.addColorStop(0.25, 'rgba(255,255,255,1)')
  g.addColorStop(0.5, 'rgba(255,255,255,0.85)')
  g.addColorStop(1, 'rgba(255,255,255,0)')
  tctx.fillStyle = g
  tctx.fillRect(0, 0, 64, 64)
  const tex = new THREE.CanvasTexture(tc)

  const mat = new THREE.PointsMaterial({
    size: 1.5,
    vertexColors: true,
    transparent: true,
    opacity: 1,
    blending: THREE.AdditiveBlending,
    depthWrite: false,
    map: tex,
    sizeAttenuation: true,
  })

  particles = new THREE.Points(geo, mat)
  scene.add(particles)
}

function onMouseMove(e: MouseEvent) {
  const el = containerRef.value!
  const rect = el.getBoundingClientRect()
  isMouseActive = true
  mouse.x = ((e.clientX - rect.left) / rect.width) * 2 - 1
  mouse.y = -((e.clientY - rect.top) / rect.height) * 2 + 1
}

function onMouseLeave() {
  isMouseActive = false
}

function animate() {
  rafId = requestAnimationFrame(animate)

  if (!particles || !origPos) return

  const delta = Math.min(clock.getDelta(), 0.05)
  const time = clock.getElapsedTime()

  const el = containerRef.value!
  if (!el) return
  const aspect = el.clientWidth / el.clientHeight
  halfW = 5 * aspect
  halfH = 5

  camera.left = -halfW
  camera.right = halfW
  camera.top = halfH
  camera.bottom = -halfH
  camera.updateProjectionMatrix()

  const worldMouseX = mouse.x * halfW
  const worldMouseY = mouse.y * halfH

  const pos = particles.geometry.attributes.position.array as Float32Array
  const orig = origPos
  const totalCount = pos.length / 3

  // 移动单词
  for (const ws of wordStates) {
    ws.x -= ws.speed * delta * 18
    // 移出左边后，直接从最右侧重新开始（粒子位置同步重置，避免 lerp 造成快速飞过效果）
    if (ws.x + ws.width / 2 < -halfW) {
      ws.x = halfW + ws.width / 2
      for (let j = 0; j < ws.particleCount; j++) {
        const pi = (ws.startIndex + j) * 3
        pos[pi]     = orig[pi]     + ws.x
        pos[pi + 1] = orig[pi + 1] + ws.y
        pos[pi + 2] = orig[pi + 2] + ws.z
      }
    }
  }

  // 更新粒子位置
  for (let i = 0; i < totalCount; i++) {
    const i3 = i * 3
    const ox = orig[i3], oy = orig[i3 + 1], oz = orig[i3 + 2]
    const ws = wordStates.find(w => i >= w.startIndex && i < w.startIndex + w.particleCount)
    if (!ws) continue

    const tx = ox + ws.x
    const ty = oy + ws.y
    const tz = oz + ws.z

    const dx = tx - worldMouseX
    const dy = ty - worldMouseY
    const dist = Math.sqrt(dx * dx + dy * dy)

    if (dist < 4 && isMouseActive) {
      const s = (1 - dist / 4) ** 2 * 0.6
      pos[i3]     = tx + Math.sin(time * 2 + ty * 2) * s
      pos[i3 + 1] = ty + Math.cos(time * 2 + tx * 2) * s
      pos[i3 + 2] = tz + Math.sin(time * 2 + tx + ty) * s * 0.3
    } else {
      pos[i3]     += (tx - pos[i3]) * 0.05
      pos[i3 + 1] += (ty - pos[i3 + 1]) * 0.05
      pos[i3 + 2] += (tz - pos[i3 + 2]) * 0.05
    }
  }

  particles.geometry.attributes.position.needsUpdate = true

  const breathe = 1 + Math.sin(time * 0.6) * 0.06
  particles.material.size = 1.5 * breathe

  renderer.render(scene, camera)
}

function onResize() {
  if (!containerRef.value || !renderer) return
  renderer.setSize(containerRef.value.clientWidth, containerRef.value.clientHeight)
}
</script>

<style scoped>
.text-particles {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 0;
  pointer-events: none;
}
</style>
