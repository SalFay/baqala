import { useEffect, useState } from 'react'
import { Form, Input, InputNumber, DatePicker, Select, Row, Col, message } from 'antd'
import { useMutation } from '@tanstack/react-query'
import dayjs from 'dayjs'
import CustomModal from '@/Components/CustomModal'
import { createCheque, updateCheque } from '@/Helpers/api/chequeService'
import { getCurrency } from '@/Helpers/formatters'
import axios from 'axios'

export default function ChequeModal({
  open,
  onClose,
  onSuccess,
  cheque,
}) {
  const [form] = Form.useForm()
  const [customers, setCustomers] = useState([])
  const [searchLoading, setSearchLoading] = useState(false)
  const isEditing = !!cheque

  useEffect(() => {
    if (open && cheque) {
      form.setFieldsValue({
        cheque_number: cheque.cheque_number,
        bank_name: cheque.bank_name,
        amount: cheque.amount,
        cheque_date: cheque.cheque_date ? dayjs(cheque.cheque_date) : null,
        due_date: cheque.due_date ? dayjs(cheque.due_date) : null,
        customer_id: cheque.customer_id,
        notes: cheque.notes,
      })
      if (cheque.customer) {
        setCustomers([cheque.customer])
      }
    } else if (open) {
      form.resetFields()
    }
  }, [open, cheque, form])

  // Create mutation
  const createMutation = useMutation({
    mutationFn: (data) => createCheque(data),
    onSuccess: () => {
      message.success('Cheque created successfully')
      onSuccess()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to create cheque')
    },
  })

  // Update mutation
  const updateMutation = useMutation({
    mutationFn: (data) => updateCheque(cheque.id, data),
    onSuccess: () => {
      message.success('Cheque updated successfully')
      onSuccess()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to update cheque')
    },
  })

  // Search customers
  const handleCustomerSearch = async (value) => {
    if (!value || value.length < 2) return
    setSearchLoading(true)
    try {
      const response = await axios.get('/pos/customers/search', {
        params: { q: value },
      })
      setCustomers(response.data.data || [])
    } catch (error) {
      console.error('Customer search failed:', error)
    } finally {
      setSearchLoading(false)
    }
  }

  const handleSubmit = async () => {
    try {
      const values = await form.validateFields()

      const data = {
        ...values,
        cheque_date: values.cheque_date?.format('YYYY-MM-DD'),
        due_date: values.due_date?.format('YYYY-MM-DD'),
      }

      if (isEditing) {
        updateMutation.mutate(data)
      } else {
        createMutation.mutate(data)
      }
    } catch (error) {
      console.error('Validation failed:', error)
    }
  }

  const handleClose = () => {
    form.resetFields()
    setCustomers([])
    onClose()
  }

  return (
    <CustomModal
      title={isEditing ? 'Edit Cheque' : 'Add Cheque'}
      open={open}
      onCancel={handleClose}
      width={600}
      showSave
      saveText={isEditing ? 'Update' : 'Create'}
      loading={createMutation.isPending || updateMutation.isPending}
      onSave={handleSubmit}
    >
      <Form form={form} layout="vertical">
        <Row gutter={16}>
          <Col span={12}>
            <Form.Item
              name="cheque_number"
              label="Cheque Number"
              rules={[{ required: true, message: 'Please enter cheque number' }]}
            >
              <Input placeholder="Enter cheque number" />
            </Form.Item>
          </Col>
          <Col span={12}>
            <Form.Item
              name="bank_name"
              label="Bank"
              rules={[{ required: true, message: 'Please enter bank name' }]}
            >
              <Input placeholder="Enter bank name" />
            </Form.Item>
          </Col>
        </Row>

        <Row gutter={16}>
          <Col span={12}>
            <Form.Item
              name="amount"
              label={`Amount (${getCurrency()})`}
              rules={[
                { required: true, message: 'Please enter amount' },
                { type: 'number', min: 0.01, message: 'Amount must be greater than 0' },
              ]}
            >
              <InputNumber
                placeholder="0.00"
                min={0.01}
                precision={2}
                style={{ width: '100%' }}
              />
            </Form.Item>
          </Col>
          <Col span={12}>
            <Form.Item
              name="customer_id"
              label="Customer"
              rules={[{ required: true, message: 'Please select customer' }]}
            >
              <Select
                showSearch
                placeholder="Search customer..."
                filterOption={false}
                onSearch={handleCustomerSearch}
                loading={searchLoading}
                options={customers.map(c => ({
                  value: c.id,
                  label: c.full_name,
                }))}
              />
            </Form.Item>
          </Col>
        </Row>

        <Row gutter={16}>
          <Col span={12}>
            <Form.Item
              name="cheque_date"
              label="Cheque Date"
              rules={[{ required: true, message: 'Please select cheque date' }]}
            >
              <DatePicker style={{ width: '100%' }} />
            </Form.Item>
          </Col>
          <Col span={12}>
            <Form.Item
              name="due_date"
              label="Due Date"
              rules={[{ required: true, message: 'Please select due date' }]}
            >
              <DatePicker style={{ width: '100%' }} />
            </Form.Item>
          </Col>
        </Row>

        <Form.Item name="notes" label="Notes">
          <Input.TextArea rows={3} placeholder="Optional notes..." />
        </Form.Item>
      </Form>
    </CustomModal>
  )
}
