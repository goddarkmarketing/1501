import type { HTMLAttributes } from 'react'
import { cn } from '@/lib/utils'

export function Badge({
  className,
  variant = 'default',
  ...props
}: HTMLAttributes<HTMLSpanElement> & { variant?: 'default' | 'secondary' | 'success' | 'outline' }) {
  return (
    <span
      className={cn(
        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
        variant === 'default' && 'bg-[var(--color-primary)] text-white',
        variant === 'secondary' && 'bg-[var(--color-secondary)] text-[var(--color-secondary-foreground)]',
        variant === 'success' && 'bg-emerald-100 text-emerald-800',
        variant === 'outline' && 'border border-[var(--color-border)] text-slate-700',
        className,
      )}
      {...props}
    />
  )
}
