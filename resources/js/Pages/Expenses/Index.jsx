import { Head } from '@inertiajs/react'
import { Typography, Card } from 'antd'

const { Title } = Typography

export default function Expenses() {
  return (
    <>
      <Head title="Expenses" />
      <Title level={3}>Expenses</Title>
      <Card>
        <p>Expenses management content goes here.</p>
      </Card>
    </>
  )
}
