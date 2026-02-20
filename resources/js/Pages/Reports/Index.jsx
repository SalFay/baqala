import { Head } from '@inertiajs/react'
import { Typography, Card } from 'antd'

const { Title } = Typography

export default function Reports() {
  return (
    <>
      <Head title="Reports" />
      <Title level={3}>Reports</Title>
      <Card>
        <p>Reports content goes here.</p>
      </Card>
    </>
  )
}
