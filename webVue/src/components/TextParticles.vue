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

interface DandelionState {
  baseX: number; baseY: number; baseZ: number
  scale: number
  particleCount: number
  startIndex: number
  swaySpeed: number
  swayAmount: number
  swayPhase: number
  exploding: boolean
  explodeTime: number
  explosionVels: Float32Array | null
  seedSprites: THREE.Sprite[] | null
  seedSpriteVels: Float32Array | null
  tint: [number, number, number] | null
  spriteRelPos: Float32Array | null     // 常驻种子的相对坐标
  permSprites: THREE.Sprite[] | null    // 常驻种子精灵引用
}

let scene: THREE.Scene
let camera: THREE.OrthographicCamera
let renderer: THREE.WebGLRenderer
let particles: THREE.Points
let clock: THREE.Clock
let rafId = 0
let wordStates: WordState[] = []
let origPos: Float32Array | null = null
let dandelionStates: DandelionState[] = []
let seedTexture: THREE.CanvasTexture | null = null

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
    window.addEventListener('click', onScreenClick)
  } catch (e) {
    console.error('[TextParticles] 初始化失败:', e)
  }
})

onUnmounted(() => {
  window.removeEventListener('mousemove', onMouseMove)
  window.removeEventListener('mouseleave', onMouseLeave)
  window.removeEventListener('resize', onResize)
  window.removeEventListener('click', onScreenClick)
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

  const wordParticleCount = sampled.reduce((s, d) => s + d.data.positions.length / 3, 0)

  // 预生成蒲公英数据，提前计算总粒子数
  const dandCfgs: { x: number; y: number; scale: number; tint: [number,number,number] | null; hasPermSprites?: boolean }[] = [
    { x: halfW * 0.62, y: -halfH * 0.63, scale: 1.8, tint: [0.80, 0.45, 0.95], hasPermSprites: true },
    { x: halfW * 0.80, y: -halfH * 0.55, scale: 0.8, tint: [0.55, 0.80, 1.00] },
  ]
  const dandelionData = dandCfgs.map(dc => ({ config: dc, data: generateDandelion(dc.scale, dc.tint) }))
  const dandelionParticleCount = dandelionData.reduce((s, d) => s + d.data.positions.length / 3, 0)

  const totalParticles = wordParticleCount + dandelionParticleCount
  console.log(`[TextParticles] 总粒子数: ${totalParticles}`)

  const allPos = new Float32Array(totalParticles * 3)
  const allCol = new Float32Array(totalParticles * 3)
  let offset = 0
  wordStates = []
  dandelionStates = []

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

  // 蒲公英
  for (const dd of dandelionData) {
    const dc = dd.config
    const count = dd.data.positions.length / 3

    dandelionStates.push({
      baseX: dc.x, baseY: dc.y, baseZ: 0,
      scale: dc.scale,
      particleCount: count,
      startIndex: offset / 3,
      swaySpeed: 0.6 + Math.random() * 0.3,
      swayAmount: 0.25 + Math.random() * 0.1,
      swayPhase: Math.random() * Math.PI * 2,
      exploding: false,
      explodeTime: 0,
      explosionVels: null,
      seedSprites: null,
      seedSpriteVels: null,
      tint: dc.tint ?? null,
      spriteRelPos: null,
      permSprites: null,
    })

    for (let j = 0; j < count; j++) {
      const pi = offset + j * 3
      allPos[pi]     = dd.data.positions[j * 3]
      allPos[pi + 1] = dd.data.positions[j * 3 + 1]
      allPos[pi + 2] = dd.data.positions[j * 3 + 2]
      allCol[pi]     = dd.data.colors[j * 3]
      allCol[pi + 1] = dd.data.colors[j * 3 + 1]
      allCol[pi + 2] = dd.data.colors[j * 3 + 2]
    }

    offset += count * 3

    // 常驻种子精灵（大蒲公英球体由种子组成）
    const ds = dandelionStates[dandelionStates.length - 1]
    if (dc.hasPermSprites) {
      if (!seedTexture) seedTexture = createSeedTexture()
      const stemCount = 13   // stemSegs(12) + 1
      const coreCount = Math.floor(12 * dc.scale)
      const seedStart = stemCount + coreCount
      const seedParticleCount = count - seedStart
      const seeds = Math.floor(seedParticleCount / 8)
      const headRadius = 0.28 * dc.scale
      const sprites: THREE.Sprite[] = []
      const relPos: number[] = []
      for (let s = 0; s < seeds; s++) {
        let sx = 0, sy = 0, sz = 0
        const baseIdx = seedStart + s * 8
        for (let k = 0; k < 8; k++) {
          const pi = (baseIdx + k) * 3
          sx += dd.data.positions[pi]
          sy += dd.data.positions[pi + 1]
          sz += dd.data.positions[pi + 2]
        }
        sx /= 8; sy /= 8; sz /= 8
        // 只保留球体外围（过滤掉球体内部的种子）
        if (Math.sqrt(sx * sx + sy * sy + sz * sz) < headRadius * 0.75) continue

        relPos.push(sx, sy, sz)
        const mat = new THREE.SpriteMaterial({
          map: seedTexture,
          blending: THREE.AdditiveBlending,
          depthWrite: false,
          transparent: true,
          opacity: 0.85,
          rotation: 0,
          color: new THREE.Color().setHSL(Math.random(), 0.8, 0.55 + Math.random() * 0.25),
        })
        const sp = new THREE.Sprite(mat)
        sp.position.set(sx + dc.x, sy + dc.y, sz)
        const spriteScale = 0.20 * dc.scale
        sp.scale.set(spriteScale, spriteScale * 1.5, 1)
        scene.add(sp)
        sprites.push(sp)
      }
      ds.permSprites = sprites
      ds.seedSprites = sprites
      ds.spriteRelPos = new Float32Array(relPos)
    }
  }

  // 保存相对坐标
  origPos = new Float32Array(allPos)

  // 转为世界坐标 - 单词
  for (const ws of wordStates) {
    for (let j = 0; j < ws.particleCount; j++) {
      const pi = (ws.startIndex + j) * 3
      allPos[pi]     += ws.x
      allPos[pi + 1] += ws.y
      allPos[pi + 2] += ws.z
    }
  }

  // 转为世界坐标 - 蒲公英
  for (const ds of dandelionStates) {
    for (let j = 0; j < ds.particleCount; j++) {
      const pi = (ds.startIndex + j) * 3
      allPos[pi]     += ds.baseX
      allPos[pi + 1] += ds.baseY
      allPos[pi + 2] += ds.baseZ
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

// ---- 蒲公英粒子生成 ----
function generateDandelion(scale: number, tint?: [number, number, number] | null): { positions: Float32Array; colors: Float32Array } {
  const positions: number[] = []
  const colors: number[] = []

  function tintColor(r: number, g: number, b: number): [number, number, number] {
    if (!tint) return [r, g, b]
    return [r * tint[0], g * tint[1], b * tint[2]]
  }

  // 茎 — 微微弯曲
  const stemLen = 0.9 * scale
  const stemSegs = 12
  for (let i = 0; i <= stemSegs; i++) {
    const t = i / stemSegs
    const curve = Math.sin(t * Math.PI * 0.3) * 0.06 * scale
    positions.push(
      curve + (Math.random() - 0.5) * 0.03 * scale,
      -t * stemLen,
      (Math.random() - 0.5) * 0.03 * scale,
    )
    const bright = 0.35 + t * 0.55
    const [cr, cg, cb] = tintColor(bright, bright, bright + 0.04)
    colors.push(cr, cg, cb)
  }

  // 花托（中心小核）
  const coreCount = Math.floor(12 * scale)
  for (let i = 0; i < coreCount; i++) {
    const r = Math.random() * 0.04 * scale
    const theta = Math.random() * Math.PI * 2
    const phi = Math.acos(2 * Math.random() - 1)
    positions.push(
      r * Math.sin(phi) * Math.cos(theta),
      r * Math.cos(phi),
      r * Math.sin(phi) * Math.sin(theta),
    )
    const [cr, cg, cb] = tintColor(0.6, 0.6, 0.65)
    colors.push(cr, cg, cb)
  }

  // 种子（seed）—— 辐射状，每颗种子 = 柄(2颗) + 绒毛(2颗)
  const seedCount = Math.floor(130 * scale * scale)
  const headRadius = 0.28 * scale

  for (let i = 0; i < seedCount; i++) {
    const theta = Math.random() * Math.PI * 2
    const phi = Math.acos(2 * Math.random() - 1)

    // 种柄：2 颗粒子连成一条短线
    for (let j = 0; j < 2; j++) {
      const t = 0.25 + j * 0.3
      const r = headRadius * t
      positions.push(
        r * Math.sin(phi) * Math.cos(theta),
        r * Math.cos(phi),
        r * Math.sin(phi) * Math.sin(theta),
      )
      const b = 0.45 + j * 0.3
      const [cr, cg, cb] = tintColor(b, b, b + 0.05)
      colors.push(cr, cg, cb)
    }

    // 顶端绒毛：2 颗亮白粒子，略微散开
    for (let j = 0; j < 2; j++) {
      const r = headRadius * (0.85 + Math.random() * 0.15)
      const spread = 0.015 * scale
      positions.push(
        r * Math.sin(phi) * Math.cos(theta) + (Math.random() - 0.5) * spread,
        r * Math.cos(phi) + (Math.random() - 0.5) * spread,
        r * Math.sin(phi) * Math.sin(theta) + (Math.random() - 0.5) * spread,
      )
      const [cr, cg, cb] = tintColor(1.0, 1.0, 1.0)
      colors.push(cr, cg, cb)
    }
  }

  return { positions: new Float32Array(positions), colors: new Float32Array(colors) }
}

// ---- 蒲公英种子纹理 ----
function createSeedTexture(): THREE.CanvasTexture {
  const canvas = document.createElement('canvas')
  canvas.width = 80
  canvas.height = 120
  const ctx = canvas.getContext('2d')!

  // 透明底
  ctx.clearRect(0, 0, 80, 120)

  const cx = 40
  // ---- 伞状绒毛 ----
  // 辐射细线
  for (let i = 0; i < 16; i++) {
    const angle = (i / 16) * Math.PI * 2 - Math.PI / 2
    const len = 20 + (i % 3) * 2
    const ex = cx + Math.cos(angle) * len
    const ey = 42 + Math.sin(angle) * len

    ctx.strokeStyle = 'rgba(255,255,255,0.85)'
    ctx.lineWidth = 1.2
    ctx.beginPath()
    ctx.moveTo(cx, 42)
    ctx.lineTo(ex, ey)
    ctx.stroke()

    // 末端绒毛小球
    ctx.fillStyle = 'rgba(255,255,255,0.9)'
    ctx.beginPath()
    ctx.arc(ex, ey, 1.8, 0, Math.PI * 2)
    ctx.fill()
  }

  // 伞面横线（蛛网状）
  for (let r = 6; r <= 18; r += 6) {
    ctx.strokeStyle = 'rgba(255,255,255,0.25)'
    ctx.lineWidth = 0.6
    ctx.beginPath()
    ctx.arc(cx, 42, r, 0, Math.PI * 2)
    ctx.stroke()
  }

  // ---- 种子体 ----
  ctx.fillStyle = 'rgba(255,255,255,0.95)'
  ctx.beginPath()
  ctx.ellipse(cx, 62, 3.5, 6, 0, 0, Math.PI * 2)
  ctx.fill()

  // ---- 种柄 ----
  ctx.strokeStyle = 'rgba(255,255,255,0.7)'
  ctx.lineWidth = 1
  ctx.beginPath()
  ctx.moveTo(cx, 68)
  ctx.lineTo(cx + 2, 100)
  ctx.lineTo(cx + 1, 115)
  ctx.stroke()

  const texture = new THREE.CanvasTexture(canvas)
  texture.needsUpdate = true
  return texture
}

// ---- 点击爆炸 ----
function onScreenClick(e: MouseEvent) {
  const el = containerRef.value
  if (!el || !particles || dandelionStates.length === 0) return
  const rect = el.getBoundingClientRect()
  // 转换到世界坐标
  const nx = ((e.clientX - rect.left) / rect.width) * 2 - 1
  const ny = -((e.clientY - rect.top) / rect.height) * 2 + 1
  const wx = nx * halfW
  const wy = ny * halfH

  for (const ds of dandelionStates) {
    if (ds.exploding) continue
    const dx = wx - ds.baseX
    const dy = wy - ds.baseY
    if (Math.sqrt(dx * dx + dy * dy) < 0.5 * ds.scale) {
      triggerExplosion(ds)
      break
    }
  }
}

function triggerExplosion(ds: DandelionState) {
  ds.exploding = true
  ds.explodeTime = clock.getElapsedTime()

  // ---- 基础粒子继续爆炸（提供散落光点） ----
  const vels = new Float32Array(ds.particleCount * 3)
  const pos = (particles!.geometry.attributes.position.array as Float32Array)

  for (let j = 0; j < ds.particleCount; j++) {
    const pi = (ds.startIndex + j) * 3
    const wx = pos[pi], wy = pos[pi + 1], wz = pos[pi + 2]
    const dx = wx - ds.baseX, dy = wy - ds.baseY, dz = wz - ds.baseZ
    const dist = Math.sqrt(dx * dx + dy * dy + dz * dz) || 0.001

    const speed = 2.5 + Math.random() * 4
    const spread = 0.6
    vels[j * 3]     = (dx / dist) * speed + (Math.random() - 0.5) * spread
    vels[j * 3 + 1] = (dy / dist) * speed + Math.random() * 2.5 + 0.5
    vels[j * 3 + 2] = (dz / dist) * speed + (Math.random() - 0.5) * spread
  }
  ds.explosionVels = vels

  // ---- 种子精灵（伞状） ----
  if (!seedTexture) {
    seedTexture = createSeedTexture()
  }

  const seeds = Math.floor(ds.particleCount / 8)
  const sVels = new Float32Array(seeds * 3)
  const hasPerm = !!ds.spriteRelPos

  // 常驻种子 → 临时隐藏
  if (hasPerm && ds.permSprites) {
    for (const sp of ds.permSprites) sp.visible = false
  }

  // 创建爆炸用精灵（新旧都走统一的创建流程）
  const expSprites: THREE.Sprite[] = []
  for (let s = 0; s < seeds; s++) {
    let sx = 0, sy = 0, sz = 0
    for (let k = 0; k < 8; k++) {
      const pi = (ds.startIndex + s * 8 + k) * 3
      sx += pos[pi]; sy += pos[pi + 1]; sz += pos[pi + 2]
    }
    sx /= 8; sy /= 8; sz /= 8

    const dx = sx - ds.baseX, dy = sy - ds.baseY, dz = sz - ds.baseZ
    const dist = Math.sqrt(dx * dx + dy * dy + dz * dz) || 0.001
    const speed = 2.5 + Math.random() * 4
    const spread = 0.6
    sVels[s * 3]     = (dx / dist) * speed + (Math.random() - 0.5) * spread
    sVels[s * 3 + 1] = (dy / dist) * speed + Math.random() * 2.5 + 0.5
    sVels[s * 3 + 2] = (dz / dist) * speed + (Math.random() - 0.5) * spread

    const mat = new THREE.SpriteMaterial({
      map: seedTexture,
      blending: THREE.AdditiveBlending,
      depthWrite: false,
      transparent: true,
      opacity: 1,
      rotation: 0,
      color: new THREE.Color().setHSL(Math.random(), 0.8, 0.55 + Math.random() * 0.25),
    })
    const sprite = new THREE.Sprite(mat)
    sprite.position.set(sx, sy, sz)
    const spriteScale = 0.22 * ds.scale * (ds.scale < 1.0 ? 1.2 : 1.0)
    sprite.scale.set(spriteScale, spriteScale * 1.5, 1)
    scene.add(sprite)
    expSprites.push(sprite)
  }
  ds.seedSprites = expSprites
  ds.seedSpriteVels = sVels

  // 隐藏基础粒子（移到镜头外不可见）
  for (let j = 0; j < ds.particleCount; j++) {
    const pj = (ds.startIndex + j) * 3
    pos[pj + 2] = 999
  }
  particles!.geometry.attributes.position.needsUpdate = true
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

    // 蒲公英粒子：随风摇摆 / 爆裂散落
    const ds = dandelionStates.find(d => i >= d.startIndex && i < d.startIndex + d.particleCount)
    if (ds) {
      if (ds.exploding) {
        const elapsed = time - ds.explodeTime
        if (elapsed > 10) {
          // 10秒到 → 复原
          ds.exploding = false
          ds.explodeTime = 0
          ds.explosionVels = null
          ds.seedSpriteVels = null
          // 移除爆炸精灵，恢复常驻种子
          if (ds.seedSprites) {
            for (const sp of ds.seedSprites) {
              scene.remove(sp)
              sp.material.dispose()
            }
            ds.seedSprites = null
          }
          if (ds.permSprites) {
            for (const sp of ds.permSprites) sp.visible = true
            ds.seedSprites = ds.permSprites
          }
          // 恢复基础粒子位置
          for (let j = 0; j < ds.particleCount; j++) {
            const pj = (ds.startIndex + j) * 3
            pos[pj]     = orig[pj]     + ds.baseX
            pos[pj + 1] = orig[pj + 1] + ds.baseY
            pos[pj + 2] = orig[pj + 2] + ds.baseZ
          }
          particles.geometry.attributes.position.needsUpdate = true
        } else {
          const dt = delta
          const drag = Math.pow(0.985, dt * 60)
          const windX = -3                               // 右下→左上：x 向左
          const windY = 1.2                               // 右下→左上：y 向上
          const gust = Math.sin(time * 0.4 + ds.explodeTime) * 0.1

          // ---- 种子精灵（每 8 颗粒子只更新一次） ----
          const idx = i - ds.startIndex
          if (ds.seedSprites && ds.seedSpriteVels && idx % 8 === 0) {
            const si = idx / 8
            if (si < ds.seedSprites.length) {
              const sv = si * 3
              const sVels = ds.seedSpriteVels
              sVels[sv]     = sVels[sv]     * drag + (windX + gust) * dt
              sVels[sv + 1] = sVels[sv + 1] * drag + (windY - 1.2) * dt
              sVels[sv + 2] = sVels[sv + 2] * drag
              const sp = ds.seedSprites[si]
              sp.position.x += sVels[sv]     * dt
              sp.position.y += sVels[sv + 1] * dt
              sp.position.z += sVels[sv + 2] * dt
              // 随风微微旋转
              sp.material.rotation += dt * 0.3 * Math.sin(time * 0.5 + si)
            }
          }
          continue
        }
      }
      // 正常摇摆（也负责复原后的归位）
      const stemLen = 0.9 * ds.scale
      const heightFrac = Math.min(1, Math.max(0, (oy + stemLen) / stemLen))

      const swayX = Math.sin(time * ds.swaySpeed + ds.swayPhase) * ds.swayAmount
      const swayZ = Math.sin(time * ds.swaySpeed * 0.7 + ds.swayPhase * 1.3) * ds.swayAmount * 0.15
      const bob   = Math.sin(time * ds.swaySpeed * 0.5 + ds.swayPhase * 0.7) * 0.03 * ds.scale

      const tx = ox + ds.baseX + swayX * heightFrac
      const ty = oy + ds.baseY + bob * heightFrac
      const tz = oz + ds.baseZ + swayZ * heightFrac

      pos[i3]     += (tx - pos[i3]) * 0.06
      pos[i3 + 1] += (ty - pos[i3 + 1]) * 0.06
      pos[i3 + 2] += (tz - pos[i3 + 2]) * 0.06
      continue
    }

    // 单词粒子
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

  // 常驻种子精灵跟随蒲公英摇摆
  for (const ds of dandelionStates) {
    if (ds.exploding || !ds.seedSprites || !ds.spriteRelPos) continue
    const stemLen = 0.9 * ds.scale
    for (let s = 0; s < ds.seedSprites.length; s++) {
      const sp = ds.seedSprites[s]
      const ri = s * 3
      const ry = ds.spriteRelPos[ri + 1]
      const heightFrac = Math.min(1, Math.max(0, (ry + stemLen) / stemLen))
      const swayX = Math.sin(time * ds.swaySpeed + ds.swayPhase) * ds.swayAmount
      const swayZ = Math.sin(time * ds.swaySpeed * 0.7 + ds.swayPhase * 1.3) * ds.swayAmount * 0.15
      const bob   = Math.sin(time * ds.swaySpeed * 0.5 + ds.swayPhase * 0.7) * 0.03 * ds.scale
      const tx = ds.spriteRelPos[ri]     + ds.baseX + swayX * heightFrac
      const ty = ry                        + ds.baseY + bob   * heightFrac
      const tz = ds.spriteRelPos[ri + 2] + ds.baseZ + swayZ * heightFrac
      sp.position.x += (tx - sp.position.x) * 0.06
      sp.position.y += (ty - sp.position.y) * 0.06
      sp.position.z += (tz - sp.position.z) * 0.06
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
