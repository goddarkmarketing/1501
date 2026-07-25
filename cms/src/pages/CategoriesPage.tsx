import { useState } from 'react'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { useContent } from '@/context/ContentContext'
import type { CategoryMeta } from '@/lib/content'
import { Plus, Trash2 } from 'lucide-react'
import { toast } from 'sonner'

function slugify(value: string) {
  return value
    .trim()
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
}

export function CategoriesPage() {
  const { categories, setCategory, removeCategory, save } = useContent()
  const list = Object.entries(categories)
  const [newId, setNewId] = useState('')
  const [newLabel, setNewLabel] = useState('')

  const update = (id: string, patch: Partial<CategoryMeta>) => {
    setCategory(id, { ...categories[id], ...patch, id })
  }

  const addCategory = () => {
    const id = slugify(newId || newLabel)
    if (!id) {
      toast.error('กรุณากรอก ID หรือชื่อหมวด (ภาษาอังกฤษสำหรับ ID)')
      return
    }
    if (!/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(id)) {
      toast.error('ID ใช้ได้เฉพาะ a-z, 0-9 และขีดกลาง')
      return
    }
    if (categories[id]) {
      toast.error('มีหมวดนี้อยู่แล้ว')
      return
    }
    const label = newLabel.trim() || id
    setCategory(id, {
      id,
      label,
      headline: '',
      introTitle: '',
      introText: '',
      promoSection: `โปรโมชัน${label}`,
      whySection: `ทำไมต้องมี${label}?`,
      heroImage: '',
      icon: '',
      features: [],
    })
    setNewId('')
    setNewLabel('')
    toast.success(`เพิ่มหมวด «${label}» แล้ว — กดบันทึกเพื่อเก็บข้อมูล`)
  }

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-end justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">หมวดหมู่แผนประกัน</h1>
          <p className="text-sm text-[var(--color-muted-foreground)]">เพิ่ม / แก้ไขชื่อ หัวข้อ ข้อความแนะนำ และภาพของแต่ละหมวด</p>
        </div>
        <Button onClick={() => void save(false)}>บันทึก</Button>
      </div>

      <Card className="border-[var(--color-primary)]/20 bg-[var(--color-secondary)]/40">
        <CardHeader>
          <CardTitle className="text-base">เพิ่มหมวดหมู่ใหม่</CardTitle>
          <CardDescription>ID ใช้ใน URL เช่น plan-category.html?category=dental-care</CardDescription>
        </CardHeader>
        <CardContent className="flex flex-wrap items-end gap-3">
          <div className="space-y-1.5 min-w-[160px] flex-1">
            <Label>ID (ภาษาอังกฤษ)</Label>
            <Input
              placeholder="dental-care"
              value={newId}
              onChange={(e) => setNewId(e.target.value)}
            />
          </div>
          <div className="space-y-1.5 min-w-[200px] flex-1">
            <Label>ชื่อหมวดหมู่</Label>
            <Input
              placeholder="ประกันทันตกรรม"
              value={newLabel}
              onChange={(e) => setNewLabel(e.target.value)}
            />
          </div>
          <Button type="button" onClick={addCategory}>
            <Plus className="h-4 w-4" /> เพิ่มหมวด
          </Button>
        </CardContent>
      </Card>

      <div className="space-y-4">
        {list.map(([id, cat]) => (
          <Card key={id}>
            <CardHeader className="flex flex-row items-start justify-between gap-3 space-y-0">
              <div>
                <CardTitle>{cat.label || id}</CardTitle>
                <CardDescription>ID: {id}</CardDescription>
              </div>
              <Button
                size="sm"
                variant="ghost"
                onClick={() => {
                  if (confirm(`ลบหมวด «${cat.label || id}»?`)) removeCategory(id)
                }}
              >
                <Trash2 className="h-4 w-4 text-red-500" />
              </Button>
            </CardHeader>
            <CardContent className="grid gap-4 md:grid-cols-2">
              <div className="space-y-1.5">
                <Label>ชื่อหมวด</Label>
                <Input value={cat.label || ''} onChange={(e) => update(id, { label: e.target.value })} />
              </div>
              <div className="space-y-1.5">
                <Label>ไอคอน</Label>
                <Input value={String(cat.icon || '')} onChange={(e) => update(id, { icon: e.target.value })} />
              </div>
              <div className="space-y-1.5 md:col-span-2">
                <Label>Headline</Label>
                <Input value={cat.headline || ''} onChange={(e) => update(id, { headline: e.target.value })} />
              </div>
              <div className="space-y-1.5">
                <Label>หัวข้อ Intro</Label>
                <Input value={cat.introTitle || ''} onChange={(e) => update(id, { introTitle: e.target.value })} />
              </div>
              <div className="space-y-1.5">
                <Label>รูป Hero</Label>
                <Input value={cat.heroImage || ''} onChange={(e) => update(id, { heroImage: e.target.value })} />
              </div>
              <div className="space-y-1.5 md:col-span-2">
                <Label>ข้อความ Intro</Label>
                <Textarea rows={4} value={cat.introText || ''} onChange={(e) => update(id, { introText: e.target.value })} />
              </div>
              <div className="space-y-1.5">
                <Label>หัวข้อโปรโมชัน</Label>
                <Input value={cat.promoSection || ''} onChange={(e) => update(id, { promoSection: e.target.value })} />
              </div>
              <div className="space-y-1.5">
                <Label>หัวข้อทำไมต้องมี</Label>
                <Input value={cat.whySection || ''} onChange={(e) => update(id, { whySection: e.target.value })} />
              </div>

              <div className="md:col-span-2 space-y-2">
                <div className="flex items-center justify-between">
                  <Label>ฟีเจอร์หมวด</Label>
                  <Button
                    size="sm"
                    variant="outline"
                    onClick={() =>
                      update(id, {
                        features: [...((cat.features as Array<{ title: string; desc: string }>) || []), { title: '', desc: '' }],
                      })
                    }
                  >
                    <Plus className="h-4 w-4" /> เพิ่ม
                  </Button>
                </div>
                {((cat.features as Array<{ title: string; desc: string }>) || []).map((f, index) => (
                  <div key={index} className="grid gap-2 md:grid-cols-2">
                    <Input
                      placeholder="หัวข้อ"
                      value={f.title}
                      onChange={(e) => {
                        const features = [...((cat.features as typeof f[]) || [])]
                        features[index] = { ...f, title: e.target.value }
                        update(id, { features })
                      }}
                    />
                    <Input
                      placeholder="รายละเอียด"
                      value={f.desc}
                      onChange={(e) => {
                        const features = [...((cat.features as typeof f[]) || [])]
                        features[index] = { ...f, desc: e.target.value }
                        update(id, { features })
                      }}
                    />
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        ))}
        {list.length === 0 && <p className="text-sm text-slate-500">ยังไม่มีหมวดหมู่ — เพิ่มด้านบนได้เลย</p>}
      </div>
    </div>
  )
}
