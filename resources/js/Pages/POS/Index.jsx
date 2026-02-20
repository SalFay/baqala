import { Head } from '@inertiajs/react'
import { Typography, Card } from 'antd'

const { Title } = Typography

export default function POS() {
  return (
    <>
      <Head title="POS" />
      <Title level={3}>POS</Title>
      <Card>
        <p>Point of Sale content goes here.</p>
      </Card>
    </>
  )
}
