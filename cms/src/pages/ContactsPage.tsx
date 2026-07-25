import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { getSiteBase } from '@/lib/content'
import { ExternalLink } from 'lucide-react'

export function ContactsPage() {
  const base = getSiteBase()
  return (
    <div className="space-y-4">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">ข้อความติดต่อ</h1>
        <p className="text-sm text-[var(--color-muted-foreground)]">ข้อความจากฟอร์มหน้าบ้าน (ต้องมี PHP + ฐานข้อมูล)</p>
      </div>
      <Card>
        <CardHeader>
          <CardTitle>กล่องข้อความ</CardTitle>
          <CardDescription>
            บน GitHub Pages จะดูข้อความไม่ได้เพราะไม่มีเซิร์ฟเวอร์ PHP — ใช้ XAMPP/VPS สำหรับส่วนนี้
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Button asChild>
            <a href={`${base}/admin/contacts.php`} target="_blank" rel="noreferrer">
              <ExternalLink className="h-4 w-4" /> เปิดกล่องข้อความ
            </a>
          </Button>
        </CardContent>
      </Card>
    </div>
  )
}
