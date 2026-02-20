import { Head } from '@inertiajs/react'
import { Typography, Card } from 'antd'

const { Title } = Typography

export default function PurchaseOrders() {
  return (
    <>
      <Head title="Purchase Orders" />
      <Title level={3}>Purchase Orders</Title>
      <Card>
        <p>Purchase orders management content goes here.</p>
      </Card>
    </>
  )
}
