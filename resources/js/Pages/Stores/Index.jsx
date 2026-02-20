import { Head } from '@inertiajs/react'
import { Typography, Card } from 'antd'

const { Title } = Typography

export default function Stores() {
  return (
    <>
      <Head title="Stores" />
      <Title level={3}>Stores</Title>
      <Card>
        <p>Stores management content goes here.</p>
      </Card>
    </>
  )
}
