import { Head } from '@inertiajs/react'
import { Typography, Card } from 'antd'

const { Title } = Typography

export default function Products() {
  return (
    <>
      <Head title="Products" />
      <Title level={3}>Products</Title>
      <Card>
        <p>Products management content goes here.</p>
      </Card>
    </>
  )
}
