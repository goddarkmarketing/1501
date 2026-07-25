import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { getSiteBase } from '@/lib/content'
import { ExternalLink } from 'lucide-react'

export function PromotionsPage() {
  const base = getSiteBase()
  return (
    <div className="space-y-4">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">โปรโมชัน</h1>
        <p className="text-sm text-[var(--color-muted-foreground)]">จัดการโปรโมชันและตัวกรองหมวด</p>
      </div>
      <Card>
        <CardHeader>
          <CardTitle>เปิดตัวแก้ไขโปรโมชัน</CardTitle>
          <CardDescription>ใช้หน้า admin PHP สำหรับรูปภาพ บัตรโปรโมชัน และตัวกรอง แล้วกลับมาเผยแพร่ที่นี่</CardDescription>
        </CardHeader>
        <CardContent className="flex flex-wrap gap-2">
          <Button asChild>
            <a href={`${base}/admin/promotions.php`} target="_blank" rel="noreferrer">
              <ExternalLink className="h-4 w-4" /> เปิดจัดการโปรโมชัน
            </a>
          </Button>
          <Button asChild variant="outline">
            <a href={`${base}/admin/promo-filters.php`} target="_blank" rel="noreferrer">
              ตัวกรองโปรโมชัน
            </a>
          </Button>
        </CardContent>
      </Card>
    </div>
  )
}
