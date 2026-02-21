import { useEffect, useState } from 'react'
import { Button, Form, Input, Select, message } from 'antd'
import CustomModal from '@/Components/CustomModal'
import { createUser, updateUser } from '@/Helpers/api/userService'

export default function UserModal({ visible, onCancel, record, onUpdate, roles }) {
  const [form] = Form.useForm()
  const [loading, setLoading] = useState(false)

  const isEdit = !!record

  useEffect(() => {
    if (record) {
      form.setFieldsValue({
        first_name: record.first_name,
        last_name: record.last_name,
        email: record.email,
        phone: record.phone,
        role_id: record.role_id,
        status: record.status || 'active',
      })
    } else {
      form.resetFields()
      form.setFieldsValue({ status: 'active' })
    }
  }, [record, form])

  const handleSubmit = async () => {
    try {
      const values = await form.validateFields()
      setLoading(true)

      if (record) {
        await updateUser(record.id, values)
        message.success('User updated successfully')
      } else {
        await createUser(values)
        message.success('User created successfully')
      }

      onUpdate()
      onCancel()
    } catch (error) {
      if (error.response?.data?.errors) {
        const errors = error.response.data.errors
        const fields = Object.keys(errors).map((key) => ({
          name: key,
          errors: errors[key],
        }))
        form.setFields(fields)
      } else if (error.response?.data?.message) {
        message.error(error.response.data.message)
      }
    } finally {
      setLoading(false)
    }
  }

  return (
    <CustomModal
      title={isEdit ? 'Edit User' : 'Create User'}
      open={visible}
      onCancel={onCancel}
      width={600}
      showSave
      saveText={isEdit ? 'Update' : 'Create'}
      loading={loading}
      onSave={handleSubmit}
      extraFooter={<Button onClick={onCancel}>Cancel</Button>}
    >
      <Form form={form} layout="vertical">
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
          <Form.Item
            label="First Name"
            name="first_name"
            rules={[{ required: true, message: 'Required' }]}
          >
            <Input placeholder="First name" />
          </Form.Item>

          <Form.Item
            label="Last Name"
            name="last_name"
            rules={[{ required: true, message: 'Required' }]}
          >
            <Input placeholder="Last name" />
          </Form.Item>
        </div>

        <Form.Item
          label="Email"
          name="email"
          rules={[
            { required: true, message: 'Required' },
            { type: 'email', message: 'Invalid email' },
          ]}
        >
          <Input placeholder="Email address" />
        </Form.Item>

        <Form.Item label="Phone" name="phone">
          <Input placeholder="Phone number" />
        </Form.Item>

        <Form.Item
          label="Role"
          name="role_id"
          rules={[{ required: true, message: 'Required' }]}
        >
          <Select placeholder="Select role">
            {(roles || []).map((role) => (
              <Select.Option key={role.id} value={role.id}>
                {role.name}
              </Select.Option>
            ))}
          </Select>
        </Form.Item>

        <Form.Item label="Status" name="status">
          <Select>
            <Select.Option value="active">Active</Select.Option>
            <Select.Option value="inactive">Inactive</Select.Option>
          </Select>
        </Form.Item>

        {!isEdit && (
          <>
            <Form.Item
              label="Password"
              name="password"
              rules={[
                { required: true, message: 'Required' },
                { min: 8, message: 'Min 8 characters' },
              ]}
            >
              <Input.Password placeholder="Password" />
            </Form.Item>

            <Form.Item
              label="Confirm Password"
              name="password_confirmation"
              dependencies={['password']}
              rules={[
                { required: true, message: 'Required' },
                ({ getFieldValue }) => ({
                  validator(_, value) {
                    if (!value || getFieldValue('password') === value) {
                      return Promise.resolve()
                    }
                    return Promise.reject('Passwords do not match')
                  },
                }),
              ]}
            >
              <Input.Password placeholder="Confirm password" />
            </Form.Item>
          </>
        )}
      </Form>
    </CustomModal>
  )
}
