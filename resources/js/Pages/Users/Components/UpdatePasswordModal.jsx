import { useState } from 'react'
import { Button, Form, Input, message } from 'antd'
import CustomModal from '@/Components/CustomModal'
import { updateUserPassword } from '@/Helpers/api/userService'

export default function UpdatePasswordModal({ visible, onCancel, record }) {
  const [form] = Form.useForm()
  const [loading, setLoading] = useState(false)

  const handleSubmit = async () => {
    try {
      const values = await form.validateFields()
      setLoading(true)

      await updateUserPassword(record.id, values)
      message.success('Password updated successfully')
      form.resetFields()
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
      title={`Update Password - ${record?.full_name || record?.email}`}
      open={visible}
      onCancel={onCancel}
      showSave
      saveText="Update Password"
      loading={loading}
      onSave={handleSubmit}
      extraFooter={<Button onClick={onCancel}>Cancel</Button>}
    >
      <Form form={form} layout="vertical">
        <Form.Item
          label="New Password"
          name="password"
          rules={[
            { required: true, message: 'Required' },
            { min: 8, message: 'Min 8 characters' },
          ]}
        >
          <Input.Password placeholder="New password" />
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
          <Input.Password placeholder="Confirm new password" />
        </Form.Item>
      </Form>
    </CustomModal>
  )
}
