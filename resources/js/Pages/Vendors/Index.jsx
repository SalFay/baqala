import { Head } from '@inertiajs/react'
import { Typography, Card } from 'antd'

const { Title } = Typography

export default function Vendors() {
  return (
    <>
      <Head title="Vendors" />
      <Title level={3}>Vendors</Title>
      <Card>
        <p>Vendors management content goes here.</p>
      </Card>
    </>
  )
}
