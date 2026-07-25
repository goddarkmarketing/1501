import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Textarea } from '@/components/ui/textarea'
import { Button } from '@/components/ui/button'
import { useContent } from '@/context/ContentContext'

export function BlocksPage() {
  const { blocks, setBlock, save } = useContent()
  const keys = Object.keys(blocks).sort()

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-end justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">บล็อก / เมนู / ฟุตเตอร์</h1>
          <p className="text-sm text-[var(--color-muted-foreground)]">แก้ไข JSON ของแต่ละบล็อก (เมนูนำทาง, ปุ่มลอย, ฟุตเตอร์ ฯลฯ)</p>
        </div>
        <Button onClick={() => void save(false)}>บันทึก</Button>
      </div>

      <div className="space-y-4">
        {keys.map((key) => {
          const raw = JSON.stringify(blocks[key] ?? {}, null, 2)
          return (
            <Card key={key}>
              <CardHeader>
                <CardTitle className="font-mono text-base">{key}</CardTitle>
                <CardDescription>แก้ไขโครงสร้าง JSON แล้วกดบันทึก</CardDescription>
              </CardHeader>
              <CardContent>
                <Textarea
                  rows={12}
                  className="font-mono text-xs"
                  defaultValue={raw}
                  key={raw.slice(0, 40)}
                  onBlur={(e) => {
                    try {
                      setBlock(key, JSON.parse(e.target.value))
                    } catch {
                      /* keep previous until valid */
                    }
                  }}
                />
              </CardContent>
            </Card>
          )
        })}
        {keys.length === 0 && <p className="text-sm text-slate-500">ยังไม่มีบล็อก</p>}
      </div>
    </div>
  )
}
