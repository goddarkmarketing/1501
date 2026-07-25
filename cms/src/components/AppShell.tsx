import { NavLink, Outlet } from 'react-router-dom'
import {
  LayoutDashboard,
  Home,
  FileText,
  Shield,
  FolderTree,
  HelpCircle,
  Newspaper,
  BadgePercent,
  Blocks,
  Settings,
  Mail,
  Save,
  Upload,
  ExternalLink,
} from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'
import { useContent } from '@/context/ContentContext'
import { getSiteBase } from '@/lib/content'
import { cn } from '@/lib/utils'

const groups = [
  {
    label: 'ภาพรวม',
    items: [{ to: '/', label: 'แดชบอร์ด', icon: LayoutDashboard, end: true }],
  },
  {
    label: 'หน้าเว็บ',
    items: [
      { to: '/home-visual', label: 'หน้าแรก (แก้ไขแบบเห็นจริง)', icon: Home },
      { to: '/pages', label: 'หน้าอื่นๆ', icon: FileText },
    ],
  },
  {
    label: 'ผลิตภัณฑ์',
    items: [
      { to: '/plans', label: 'แผนประกัน', icon: Shield },
      { to: '/categories', label: 'หมวดหมู่แผน', icon: FolderTree },
    ],
  },
  {
    label: 'เนื้อหา',
    items: [
      { to: '/faq', label: 'คำถามที่พบบ่อย', icon: HelpCircle },
      { to: '/blogs', label: 'บทความ', icon: Newspaper },
      { to: '/promotions', label: 'โปรโมชัน', icon: BadgePercent },
      { to: '/blocks', label: 'บล็อก / เมนู / ฟุตเตอร์', icon: Blocks },
    ],
  },
  {
    label: 'ระบบ',
    items: [
      { to: '/settings', label: 'ตั้งค่าเว็บไซต์', icon: Settings },
      { to: '/contacts', label: 'ข้อความติดต่อ', icon: Mail },
    ],
  },
]

export function AppShell() {
  const { dirty, mode, loading, save, publish } = useContent()
  const siteBase = getSiteBase()

  return (
    <div className="min-h-screen lg:grid lg:grid-cols-[260px_1fr]">
      <aside className="border-r border-[var(--color-border)] bg-white">
        <div className="sticky top-0 flex h-screen flex-col">
          <div className="border-b border-[var(--color-border)] px-5 py-5">
            <p className="text-lg font-bold text-[var(--color-primary)]">Agent Thailand</p>
            <p className="text-xs text-[var(--color-muted-foreground)]">CMS หลังบ้าน · Building Blocks</p>
            <div className="mt-3 flex flex-wrap gap-2">
              <Badge variant={mode === 'api' ? 'success' : 'secondary'}>
                {mode === 'api' ? 'เชื่อม PHP/DB' : mode === 'static' ? 'โหมด Git/Static' : 'กำลังโหลด'}
              </Badge>
              {dirty && <Badge variant="outline">มีการแก้ไข</Badge>}
            </div>
          </div>
          <nav className="flex-1 space-y-5 overflow-y-auto p-3 text-sm">
            {groups.map((group) => (
              <div key={group.label}>
                <p className="mb-1.5 px-3 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                  {group.label}
                </p>
                <div className="space-y-0.5">
                  {group.items.map((item) => {
                    const Icon = item.icon
                    return (
                      <NavLink
                        key={item.to}
                        to={item.to}
                        end={'end' in item ? item.end : false}
                        className={({ isActive }) =>
                          cn(
                            'flex items-center gap-2.5 rounded-lg px-3 py-2 text-slate-600 transition-colors hover:bg-slate-50',
                            isActive && 'bg-[var(--color-primary)] text-white hover:bg-[var(--color-primary)]',
                          )
                        }
                      >
                        <Icon className="h-4 w-4 shrink-0 opacity-80" />
                        <span className="leading-snug">{item.label}</span>
                      </NavLink>
                    )
                  })}
                </div>
              </div>
            ))}
          </nav>
          <div className="space-y-2 border-t border-[var(--color-border)] p-3">
            <Button className="w-full" disabled={loading} onClick={() => void save(false)}>
              <Save className="h-4 w-4" />
              บันทึก
            </Button>
            <Button className="w-full" variant="success" disabled={loading} onClick={() => void publish()}>
              <Upload className="h-4 w-4" />
              เผยแพร่เว็บไซต์
            </Button>
            <a
              href={`${siteBase}/index.html`}
              target="_blank"
              rel="noreferrer"
              className="flex items-center justify-center gap-2 rounded-md px-3 py-2 text-xs text-slate-500 hover:bg-slate-50"
            >
              <ExternalLink className="h-3.5 w-3.5" />
              เปิดเว็บจริง
            </a>
          </div>
        </div>
      </aside>

      <div className="min-w-0">
        <header className="sticky top-0 z-20 flex items-center justify-between border-b border-[var(--color-border)] bg-white/90 px-6 py-3 backdrop-blur">
          <div>
            <p className="text-sm font-semibold">ระบบจัดการเนื้อหา</p>
            <p className="text-xs text-[var(--color-muted-foreground)]">
              แก้ไขทุกส่วนของเว็บ · หน้าแรกแก้แบบเห็นจริง · แผนประกันแก้ผ่านฟอร์มละเอียด
            </p>
          </div>
          {dirty && (
            <Button size="sm" onClick={() => void save(false)}>
              บันทึกการเปลี่ยนแปลง
            </Button>
          )}
        </header>
        <main className="p-6">
          {loading ? (
            <div className="rounded-xl border border-dashed border-slate-300 bg-white p-12 text-center text-slate-500">
              กำลังโหลดข้อมูลเว็บไซต์...
            </div>
          ) : (
            <Outlet />
          )}
        </main>
      </div>
      <Separator className="hidden" />
    </div>
  )
}
