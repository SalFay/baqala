import { Head } from '@inertiajs/react'
import { Typography, Card } from 'antd'

const { Title } = Typography

export default function Returns() {
  return (
    <>
      <Head title="Returns" />
      <Title level={3}>Returns</Title>
      <Card>
        <p>Returns management content goes here.</p>
      </Card>
    </>
  )
}
