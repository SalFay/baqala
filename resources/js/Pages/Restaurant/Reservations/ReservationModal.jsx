import { useEffect, useState } from 'react'
import { Form, Input, InputNumber, DatePicker, TimePicker, Row, Col, message } from 'antd'
import { useMutation, useQuery } from '@tanstack/react-query'
import dayjs from 'dayjs'
import CustomModal from '@/Components/CustomModal'
import TableSelector from '@/Pages/POS/Components/TableSelector'
import CustomerSelector from '@/Components/CustomerSelector'
import { createReservation, updateReservation } from '@/Helpers/api/restaurantService'

export default function ReservationModal({
  open,
  onClose,
  onSuccess,
  reservation,
}) {
  const [form] = Form.useForm()
  const isEditing = !!reservation
  const [useExistingCustomer, setUseExistingCustomer] = useState(false)

  useEffect(() => {
    if (open && reservation) {
      form.setFieldsValue({
        table_id: reservation.table?.id,
        customer_id: reservation.customer_id,
        guest_name: reservation.guest_name,
        customer_phone: reservation.customer_phone,
        customer_email: reservation.customer_email,
        reservation_date: reservation.reservation_date ? dayjs(reservation.reservation_date) : null,
        start_time: reservation.start_time ? dayjs(reservation.start_time, 'HH:mm') : null,
        end_time: reservation.end_time ? dayjs(reservation.end_time, 'HH:mm') : null,
        party_size: reservation.party_size,
        special_requests: reservation.special_requests,
        notes: reservation.notes,
      })
      setUseExistingCustomer(!!reservation.customer_id)
    } else if (open) {
      form.resetFields()
      form.setFieldsValue({
        reservation_date: dayjs(),
        party_size: 2,
      })
      setUseExistingCustomer(false)
    }
  }, [open, reservation, form])

  // Create mutation
  const createMutation = useMutation({
    mutationFn: (data) => createReservation(data),
    onSuccess: () => {
      message.success('Reservation created successfully')
      onSuccess()
    },
    onError: (error) => {
      message.error(error.response?.data?.notifications?.[0]?.message || 'Failed to create reservation')
    },
  })

  // Update mutation
  const updateMutation = useMutation({
    mutationFn: (data) => updateReservation(reservation.id, data),
    onSuccess: () => {
      message.success('Reservation updated successfully')
      onSuccess()
    },
    onError: (error) => {
      message.error(error.response?.data?.notifications?.[0]?.message || 'Failed to update reservation')
    },
  })

  const handleSubmit = async () => {
    try {
      const values = await form.validateFields()

      const data = {
        ...values,
        reservation_date: values.reservation_date?.format('YYYY-MM-DD'),
        start_time: values.start_time?.format('HH:mm'),
        end_time: values.end_time?.format('HH:mm'),
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
    onClose()
  }

  const handleCustomerSelect = (customer) => {
    if (customer) {
      form.setFieldsValue({
        customer_id: customer.id,
        guest_name: customer.name,
        customer_phone: customer.phone,
        customer_email: customer.email,
      })
    }
  }

  return (
    <CustomModal
      title={isEditing ? 'Edit Reservation' : 'New Reservation'}
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
              name="table_id"
              label="Table"
              rules={[{ required: true, message: 'Please select a table' }]}
            >
              <TableSelector
                placeholder="Select table"
                statusFilter="available"
              />
            </Form.Item>
          </Col>
          <Col span={12}>
            <Form.Item
              name="party_size"
              label="Party Size"
              rules={[{ required: true, message: 'Enter party size' }]}
            >
              <InputNumber
                min={1}
                max={50}
                style={{ width: '100%' }}
                suffix="guests"
              />
            </Form.Item>
          </Col>
        </Row>

        <Row gutter={16}>
          <Col span={8}>
            <Form.Item
              name="reservation_date"
              label="Date"
              rules={[{ required: true, message: 'Select date' }]}
            >
              <DatePicker
                style={{ width: '100%' }}
                disabledDate={(current) => current && current < dayjs().startOf('day')}
              />
            </Form.Item>
          </Col>
          <Col span={8}>
            <Form.Item
              name="start_time"
              label="Start Time"
              rules={[{ required: true, message: 'Select time' }]}
            >
              <TimePicker
                format="HH:mm"
                minuteStep={15}
                style={{ width: '100%' }}
              />
            </Form.Item>
          </Col>
          <Col span={8}>
            <Form.Item name="end_time" label="End Time">
              <TimePicker
                format="HH:mm"
                minuteStep={15}
                style={{ width: '100%' }}
              />
            </Form.Item>
          </Col>
        </Row>

        <Form.Item name="customer_id" label="Existing Customer">
          <CustomerSelector
            allowClear
            placeholder="Search customer (optional)"
            onChange={(value, option) => {
              if (option?.customer) {
                handleCustomerSelect(option.customer)
              }
            }}
          />
        </Form.Item>

        <Row gutter={16}>
          <Col span={12}>
            <Form.Item
              name="guest_name"
              label="Guest Name"
              rules={[{ required: true, message: 'Enter guest name' }]}
            >
              <Input placeholder="Guest name" />
            </Form.Item>
          </Col>
          <Col span={12}>
            <Form.Item name="customer_phone" label="Phone">
              <Input placeholder="Phone number" />
            </Form.Item>
          </Col>
        </Row>

        <Form.Item name="customer_email" label="Email">
          <Input type="email" placeholder="Email address" />
        </Form.Item>

        <Form.Item name="special_requests" label="Special Requests">
          <Input.TextArea
            rows={2}
            placeholder="Any special requests or dietary requirements"
          />
        </Form.Item>

        <Form.Item name="notes" label="Internal Notes">
          <Input.TextArea
            rows={2}
            placeholder="Notes for staff only"
          />
        </Form.Item>
      </Form>
    </CustomModal>
  )
}
