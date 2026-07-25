import { useEffect, useMemo, useRef, useState } from 'react'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { useContent } from '@/context/ContentContext'
import { getSiteBase } from '@/lib/content'
import { MousePointerClick, RefreshCw, ZoomIn, ZoomOut } from 'lucide-react'

type SelectedField = {
  kind: 'page' | 'setting' | 'block'
  page?: string
  key: string
  label: string
  value: string
  multiline?: boolean
}

const HOME_FIELDS: Array<{ key: string; label: string; multiline?: boolean }> = [
  { key: 'hero_eyebrow', label: 'ข้อความเล็กบน Hero' },
  { key: 'hero_title', label: 'หัวข้อ Hero (รองรับ <br>)', multiline: true },
  { key: 'hero_cta', label: 'ข้อความปุ่ม Hero' },
  { key: 'hero_cta_link', label: 'ลิงก์ปุ่ม Hero' },
  { key: 'intro_title', label: 'หัวข้อ Intro' },
  { key: 'intro_text', label: 'ข้อความ Intro', multiline: true },
  { key: 'rec_section_label', label: 'ป้ายแผนแนะนำ' },
  { key: 'rec_section_title', label: 'หัวข้อแผนแนะนำ' },
  { key: 'consult_title', label: 'หัวข้อฟอร์มปรึกษา' },
]

const ZOOM_PRESETS = [40, 50, 60, 75, 90, 100, 125, 150]

export function HomeVisualPage() {
  const { pages, settings, setPageField, setSetting, save } = useContent()
  const iframeRef = useRef<HTMLIFrameElement>(null)
  const viewportRef = useRef<HTMLDivElement>(null)
  const scaleRef = useRef<HTMLDivElement>(null)
  const sizerRef = useRef<HTMLDivElement>(null)
  const [selected, setSelected] = useState<SelectedField | null>(null)
  const [iframeKey, setIframeKey] = useState(0)
  const [zoom, setZoom] = useState(() => {
    const saved = Number(localStorage.getItem('cms_visual_zoom') || 75)
    return Number.isFinite(saved) ? saved : 75
  })
  const home = pages.home || {}
  const previewUrl = useMemo(() => `${getSiteBase()}/index.html?cms_preview=1&t=${iframeKey}`, [iframeKey])

  useEffect(() => {
    const onMessage = (event: MessageEvent) => {
      const data = event.data
      if (!data || data.source !== 'agent-cms-preview') return
      if (data.type === 'select-field') {
        setSelected({
          kind: data.kind,
          page: data.page,
          key: data.key,
          label: data.label || data.key,
          value: data.value || '',
          multiline: !!data.multiline,
        })
      }
    }
    window.addEventListener('message', onMessage)
    return () => window.removeEventListener('message', onMessage)
  }, [])

  useEffect(() => {
    const viewport = viewportRef.current
    const scaleWrap = scaleRef.current
    const sizer = sizerRef.current
    if (!viewport || !scaleWrap || !sizer) return
    const scale = zoom / 100
    localStorage.setItem('cms_visual_zoom', String(zoom))

    const measure = () => {
      try {
        const doc = iframeRef.current?.contentDocument
        return Math.max(
          doc?.body?.scrollHeight || 0,
          doc?.documentElement?.scrollHeight || 0,
          1200,
        ) + 24
      } catch {
        return 2400
      }
    }

    const apply = () => {
      const vw = viewport.clientWidth || 900
      const pageWidth = Math.max(vw / scale, 1100)
      const pageHeight = measure()
      scaleWrap.style.width = `${pageWidth}px`
      scaleWrap.style.height = `${pageHeight}px`
      scaleWrap.style.transform = `scale(${scale})`
      sizer.style.width = `${Math.ceil(pageWidth * scale)}px`
      sizer.style.height = `${Math.ceil(pageHeight * scale)}px`
      if (iframeRef.current) iframeRef.current.style.height = `${pageHeight}px`
    }
    apply()
    const t1 = window.setTimeout(apply, 400)
    const t2 = window.setTimeout(apply, 1200)
    const ro = new ResizeObserver(apply)
    ro.observe(viewport)
    return () => {
      ro.disconnect()
      window.clearTimeout(t1)
      window.clearTimeout(t2)
    }
  }, [zoom, iframeKey])

  const pushPreviewUpdate = (payload: Record<string, unknown>) => {
    iframeRef.current?.contentWindow?.postMessage({ source: 'agent-cms-parent', ...payload }, '*')
  }

  const applySelected = (value: string) => {
    if (!selected) return
    setSelected({ ...selected, value })
    if (selected.kind === 'page' && selected.page) {
      setPageField(selected.page, selected.key, value)
      pushPreviewUpdate({ type: 'update-page', page: selected.page, key: selected.key, value })
    } else if (selected.kind === 'setting') {
      setSetting(selected.key, value)
      pushPreviewUpdate({ type: 'update-setting', key: selected.key, value })
    }
  }

  const bumpZoom = (delta: number) => setZoom((z) => Math.max(30, Math.min(200, z + delta)))

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-end justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">แก้ไขหน้าแรกแบบเห็นจริง</h1>
          <p className="text-sm text-[var(--color-muted-foreground)]">
            พรีวิวเหมือนหน้าบ้าน — คลิกข้อความที่มีกรอบเพื่อแก้ไข · ใช้ซูมเพื่อดูการจัดวางทั้งหน้า
          </p>
        </div>
        <div className="flex gap-2">
          <Button variant="outline" onClick={() => setIframeKey((k) => k + 1)}>
            <RefreshCw className="h-4 w-4" /> รีเฟรชพรีวิว
          </Button>
          <Button onClick={() => void save(false)}>บันทึก</Button>
        </div>
      </div>

      <div className="grid gap-4 xl:grid-cols-[1fr_340px]">
        <Card className="overflow-hidden">
          <CardHeader className="border-b border-[var(--color-border)] py-3">
            <div className="flex flex-wrap items-center gap-2">
              <Badge variant="secondary">
                <MousePointerClick className="mr-1 h-3 w-3" /> โหมดคลิกเพื่อแก้ไข
              </Badge>
              <CardDescription className="hidden sm:block">ชี้เมาส์ที่ข้อความ — จะมีกรอบสีน้ำเงิน</CardDescription>
              <div className="ml-auto flex items-center gap-1.5">
                <Button type="button" size="icon" variant="outline" className="h-8 w-8" onClick={() => bumpZoom(-10)}>
                  <ZoomOut className="h-4 w-4" />
                </Button>
                <select
                  className="h-8 rounded-md border border-[var(--color-border)] bg-white px-2 text-xs font-semibold"
                  value={zoom}
                  onChange={(e) => setZoom(Number(e.target.value))}
                >
                  {!ZOOM_PRESETS.includes(zoom) && <option value={zoom}>{zoom}%</option>}
                  {ZOOM_PRESETS.map((p) => (
                    <option key={p} value={p}>
                      {p}%
                    </option>
                  ))}
                </select>
                <Button type="button" size="icon" variant="outline" className="h-8 w-8" onClick={() => bumpZoom(10)}>
                  <ZoomIn className="h-4 w-4" />
                </Button>
                <Button type="button" size="sm" variant="outline" className="h-8" onClick={() => setZoom(60)}>
                  พอดี
                </Button>
              </div>
            </div>
          </CardHeader>
          <CardContent className="p-0">
            <div ref={viewportRef} className="min-h-[480px] overflow-visible bg-slate-200">
              <div ref={sizerRef} className="relative">
                <div
                  ref={scaleRef}
                  className="absolute top-0 left-0 origin-top-left bg-white shadow-sm"
                  style={{ transformOrigin: 'top left' }}
                >
                  <iframe
                    key={iframeKey}
                    ref={iframeRef}
                    title="Home preview"
                    src={previewUrl}
                    className="h-full w-full border-0 bg-white"
                  />
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <div className="space-y-4 xl:sticky xl:top-6 xl:max-h-[calc(100vh-2rem)] xl:self-start xl:overflow-y-auto">
          <Card>
            <CardHeader>
              <CardTitle>แผงแก้ไข</CardTitle>
              <CardDescription>
                {selected ? selected.label : 'คลิกข้อความบนพรีวิว หรือเลือกจากรายการด้านล่าง'}
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-3">
              {selected ? (
                <>
                  <div className="space-y-1.5">
                    <Label>{selected.label}</Label>
                    {selected.multiline ? (
                      <Textarea
                        rows={6}
                        value={selected.value}
                        onChange={(e) => applySelected(e.target.value)}
                      />
                    ) : (
                      <Input value={selected.value} onChange={(e) => applySelected(e.target.value)} />
                    )}
                  </div>
                  <p className="text-xs text-[var(--color-muted-foreground)]">
                    คีย์: <code>{selected.key}</code>
                  </p>
                </>
              ) : (
                <p className="text-sm text-slate-500">ยังไม่ได้เลือกฟิลด์</p>
              )}
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>ฟิลด์หน้าแรกทั้งหมด</CardTitle>
            </CardHeader>
            <CardContent className="space-y-2">
              {HOME_FIELDS.map((field) => (
                <button
                  key={field.key}
                  type="button"
                  className="flex w-full items-start justify-between rounded-lg border border-[var(--color-border)] px-3 py-2 text-left text-sm hover:bg-slate-50"
                  onClick={() =>
                    setSelected({
                      kind: 'page',
                      page: 'home',
                      key: field.key,
                      label: field.label,
                      value: home[field.key] || '',
                      multiline: field.multiline,
                    })
                  }
                >
                  <span className="font-medium">{field.label}</span>
                  <span className="max-w-[45%] truncate text-xs text-slate-400">{home[field.key] || '—'}</span>
                </button>
              ))}
              <button
                type="button"
                className="flex w-full items-start justify-between rounded-lg border border-[var(--color-border)] px-3 py-2 text-left text-sm hover:bg-slate-50"
                onClick={() =>
                  setSelected({
                    kind: 'setting',
                    key: 'site_tagline',
                    label: 'คำโปรยเว็บไซต์',
                    value: settings.site_tagline || '',
                  })
                }
              >
                <span className="font-medium">คำโปรยเว็บไซต์</span>
                <span className="max-w-[45%] truncate text-xs text-slate-400">{settings.site_tagline || '—'}</span>
              </button>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  )
}
