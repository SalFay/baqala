import { Head } from '@inertiajs/react'
import { Typography, Card } from 'antd'

const { Title } = Typography

export default function StockTransfers() {
  return (
    <>
      <Head title="Stock Transfers" />
      <Title level={3}>Stock Transfers</Title>
      <Card>
        <p>Stock transfers management content goes here.</p>
      </Card>
    </>
  )
}
