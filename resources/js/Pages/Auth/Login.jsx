import { Head, useForm } from '@inertiajs/react'
import { Card, Form, Input, Button, Typography, Alert } from 'antd'
import { UserOutlined, LockOutlined } from '@ant-design/icons'

const { Title, Text } = Typography

// No layout for login page
Login.layout = (page) => page

export default function Login() {
  const { data, setData, post, processing, errors } = useForm({
    email: '',
    password: '',
    remember: false,
  })

  const handleSubmit = () => {
    post(route('login'))
  }

  return (
    <>
      <Head title="Login" />

      <div
        style={{
          minHeight: '100vh',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
          padding: 24,
        }}
      >
        <Card style={{ width: 400, boxShadow: '0 4px 12px rgba(0,0,0,0.15)' }}>
          <div style={{ textAlign: 'center', marginBottom: 24 }}>
            <Title level={2} style={{ marginBottom: 8 }}>Baqala POS</Title>
            <Text type="secondary">Sign in to your account</Text>
          </div>

          {errors.email && (
            <Alert
              message={errors.email}
              type="error"
              showIcon
              style={{ marginBottom: 16 }}
            />
          )}

          <Form layout="vertical" onFinish={handleSubmit}>
            <Form.Item
              label="Email"
              validateStatus={errors.email ? 'error' : ''}
            >
              <Input
                prefix={<UserOutlined />}
                placeholder="Enter your email"
                value={data.email}
                onChange={(e) => setData('email', e.target.value)}
                size="large"
              />
            </Form.Item>

            <Form.Item
              label="Password"
              validateStatus={errors.password ? 'error' : ''}
              help={errors.password}
            >
              <Input.Password
                prefix={<LockOutlined />}
                placeholder="Enter your password"
                value={data.password}
                onChange={(e) => setData('password', e.target.value)}
                size="large"
              />
            </Form.Item>

            <Form.Item>
              <Button
                type="primary"
                htmlType="submit"
                loading={processing}
                block
                size="large"
              >
                Sign In
              </Button>
            </Form.Item>
          </Form>
        </Card>
      </div>
    </>
  )
}
