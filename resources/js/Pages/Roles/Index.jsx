import { Head } from '@inertiajs/react'
import { Typography, Card } from 'antd'

const { Title } = Typography

export default function Roles() {
  return (
    <>
      <Head title="Roles" />
      <Title level={3}>Roles</Title>
      <Card>
        <p>Roles management content goes here.</p>
      </Card>
    </>
  )
}
