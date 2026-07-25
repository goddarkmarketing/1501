import { Link } from 'react-router-dom'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { useContent } from '@/context/ContentContext'
import { Pencil } from 'lucide-react'

export function PlansPage() {
  const { plans, categories } = useContent()
  const list = Object.values(plans).sort((a, b) => String(a.name).localeCompare(String(b.name), 'th'))

  return (
    <div className="space-y-4">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">แผนประกัน</h1>
        <p className="text-sm text-[var(--color-muted-foreground)]">ฟอร์มละเอียด — แก้ไขได้ทุกส่วนของแผน</p>
      </div>

      <div className="grid gap-3">
        {list.map((plan) => (
          <Card key={plan.id}>
            <CardHeader className="flex flex-row items-start justify-between gap-3 space-y-0">
              <div>
                <CardTitle className="text-base">{plan.name}</CardTitle>
                <CardDescription className="mt-1">
                  ID: {plan.id} · หมวด: {categories[plan.category]?.label || plan.category}
                </CardDescription>
              </div>
              <div className="flex items-center gap-2">
                {plan.promo?.code && <Badge variant="secondary">{plan.promo.code}</Badge>}
                <Button asChild size="sm">
                  <Link to={`/plans/${plan.id}`}>
                    <Pencil className="h-3.5 w-3.5" /> แก้ไข
                  </Link>
                </Button>
              </div>
            </CardHeader>
            <CardContent className="text-sm text-slate-600">
              {plan.headline || plan.tagline || '—'}
            </CardContent>
          </Card>
        ))}
        {list.length === 0 && (
          <Card>
            <CardContent className="py-10 text-center text-slate-500">ยังไม่มีแผนประกัน</CardContent>
          </Card>
        )}
      </div>
    </div>
  )
}
