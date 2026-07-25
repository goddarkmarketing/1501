import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { getSiteBase } from '@/lib/content'
import { ExternalLink } from 'lucide-react'

export function BlogsPage() {
  const base = getSiteBase()
  return (
    <div className="space-y-4">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">บทความ</h1>
        <p className="text-sm text-[var(--color-muted-foreground)]">จัดการบทความผ่านฟอร์มละเอียดของระบบเดิม</p>
      </div>
      <Card>
        <CardHeader>
          <CardTitle>เปิดตัวแก้ไขบทความ</CardTitle>
          <CardDescription>
            บทความมีโครงสร้างเนื้อหาหลายบล็อก — ใช้หน้า admin PHP เพื่อแก้ไขแบบละเอียด แล้วกดเผยแพร่จาก CMS นี้ได้
          </CardDescription>
        </CardHeader>
        <CardContent className="flex flex-wrap gap-2">
          <Button asChild>
            <a href={`${base}/admin/blogs.php`} target="_blank" rel="noreferrer">
              <ExternalLink className="h-4 w-4" /> เปิดจัดการบทความ
            </a>
          </Button>
          <Button asChild variant="outline">
            <a href={`${base}/blogs.html`} target="_blank" rel="noreferrer">
              ดูหน้ารายการบทความ
            </a>
          </Button>
        </CardContent>
      </Card>
    </div>
  )
}
