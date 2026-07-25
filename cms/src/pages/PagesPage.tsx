import { useMemo, useState } from 'react'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { useContent } from '@/context/ContentContext'

const PAGE_LABELS: Record<string, string> = {
  home: 'หน้าแรก',
  about: 'เกี่ยวกับเรา',
  contact: 'ติดต่อเรา',
  register: 'สมัครตัวแทน',
  faq: 'FAQ',
  plans: 'แผนประกัน',
  promotions: 'โปรโมชัน',
  blogs: 'บทความ',
}

function isMultiline(key: string, value: string) {
  return value.length > 80 || /title|text|headline|html|desc|body/i.test(key) || value.includes('<br')
}

export function PagesPage() {
  const { pages, setPageField, save } = useContent()
  const slugs = useMemo(() => Object.keys(pages).sort((a, b) => (a === 'home' ? -1 : b === 'home' ? 1 : a.localeCompare(b))), [pages])
  const [active, setActive] = useState(slugs[0] || 'home')
  const current = pages[active] || {}

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-end justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">หน้าอื่นๆ</h1>
          <p className="text-sm text-[var(--color-muted-foreground)]">แก้ไขข้อความทุกส่วนของแต่ละหน้า (หน้าแรกแนะนำใช้ Visual Editor)</p>
        </div>
        <Button onClick={() => void save(false)}>บันทึก</Button>
      </div>

      <Tabs value={active} onValueChange={setActive}>
        <TabsList className="flex h-auto flex-wrap justify-start gap-1">
          {slugs.map((slug) => (
            <TabsTrigger key={slug} value={slug}>
              {PAGE_LABELS[slug] || slug}
            </TabsTrigger>
          ))}
        </TabsList>
        {slugs.map((slug) => (
          <TabsContent key={slug} value={slug}>
            <Card>
              <CardHeader>
                <CardTitle>{PAGE_LABELS[slug] || slug}</CardTitle>
                <CardDescription>slug: {slug}</CardDescription>
              </CardHeader>
              <CardContent className="grid gap-4 md:grid-cols-2">
                {Object.entries(pages[slug] || {}).map(([key, value]) => (
                  <div key={key} className={`space-y-1.5 ${isMultiline(key, value) ? 'md:col-span-2' : ''}`}>
                    <Label htmlFor={`${slug}-${key}`}>{key}</Label>
                    {isMultiline(key, value) ? (
                      <Textarea
                        id={`${slug}-${key}`}
                        rows={4}
                        value={current[key] ?? value}
                        onChange={(e) => setPageField(slug, key, e.target.value)}
                      />
                    ) : (
                      <Input
                        id={`${slug}-${key}`}
                        value={pages[slug]?.[key] ?? ''}
                        onChange={(e) => setPageField(slug, key, e.target.value)}
                      />
                    )}
                  </div>
                ))}
                {Object.keys(pages[slug] || {}).length === 0 && (
                  <p className="text-sm text-slate-500 md:col-span-2">ยังไม่มีฟิลด์สำหรับหน้านี้</p>
                )}
              </CardContent>
            </Card>
          </TabsContent>
        ))}
      </Tabs>
    </div>
  )
}
