import { Head } from '@inertiajs/react'
import { Typography, Card } from 'antd'

const { Title } = Typography

export default function Customers() {
  return (
    <>
      <Head title="Customers" />
      <Title level={3}>Customers</Title>
      <Card>
        <p>Customers management content goes here.</p>
      </Card>
    </>
  )
}
