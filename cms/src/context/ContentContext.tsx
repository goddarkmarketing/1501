import { createContext, useCallback, useContext, useEffect, useMemo, useState, type ReactNode } from 'react'
import {
  loadSiteBundle,
  publishSite,
  saveSiteBundle,
  type CategoryMeta,
  type PlanProduct,
  type SiteBundle,
} from '@/lib/content'
import { toast } from 'sonner'

type ContentContextValue = {
  loading: boolean
  dirty: boolean
  mode: 'api' | 'static' | null
  pages: Record<string, Record<string, string>>
  settings: Record<string, string>
  blocks: Record<string, unknown>
  faqs: Array<{ q: string; a: string }>
  plans: Record<string, PlanProduct>
  categories: Record<string, CategoryMeta>
  setPageField: (page: string, key: string, value: string) => void
  setSetting: (key: string, value: string) => void
  setBlock: (key: string, value: unknown) => void
  setFaqs: (faqs: Array<{ q: string; a: string }>) => void
  setPlan: (id: string, plan: PlanProduct) => void
  setCategory: (id: string, cat: CategoryMeta) => void
  removeCategory: (id: string) => void
  reload: () => Promise<void>
  save: (publish?: boolean) => Promise<void>
  publish: () => Promise<void>
}

const ContentContext = createContext<ContentContextValue | null>(null)

export function ContentProvider({ children }: { children: ReactNode }) {
  const [loading, setLoading] = useState(true)
  const [dirty, setDirty] = useState(false)
  const [mode, setMode] = useState<'api' | 'static' | null>(null)
  const [pages, setPages] = useState<Record<string, Record<string, string>>>({})
  const [settings, setSettings] = useState<Record<string, string>>({})
  const [blocks, setBlocks] = useState<Record<string, unknown>>({})
  const [faqs, setFaqsState] = useState<Array<{ q: string; a: string }>>([])
  const [plans, setPlans] = useState<Record<string, PlanProduct>>({})
  const [categories, setCategories] = useState<Record<string, CategoryMeta>>({})

  const applyBundle = useCallback((bundle: SiteBundle) => {
    setPages(bundle.pages || {})
    setSettings(bundle.settings || {})
    setBlocks(bundle.blocks || {})
    setFaqsState(bundle.faqs || [])
    setPlans(bundle.plans || {})
    setCategories(bundle.categories || {})
    setMode(bundle.mode)
    setDirty(false)
  }, [])

  const reload = useCallback(async () => {
    setLoading(true)
    try {
      const bundle = await loadSiteBundle()
      applyBundle(bundle)
    } catch (e) {
      toast.error(e instanceof Error ? e.message : 'โหลดข้อมูลไม่สำเร็จ')
    } finally {
      setLoading(false)
    }
  }, [applyBundle])

  useEffect(() => {
    void reload()
  }, [reload])

  const setPageField = useCallback((page: string, key: string, value: string) => {
    setPages((prev) => ({ ...prev, [page]: { ...(prev[page] || {}), [key]: value } }))
    setDirty(true)
  }, [])

  const setSetting = useCallback((key: string, value: string) => {
    setSettings((prev) => ({ ...prev, [key]: value }))
    setDirty(true)
  }, [])

  const setBlock = useCallback((key: string, value: unknown) => {
    setBlocks((prev) => ({ ...prev, [key]: value }))
    setDirty(true)
  }, [])

  const setFaqs = useCallback((next: Array<{ q: string; a: string }>) => {
    setFaqsState(next)
    setDirty(true)
  }, [])

  const setPlan = useCallback((id: string, plan: PlanProduct) => {
    setPlans((prev) => ({ ...prev, [id]: plan }))
    setDirty(true)
  }, [])

  const setCategory = useCallback((id: string, cat: CategoryMeta) => {
    setCategories((prev) => ({ ...prev, [id]: cat }))
    setDirty(true)
  }, [])

  const removeCategory = useCallback((id: string) => {
    setCategories((prev) => {
      const next = { ...prev }
      delete next[id]
      return next
    })
    setDirty(true)
  }, [])

  const save = useCallback(
    async (publish = false) => {
      const result = await saveSiteBundle(
        { pages, settings, blocks, faqs, plans, categories },
        { publish },
      )
      if (result.ok) {
        setDirty(false)
        toast.success(result.message)
      } else {
        toast.error(result.message)
      }
    },
    [pages, settings, blocks, faqs, plans, categories],
  )

  const publish = useCallback(async () => {
    await save(true)
    const result = await publishSite()
    if (result.ok) toast.success(result.message)
    else toast.message(result.message)
  }, [save])

  const value = useMemo(
    () => ({
      loading,
      dirty,
      mode,
      pages,
      settings,
      blocks,
      faqs,
      plans,
      categories,
      setPageField,
      setSetting,
      setBlock,
      setFaqs,
      setPlan,
      setCategory,
      removeCategory,
      reload,
      save,
      publish,
    }),
    [
      loading,
      dirty,
      mode,
      pages,
      settings,
      blocks,
      faqs,
      plans,
      categories,
      setPageField,
      setSetting,
      setBlock,
      setFaqs,
      setPlan,
      setCategory,
      removeCategory,
      reload,
      save,
      publish,
    ],
  )

  return <ContentContext.Provider value={value}>{children}</ContentContext.Provider>
}

export function useContent() {
  const ctx = useContext(ContentContext)
  if (!ctx) throw new Error('useContent must be used within ContentProvider')
  return ctx
}
