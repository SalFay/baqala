import { Head } from '@inertiajs/react'
import { Typography, Card } from 'antd'

const { Title } = Typography

export default function Users() {
  return (
    <>
      <Head title="Users" />
      <Title level={3}>Users</Title>
      <Card>
        <p>Users management content goes here.</p>
      </Card>
    </>
  )
}
