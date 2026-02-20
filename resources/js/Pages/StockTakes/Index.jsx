import { Head } from '@inertiajs/react'
import { Typography, Card } from 'antd'

const { Title } = Typography

export default function StockTakes() {
  return (
    <>
      <Head title="Stock Takes" />
      <Title level={3}>Stock Takes</Title>
      <Card>
        <p>Stock takes management content goes here.</p>
      </Card>
    </>
  )
}
