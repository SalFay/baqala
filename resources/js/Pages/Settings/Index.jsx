import { Head } from '@inertiajs/react'
import { Typography, Card } from 'antd'

const { Title } = Typography

export default function Settings() {
  return (
    <>
      <Head title="Settings" />
      <Title level={3}>Settings</Title>
      <Card>
        <p>Settings content goes here.</p>
      </Card>
    </>
  )
}
