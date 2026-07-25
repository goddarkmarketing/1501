import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { useContent } from '@/context/ContentContext'

const LABELS: Record<string, string> = {
  site_name: 'ชื่อเว็บไซต์',
  site_tagline: 'คำโปรย',
  phone: 'เบอร์โทรหลัก',
  phone2: 'เบอร์โทรสำรอง',
  business_hours: 'เวลาทำการ',
  copyright: 'ลิขสิทธิ์',
  email: 'อีเมล',
  line_id: 'LINE ID',
  facebook: 'Facebook',
  tiktok: 'TikTok',
  facebook_url: 'ลิงก์ Facebook',
  line_url: 'ลิงก์ LINE',
  tiktok_url: 'ลิงก์ TikTok',
  youtube_url: 'ลิงก์ YouTube',
  instagram_url: 'ลิงก์ Instagram',
  privacy_url: 'นโยบายความเป็นส่วนตัว',
  terms_url: 'ข้อกำหนดการใช้งาน',
  logo_url: 'โลโก้',
  address: 'ที่อยู่',
  primary_color: 'สีหลัก',
}

export function SettingsPage() {
  const { settings, setSetting, save } = useContent()
  const keys = Object.keys(settings).sort()

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-end justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">ตั้งค่าเว็บไซต์</h1>
          <p className="text-sm text-[var(--color-muted-foreground)]">ข้อมูลติดต่อ โซเชียล และค่าทั่วไป</p>
        </div>
        <Button onClick={() => void save(false)}>บันทึก</Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>ค่าทั้งหมด</CardTitle>
        </CardHeader>
        <CardContent className="grid gap-4 md:grid-cols-2">
          {keys.map((key) => (
            <div key={key} className="space-y-1.5">
              <Label htmlFor={key}>{LABELS[key] || key}</Label>
              <Input id={key} value={settings[key] || ''} onChange={(e) => setSetting(key, e.target.value)} />
            </div>
          ))}
        </CardContent>
      </Card>
    </div>
  )
}
