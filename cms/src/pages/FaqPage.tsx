import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Button } from '@/components/ui/button'
import { useContent } from '@/context/ContentContext'
import { Plus, Trash2 } from 'lucide-react'

export function FaqPage() {
  const { faqs, setFaqs, save } = useContent()

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-end justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">คำถามที่พบบ่อย</h1>
          <p className="text-sm text-[var(--color-muted-foreground)]">แสดงบนหน้าแรกและหน้า FAQ</p>
        </div>
        <div className="flex gap-2">
          <Button variant="outline" onClick={() => setFaqs([...faqs, { q: '', a: '' }])}>
            <Plus className="h-4 w-4" /> เพิ่มคำถาม
          </Button>
          <Button onClick={() => void save(false)}>บันทึก</Button>
        </div>
      </div>

      <div className="space-y-3">
        {faqs.map((item, index) => (
          <Card key={index}>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-base">ข้อที่ {index + 1}</CardTitle>
              <Button size="icon" variant="ghost" onClick={() => setFaqs(faqs.filter((_, i) => i !== index))}>
                <Trash2 className="h-4 w-4 text-red-500" />
              </Button>
            </CardHeader>
            <CardContent className="space-y-2">
              <Input
                placeholder="คำถาม"
                value={item.q}
                onChange={(e) => {
                  const next = [...faqs]
                  next[index] = { ...item, q: e.target.value }
                  setFaqs(next)
                }}
              />
              <Textarea
                rows={4}
                placeholder="คำตอบ"
                value={item.a}
                onChange={(e) => {
                  const next = [...faqs]
                  next[index] = { ...item, a: e.target.value }
                  setFaqs(next)
                }}
              />
            </CardContent>
          </Card>
        ))}
        {faqs.length === 0 && <p className="text-sm text-slate-500">ยังไม่มี FAQ — กดเพิ่มคำถาม</p>}
      </div>
    </div>
  )
}
