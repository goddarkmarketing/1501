import { useMemo, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { useContent } from '@/context/ContentContext'
import type { PlanProduct } from '@/lib/content'
import { Plus, Trash2 } from 'lucide-react'

function listToText(items?: string[]) {
  return (items || []).join('\n')
}

function textToList(text: string) {
  return text
    .split(/\r?\n/)
    .map((s) => s.trim())
    .filter(Boolean)
}

export function PlanEditPage() {
  const { id = '' } = useParams()
  const { plans, categories, setPlan, save } = useContent()
  const plan = plans[id]
  const [draft, setDraft] = useState<PlanProduct | null>(null)
  const current = draft && draft.id === id ? draft : plan

  const catOptions = useMemo(() => Object.entries(categories).map(([cid, c]) => ({ id: cid, label: c.label || cid })), [categories])

  if (!plan || !current) {
    return (
      <div className="space-y-3">
        <p className="text-slate-500">ไม่พบแผน {id}</p>
        <Button asChild variant="outline">
          <Link to="/plans">กลับรายการแผน</Link>
        </Button>
      </div>
    )
  }

  const update = (patch: Partial<PlanProduct>) => {
    const next = { ...current, ...patch, id: current.id }
    setDraft(next)
    setPlan(current.id, next)
  }

  const updateList = (key: 'benefits' | 'highlights' | 'conditions' | 'renewal' | 'why', text: string) => {
    update({ [key]: textToList(text) })
  }

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-end justify-between gap-3">
        <div>
          <p className="text-sm text-slate-500">
            <Link to="/plans" className="hover:text-[var(--color-primary)]">
              ← แผนประกัน
            </Link>
          </p>
          <h1 className="text-2xl font-bold tracking-tight">{current.name}</h1>
          <p className="text-sm text-[var(--color-muted-foreground)]">แก้ไขรายละเอียดครบทุกส่วน</p>
        </div>
        <Button onClick={() => void save(false)}>บันทึกแผนนี้</Button>
      </div>

      <Tabs defaultValue="basic">
        <TabsList className="flex h-auto flex-wrap justify-start gap-1">
          <TabsTrigger value="basic">ข้อมูลหลัก</TabsTrigger>
          <TabsTrigger value="lists">จุดเด่น / เงื่อนไข</TabsTrigger>
          <TabsTrigger value="promo">โปรโมชัน</TabsTrigger>
          <TabsTrigger value="tiers">แผนย่อย (Tiers)</TabsTrigger>
          <TabsTrigger value="coverage">ความคุ้มครอง</TabsTrigger>
          <TabsTrigger value="faqs">FAQ ของแผน</TabsTrigger>
        </TabsList>

        <TabsContent value="basic">
          <Card>
            <CardHeader>
              <CardTitle>ข้อมูลหลัก</CardTitle>
              <CardDescription>ชื่อ หมวด ราคา และภาพฮีโร่</CardDescription>
            </CardHeader>
            <CardContent className="grid gap-4 md:grid-cols-2">
              <div className="space-y-1.5">
                <Label>ชื่อแผน</Label>
                <Input value={current.name} onChange={(e) => update({ name: e.target.value })} />
              </div>
              <div className="space-y-1.5">
                <Label>หมวดหมู่</Label>
                <select
                  className="flex h-10 w-full rounded-md border border-[var(--color-border)] bg-white px-3 text-sm"
                  value={current.category}
                  onChange={(e) => update({ category: e.target.value })}
                >
                  {catOptions.map((c) => (
                    <option key={c.id} value={c.id}>
                      {c.label}
                    </option>
                  ))}
                </select>
              </div>
              <div className="space-y-1.5">
                <Label>Tagline</Label>
                <Input value={current.tagline || ''} onChange={(e) => update({ tagline: e.target.value })} />
              </div>
              <div className="space-y-1.5">
                <Label>ราคาเริ่มต้น (บาท)</Label>
                <Input
                  type="number"
                  value={current.priceFrom ?? 0}
                  onChange={(e) => update({ priceFrom: Number(e.target.value) })}
                />
              </div>
              <div className="space-y-1.5 md:col-span-2">
                <Label>Headline</Label>
                <Textarea rows={3} value={current.headline || ''} onChange={(e) => update({ headline: e.target.value })} />
              </div>
              <div className="space-y-1.5 md:col-span-2">
                <Label>หมายเหตุราคา</Label>
                <Input value={current.priceNote || ''} onChange={(e) => update({ priceNote: e.target.value })} />
              </div>
              <div className="space-y-1.5 md:col-span-2">
                <Label>รูป Hero (path)</Label>
                <Input value={current.heroImage || ''} onChange={(e) => update({ heroImage: e.target.value })} />
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="lists">
          <div className="grid gap-4 lg:grid-cols-2">
            {(
              [
                ['benefits', 'สิทธิประโยชน์ (บรรทัดละ 1 ข้อ)'],
                ['highlights', 'จุดเด่น'],
                ['conditions', 'เงื่อนไข'],
                ['renewal', 'การต่ออายุ'],
                ['why', 'ทำไมต้องมีแผนนี้'],
              ] as const
            ).map(([key, label]) => (
              <Card key={key}>
                <CardHeader>
                  <CardTitle className="text-base">{label}</CardTitle>
                </CardHeader>
                <CardContent>
                  <Textarea
                    rows={8}
                    value={listToText(current[key] as string[] | undefined)}
                    onChange={(e) => updateList(key, e.target.value)}
                  />
                </CardContent>
              </Card>
            ))}
          </div>
        </TabsContent>

        <TabsContent value="promo">
          <Card>
            <CardHeader>
              <CardTitle>โปรโมชันของแผน</CardTitle>
            </CardHeader>
            <CardContent className="grid gap-4 md:grid-cols-2">
              <div className="space-y-1.5 md:col-span-2">
                <Label>ข้อความโปรโมชัน</Label>
                <Textarea
                  rows={3}
                  value={current.promo?.text || ''}
                  onChange={(e) => update({ promo: { ...current.promo, text: e.target.value } })}
                />
              </div>
              <div className="space-y-1.5">
                <Label>รหัสโปรโมชัน</Label>
                <Input
                  value={current.promo?.code || ''}
                  onChange={(e) => update({ promo: { ...current.promo, code: e.target.value } })}
                />
              </div>
              <div className="space-y-1.5">
                <Label>ใช้ได้ถึง</Label>
                <Input
                  value={current.promo?.until || ''}
                  onChange={(e) => update({ promo: { ...current.promo, until: e.target.value } })}
                />
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="tiers">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between">
              <div>
                <CardTitle>แผนย่อย / วงเงิน</CardTitle>
                <CardDescription>Basic / Standard / Advance</CardDescription>
              </div>
              <Button
                size="sm"
                variant="outline"
                onClick={() =>
                  update({
                    tiers: [
                      ...(current.tiers || []),
                      { id: `tier-${Date.now()}`, label: 'New', amount: '', unit: 'บาท/ปี', popular: false },
                    ],
                  })
                }
              >
                <Plus className="h-4 w-4" /> เพิ่ม
              </Button>
            </CardHeader>
            <CardContent className="space-y-3">
              {(current.tiers || []).map((tier, index) => (
                <div key={tier.id + index} className="grid gap-2 rounded-lg border border-[var(--color-border)] p-3 md:grid-cols-5">
                  <Input
                    placeholder="id"
                    value={tier.id}
                    onChange={(e) => {
                      const tiers = [...(current.tiers || [])]
                      tiers[index] = { ...tier, id: e.target.value }
                      update({ tiers })
                    }}
                  />
                  <Input
                    placeholder="label"
                    value={tier.label}
                    onChange={(e) => {
                      const tiers = [...(current.tiers || [])]
                      tiers[index] = { ...tier, label: e.target.value }
                      update({ tiers })
                    }}
                  />
                  <Input
                    placeholder="amount"
                    value={tier.amount}
                    onChange={(e) => {
                      const tiers = [...(current.tiers || [])]
                      tiers[index] = { ...tier, amount: e.target.value }
                      update({ tiers })
                    }}
                  />
                  <Input
                    placeholder="unit"
                    value={tier.unit || ''}
                    onChange={(e) => {
                      const tiers = [...(current.tiers || [])]
                      tiers[index] = { ...tier, unit: e.target.value }
                      update({ tiers })
                    }}
                  />
                  <div className="flex items-center gap-2">
                    <label className="flex items-center gap-1.5 text-xs">
                      <input
                        type="checkbox"
                        checked={!!tier.popular}
                        onChange={(e) => {
                          const tiers = [...(current.tiers || [])]
                          tiers[index] = { ...tier, popular: e.target.checked }
                          update({ tiers })
                        }}
                      />
                      ยอดนิยม
                    </label>
                    <Button
                      size="icon"
                      variant="ghost"
                      onClick={() => update({ tiers: (current.tiers || []).filter((_, i) => i !== index) })}
                    >
                      <Trash2 className="h-4 w-4 text-red-500" />
                    </Button>
                  </div>
                </div>
              ))}
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="coverage">
          <div className="space-y-4">
            <Card>
              <CardHeader className="flex flex-row items-center justify-between">
                <CardTitle>สรุปความคุ้มครอง</CardTitle>
                <Button
                  size="sm"
                  variant="outline"
                  onClick={() =>
                    update({
                      coverageSummary: [...((current.coverageSummary as Array<{ label: string; value: string; unit?: string }>) || []), { label: '', value: '', unit: '' }],
                    })
                  }
                >
                  <Plus className="h-4 w-4" /> เพิ่ม
                </Button>
              </CardHeader>
              <CardContent className="space-y-2">
                {((current.coverageSummary as Array<{ label: string; value: string; unit?: string }>) || []).map((row, index) => (
                  <div key={index} className="grid gap-2 md:grid-cols-4">
                    <Input
                      placeholder="label"
                      value={row.label}
                      onChange={(e) => {
                        const coverageSummary = [...((current.coverageSummary as typeof row[]) || [])]
                        coverageSummary[index] = { ...row, label: e.target.value }
                        update({ coverageSummary })
                      }}
                    />
                    <Input
                      placeholder="value"
                      value={row.value}
                      onChange={(e) => {
                        const coverageSummary = [...((current.coverageSummary as typeof row[]) || [])]
                        coverageSummary[index] = { ...row, value: e.target.value }
                        update({ coverageSummary })
                      }}
                    />
                    <Input
                      placeholder="unit"
                      value={row.unit || ''}
                      onChange={(e) => {
                        const coverageSummary = [...((current.coverageSummary as typeof row[]) || [])]
                        coverageSummary[index] = { ...row, unit: e.target.value }
                        update({ coverageSummary })
                      }}
                    />
                    <Button
                      variant="ghost"
                      onClick={() =>
                        update({
                          coverageSummary: ((current.coverageSummary as typeof row[]) || []).filter((_, i) => i !== index),
                        })
                      }
                    >
                      ลบ
                    </Button>
                  </div>
                ))}
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>ตารางเปรียบเทียบ (coverageRows)</CardTitle>
                <CardDescription>values คั่นด้วย | เช่น 500,000|1,000,000|1,500,000</CardDescription>
              </CardHeader>
              <CardContent className="space-y-2">
                {((current.coverageRows as Array<{ label: string; values: string[] }>) || []).map((row, index) => (
                  <div key={index} className="grid gap-2 md:grid-cols-[1fr_2fr_auto]">
                    <Input
                      placeholder="หัวข้อแถว"
                      value={row.label}
                      onChange={(e) => {
                        const coverageRows = [...((current.coverageRows as typeof row[]) || [])]
                        coverageRows[index] = { ...row, label: e.target.value }
                        update({ coverageRows })
                      }}
                    />
                    <Input
                      placeholder="ค่าต่อแผน คั่น |"
                      value={(row.values || []).join('|')}
                      onChange={(e) => {
                        const coverageRows = [...((current.coverageRows as typeof row[]) || [])]
                        coverageRows[index] = {
                          ...row,
                          values: e.target.value.split('|').map((s) => s.trim()),
                        }
                        update({ coverageRows })
                      }}
                    />
                    <Button
                      variant="ghost"
                      onClick={() =>
                        update({
                          coverageRows: ((current.coverageRows as typeof row[]) || []).filter((_, i) => i !== index),
                        })
                      }
                    >
                      ลบ
                    </Button>
                  </div>
                ))}
                <Button
                  size="sm"
                  variant="outline"
                  onClick={() =>
                    update({
                      coverageRows: [...((current.coverageRows as Array<{ label: string; values: string[] }>) || []), { label: '', values: ['', '', ''] }],
                    })
                  }
                >
                  <Plus className="h-4 w-4" /> เพิ่มแถว
                </Button>
              </CardContent>
            </Card>
          </div>
        </TabsContent>

        <TabsContent value="faqs">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between">
              <CardTitle>FAQ ของแผนนี้</CardTitle>
              <Button
                size="sm"
                variant="outline"
                onClick={() => update({ faqs: [...(current.faqs || []), { q: '', a: '' }] })}
              >
                <Plus className="h-4 w-4" /> เพิ่ม
              </Button>
            </CardHeader>
            <CardContent className="space-y-3">
              {(current.faqs || []).map((item, index) => (
                <div key={index} className="space-y-2 rounded-lg border border-[var(--color-border)] p-3">
                  <Input
                    placeholder="คำถาม"
                    value={item.q}
                    onChange={(e) => {
                      const faqs = [...(current.faqs || [])]
                      faqs[index] = { ...item, q: e.target.value }
                      update({ faqs })
                    }}
                  />
                  <Textarea
                    rows={3}
                    placeholder="คำตอบ"
                    value={item.a}
                    onChange={(e) => {
                      const faqs = [...(current.faqs || [])]
                      faqs[index] = { ...item, a: e.target.value }
                      update({ faqs })
                    }}
                  />
                  <Button size="sm" variant="ghost" onClick={() => update({ faqs: (current.faqs || []).filter((_, i) => i !== index) })}>
                    ลบรายการนี้
                  </Button>
                </div>
              ))}
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  )
}
