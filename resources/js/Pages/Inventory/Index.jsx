import { Head } from '@inertiajs/react'
import { Typography, Card } from 'antd'

const { Title } = Typography

export default function Inventory() {
  return (
    <>
      <Head title="Inventory" />
      <Title level={3}>Inventory</Title>
      <Card>
        <p>Inventory management content goes here.</p>
      </Card>
    </>
  )
}
