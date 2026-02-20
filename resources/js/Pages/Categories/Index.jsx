import { Head } from '@inertiajs/react'
import { Typography, Card } from 'antd'

const { Title } = Typography

export default function Categories() {
  return (
    <>
      <Head title="Categories" />
      <Title level={3}>Categories</Title>
      <Card>
        <p>Categories management content goes here.</p>
      </Card>
    </>
  )
}
