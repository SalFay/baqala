import { Head } from '@inertiajs/react'
import { Typography, Card } from 'antd'

const { Title } = Typography

export default function Statements() {
  return (
    <>
      <Head title="Statements" />
      <Title level={3}>Statements</Title>
      <Card>
        <p>Statements content goes here.</p>
      </Card>
    </>
  )
}
