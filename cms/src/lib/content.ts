/** Resolve site root paths whether CMS runs on Vite, XAMPP, or GitHub Pages */
export function getSiteBase(): string {
  const { pathname, origin } = window.location
  // /1501/cms/ or /1501/cms/dist/
  if (pathname.includes('/1501/')) {
    return `${origin}/1501`
  }
  // GitHub Pages: /1501/cms/
  if (pathname.includes('/cms')) {
    const root = pathname.split('/cms')[0] || ''
    return `${origin}${root}`
  }
  // Vite dev — site is on XAMPP
  return 'http://localhost/1501'
}

export function getAdminApiBase(): string {
  return `${getSiteBase()}/admin/api`
}

export type SiteBundle = {
  pages: Record<string, Record<string, string>>
  settings: Record<string, string>
  blocks: Record<string, unknown>
  faqs: Array<{ q: string; a: string }>
  plans: Record<string, PlanProduct>
  categories: Record<string, CategoryMeta>
  mode: 'api' | 'static'
}

export type PlanProduct = {
  id: string
  name: string
  category: string
  tagline?: string
  headline?: string
  priceFrom?: number
  priceNote?: string
  heroImage?: string
  benefits?: string[]
  highlights?: string[]
  conditions?: string[]
  renewal?: string[]
  why?: string[]
  promo?: { text?: string; code?: string; until?: string; badge?: string }
  faqs?: Array<{ q: string; a: string }>
  tiers?: Array<{ id: string; label: string; amount: string; unit?: string; popular?: boolean }>
  [key: string]: unknown
}

export type CategoryMeta = {
  id?: string
  label?: string
  headline?: string
  heroImage?: string
  introTitle?: string
  introText?: string
  promoSection?: string
  whySection?: string
  icon?: string
  features?: unknown[]
  [key: string]: unknown
}

function extractJsAssign(source: string, name: string): unknown {
  const re = new RegExp(`(?:const|var|let)\\s+${name}\\s*=\\s*`)
  const m = source.match(re)
  if (!m || m.index === undefined) return null
  let i = m.index + m[0].length
  while (i < source.length && /\s/.test(source[i])) i++
  const start = i
  const opener = source[i]
  if (opener !== '{' && opener !== '[') {
    // string or primitive — take until semicolon
    const end = source.indexOf(';', i)
    return JSON.parse(source.slice(i, end).replace(/'/g, '"'))
  }
  const closer = opener === '{' ? '}' : ']'
  let depth = 0
  let inStr: string | null = null
  let escaped = false
  for (; i < source.length; i++) {
    const ch = source[i]
    if (inStr) {
      if (escaped) {
        escaped = false
        continue
      }
      if (ch === '\\') {
        escaped = true
        continue
      }
      if (ch === inStr) inStr = null
      continue
    }
    if (ch === '"' || ch === "'" || ch === '`') {
      inStr = ch
      continue
    }
    if (ch === opener) depth++
    if (ch === closer) {
      depth--
      if (depth === 0) {
        const raw = source.slice(start, i + 1)
        // Supports createPlanProduct({...}) wrappers in plan-data.js
        // eslint-disable-next-line no-new-func
        return new Function('createPlanProduct', `return (${raw})`)((obj: unknown) => obj)
      }
    }
  }
  throw new Error(`Could not parse ${name}`)
}

async function loadFromStatic(): Promise<SiteBundle> {
  const base = getSiteBase()
  const [siteRes, planRes] = await Promise.all([
    fetch(`${base}/assets/js/site-content.js?t=${Date.now()}`),
    fetch(`${base}/assets/js/plan-data.js?t=${Date.now()}`),
  ])
  if (!siteRes.ok) throw new Error('โหลด site-content.js ไม่สำเร็จ')
  const siteJs = await siteRes.text()
  const planJs = planRes.ok ? await planRes.text() : ''

  const pages = (extractJsAssign(siteJs, 'SITE_PAGES') || {}) as Record<string, Record<string, string>>
  const settings = (extractJsAssign(siteJs, 'SITE_SETTINGS') || {}) as Record<string, string>
  const blocks = (extractJsAssign(siteJs, 'SITE_BLOCKS') || {}) as Record<string, unknown>
  const faqs = (extractJsAssign(siteJs, 'SITE_FAQ_ITEMS') || []) as Array<{ q: string; a: string }>

  let plans: Record<string, PlanProduct> = {}
  let categories: Record<string, CategoryMeta> = {}
  if (planJs) {
    plans = (extractJsAssign(planJs, 'PLAN_PRODUCTS') || {}) as Record<string, PlanProduct>
    const labels = (extractJsAssign(planJs, 'PLAN_CATEGORY_LABELS') || {}) as Record<string, string>
    const meta = (extractJsAssign(planJs, 'PLAN_CATEGORY_META') || {}) as Record<string, CategoryMeta>
    categories = Object.fromEntries(
      Object.keys(labels).map((id) => [id, { id, label: labels[id], ...(meta[id] || {}) }]),
    )
  }

  return { pages, settings, blocks, faqs, plans, categories, mode: 'static' }
}

async function loadFromApi(): Promise<SiteBundle | null> {
  try {
    const res = await fetch(`${getAdminApiBase()}/cms-bundle.php`, { credentials: 'include' })
    if (!res.ok) return null
    const data = await res.json()
    if (!data.ok) return null
    return { ...data.bundle, mode: 'api' }
  } catch {
    return null
  }
}

export async function loadSiteBundle(): Promise<SiteBundle> {
  const api = await loadFromApi()
  if (api) return api
  return loadFromStatic()
}

export async function saveSiteBundle(
  bundle: Omit<SiteBundle, 'mode'>,
  options: { publish?: boolean } = {},
): Promise<{ ok: boolean; message: string; mode: string }> {
  try {
    const res = await fetch(`${getAdminApiBase()}/cms-bundle.php`, {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ...bundle, publish: !!options.publish }),
    })
    if (res.ok) {
      const data = await res.json()
      if (data.ok) return { ok: true, message: data.message || 'บันทึกสำเร็จ', mode: 'api' }
    }
  } catch {
    /* fall through */
  }

  // Static / GitHub Pages fallback — persist draft + download files
  localStorage.setItem('cms_draft_bundle', JSON.stringify(bundle))
  downloadText('site-content.draft.json', JSON.stringify(bundle, null, 2))
  return {
    ok: true,
    message: 'โหมด Git/Static: บันทึกร่างในเครื่องและดาวน์โหลด JSON แล้ว (ใช้ปุ่มเผยแพร่บนเซิร์ฟเวอร์ PHP เพื่ออัปเดตเว็บจริง)',
    mode: 'static',
  }
}

export async function publishSite(): Promise<{ ok: boolean; message: string }> {
  try {
    const res = await fetch(`${getAdminApiBase()}/publish.php`, { credentials: 'include' })
    if (!res.ok) throw new Error('publish failed')
    const text = await res.text()
    return { ok: true, message: text.slice(0, 200) || 'เผยแพร่แล้ว' }
  } catch {
    return { ok: false, message: 'เผยแพร่ได้เฉพาะเมื่อรันบนเซิร์ฟเวอร์ PHP (XAMPP/VPS)' }
  }
}

function downloadText(filename: string, content: string) {
  const blob = new Blob([content], { type: 'application/json;charset=utf-8' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = filename
  a.click()
  URL.revokeObjectURL(url)
}
