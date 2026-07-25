import { Link } from 'react-router-dom'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { useContent } from '@/context/ContentContext'
import { Home, Shield, HelpCircle, Blocks } from 'lucide-react'

export function DashboardPage() {
  const { pages, plans, faqs, categories, mode, dirty } = useContent()
  const planCount = Object.keys(plans).length
  const catCount = Object.keys(categories).length
  const pageCount = Object.keys(pages).length

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">แดชบอร์ด</h1>
        <p className="text-sm text-[var(--color-muted-foreground)]">
          ภาพรวมระบบหลังบ้าน · โหมดปัจจุบัน: {mode === 'api' ? 'เซิร์ฟเวอร์ PHP + ฐานข้อมูล' : 'Static / GitHub Pages'}
          {dirty ? ' · มีการแก้ไขที่ยังไม่บันทึก' : ''}
        </p>
      </div>

      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <Card>
          <CardHeader>
            <CardTitle>หน้าเว็บ</CardTitle>
            <CardDescription>จำนวนหน้าใน SITE_PAGES</CardDescription>
          </CardHeader>
          <CardContent className="text-3xl font-bold text-[var(--color-primary)]">{pageCount}</CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>แผนประกัน</CardTitle>
            <CardDescription>ผลิตภัณฑ์ที่เผยแพร่</CardDescription>
          </CardHeader>
          <CardContent className="text-3xl font-bold text-[var(--color-primary)]">{planCount}</CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>หมวดหมู่</CardTitle>
            <CardDescription>หมวดแผนประกัน</CardDescription>
          </CardHeader>
          <CardContent className="text-3xl font-bold text-[var(--color-primary)]">{catCount}</CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>FAQ</CardTitle>
            <CardDescription>คำถามที่พบบ่อย</CardDescription>
          </CardHeader>
          <CardContent className="text-3xl font-bold text-[var(--color-primary)]">{faqs.length}</CardContent>
        </Card>
      </div>

      <div className="grid gap-4 lg:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Home className="h-4 w-4" /> แก้ไขหน้าแรกแบบเห็นจริง
            </CardTitle>
            <CardDescription>คลิกข้อความบนพรีวิวเพื่อแก้ไข — เหมือนหน้าบ้าน</CardDescription>
          </CardHeader>
          <CardContent>
            <Button asChild>
              <Link to="/home-visual">เปิด Visual Editor</Link>
            </Button>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Shield className="h-4 w-4" /> แผนประกัน
            </CardTitle>
            <CardDescription>ฟอร์มละเอียด แก้ไขได้ทุกส่วนของแผน</CardDescription>
          </CardHeader>
          <CardContent className="flex gap-2">
            <Button asChild>
              <Link to="/plans">จัดการแผน</Link>
            </Button>
            <Button asChild variant="outline">
              <Link to="/categories">หมวดหมู่</Link>
            </Button>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <HelpCircle className="h-4 w-4" /> FAQ
            </CardTitle>
            <CardDescription>คำถาม–คำตอบที่แสดงบนหน้าแรกและหน้า FAQ</CardDescription>
          </CardHeader>
          <CardContent>
            <Button asChild variant="secondary">
              <Link to="/faq">แก้ไข FAQ</Link>
            </Button>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Blocks className="h-4 w-4" /> บล็อกเนื้อหา
            </CardTitle>
            <CardDescription>ฟุตเตอร์, เมนู, ปุ่มลอย, ส่วนเด่นหน้าแรก</CardDescription>
          </CardHeader>
          <CardContent>
            <Button asChild variant="outline">
              <Link to="/blocks">จัดการบล็อก</Link>
            </Button>
          </CardContent>
        </Card>
      </div>
    </div>
  )
}
