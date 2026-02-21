import { Head } from '@inertiajs/react'
import { Card, Empty } from 'antd'
import GlobalPageHeader from '@/Components/GlobalPageHeader'

export default function Stores() {
  return (
    <>
      <Head title="Stores" />

      <GlobalPageHeader
        title="Stores"
        parentPageTitle="Dashboard"
        parentPageRoute="dashboard"
      />

      <Card>
        <Empty description="Stores management coming soon" />
      </Card>
    </>
  )
}
