import { HashRouter, Navigate, Route, Routes } from 'react-router-dom'
import { Toaster } from 'sonner'
import { ContentProvider } from '@/context/ContentContext'
import { AppShell } from '@/components/AppShell'
import { DashboardPage } from '@/pages/DashboardPage'
import { HomeVisualPage } from '@/pages/HomeVisualPage'
import { PagesPage } from '@/pages/PagesPage'
import { PlansPage } from '@/pages/PlansPage'
import { PlanEditPage } from '@/pages/PlanEditPage'
import { CategoriesPage } from '@/pages/CategoriesPage'
import { FaqPage } from '@/pages/FaqPage'
import { BlocksPage } from '@/pages/BlocksPage'
import { SettingsPage } from '@/pages/SettingsPage'
import { BlogsPage } from '@/pages/BlogsPage'
import { PromotionsPage } from '@/pages/PromotionsPage'
import { ContactsPage } from '@/pages/ContactsPage'

export default function App() {
  return (
    <HashRouter>
      <ContentProvider>
        <Routes>
          <Route element={<AppShell />}>
            <Route index element={<DashboardPage />} />
            <Route path="home-visual" element={<HomeVisualPage />} />
            <Route path="pages" element={<PagesPage />} />
            <Route path="plans" element={<PlansPage />} />
            <Route path="plans/:id" element={<PlanEditPage />} />
            <Route path="categories" element={<CategoriesPage />} />
            <Route path="faq" element={<FaqPage />} />
            <Route path="blocks" element={<BlocksPage />} />
            <Route path="settings" element={<SettingsPage />} />
            <Route path="blogs" element={<BlogsPage />} />
            <Route path="promotions" element={<PromotionsPage />} />
            <Route path="contacts" element={<ContactsPage />} />
            <Route path="*" element={<Navigate to="/" replace />} />
          </Route>
        </Routes>
        <Toaster richColors position="top-right" />
      </ContentProvider>
    </HashRouter>
  )
}
