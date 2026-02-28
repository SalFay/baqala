import { useRef, useState } from 'react'
import { Head } from '@inertiajs/react'
import { Button, Dropdown, Modal, message, Form, Input, InputNumber, Select, DatePicker, Row, Col } from 'antd'
import { EditOutlined, DeleteOutlined, MoreOutlined, PlusOutlined } from '@ant-design/icons'
import { useMutation } from '@tanstack/react-query'
import axios from 'axios'
import dayjs from 'dayjs'
import DataGridTable from '@/Components/DataGridTable/DataGridTable'
import StatusBadge from '@/Components/StatusBadge'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import { formatCurrency, formatDate, getCurrency } from '@/Helpers/formatters'

const { Option } = Select
const { TextArea } = Input

export default function Expenses() {
  const gridRef = useRef()
  const [modalOpen, setModalOpen] = useState(false)
  const [editingExpense, setEditingExpense] = useState(null)
  const [form] = Form.useForm()

  const createMutation = useMutation({
    mutationFn: (data) => axios.post('/expenses', data),
    onSuccess: () => {
      message.success('Expense created successfully')
      handleCloseModal()
      gridRef.current?.reloadData()
    },
    onError: (error) => message.error(error.response?.data?.message || 'Failed to create expense'),
  })

  const updateMutation = useMutation({
    mutationFn: ({ id, data }) => axios.put(`/expenses/${id}`, data),
    onSuccess: () => {
      message.success('Expense updated successfully')
      handleCloseModal()
      gridRef.current?.reloadData()
    },
    onError: (error) => message.error(error.response?.data?.message || 'Failed to update expense'),
  })

  const deleteMutation = useMutation({
    mutationFn: (id) => axios.delete(`/expenses/${id}`),
    onSuccess: () => {
      message.success('Expense deleted successfully')
      gridRef.current?.reloadData()
    },
    onError: (error) => message.error(error.response?.data?.message || 'Failed to delete expense'),
  })

  const handleAdd = () => {
    setEditingExpense(null)
    form.resetFields()
    form.setFieldsValue({ expense_date: dayjs(), status: 'pending' })
    setModalOpen(true)
  }

  const handleEdit = (expense) => {
    setEditingExpense(expense)
    form.setFieldsValue({
      ...expense,
      expense_date: expense.expense_date ? dayjs(expense.expense_date) : null,
    })
    setModalOpen(true)
  }

  const handleDelete = (expense) => {
    Modal.confirm({
      title: 'Delete Expense',
      content: 'Are you sure you want to delete this expense?',
      okText: 'Delete',
      okType: 'danger',
      onOk: () => deleteMutation.mutate(expense.id),
    })
  }

  const handleSubmit = async () => {
    try {
      const values = await form.validateFields()
      const data = {
        ...values,
        expense_date: values.expense_date?.format('YYYY-MM-DD'),
      }
      if (editingExpense) {
        updateMutation.mutate({ id: editingExpense.id, data })
      } else {
        createMutation.mutate(data)
      }
    } catch (error) {
      console.error('Validation failed:', error)
    }
  }

  const handleCloseModal = () => {
    setModalOpen(false)
    setEditingExpense(null)
    form.resetFields()
  }

  const columns = [
    { field: 'description', headerName: 'Description', flex: 2, minWidth: 200 },
    {
      field: 'amount',
      headerName: 'Amount',
      flex: 1,
      minWidth: 100,
      cellRenderer: ({ value }) => formatCurrency(value),
    },
    {
      field: 'category',
      headerName: 'Category',
      flex: 1,
      minWidth: 120,
      cellRenderer: ({ data }) => data.category?.name || '-',
    },
    {
      field: 'expense_date',
      headerName: 'Date',
      flex: 1,
      minWidth: 120,
      cellRenderer: ({ value }) => formatDate(value),
    },
    {
      field: 'status',
      headerName: 'Status',
      flex: 1,
      minWidth: 100,
      cellRenderer: ({ value }) => <StatusBadge status={value} />,
    },
  ]

  const actionsColumn = {
    field: 'actions',
    headerName: 'Actions',
    width: 100,
    sortable: false,
    cellRenderer: ({ data }) => (
      <Dropdown
        trigger={['click']}
        menu={{
          items: [
            { key: 'edit', label: 'Edit', icon: <EditOutlined />, onClick: () => handleEdit(data) },
            { type: 'divider' },
            { key: 'delete', label: 'Delete', icon: <DeleteOutlined />, danger: true, onClick: () => handleDelete(data) },
          ],
        }}
      >
        <Button type="text" icon={<MoreOutlined />} />
      </Dropdown>
    ),
  }

  return (
    <>
      <Head title="Expenses" />

      <GlobalPageHeader
        title="Expenses"
        parentPageTitle="Dashboard"
        parentPageRoute="dashboard"
        actionButtons={[
          {
            title: 'Add Expense',
            icon: <PlusOutlined />,
            onClick: handleAdd,
            type: 'primary',
          },
        ]}
      />

      <DataGridTable
        gridRef={gridRef}
        routeName="pos.expenses.listing"
        columns={[...columns, actionsColumn]}
        instanceId="expenses"
        pageSize={20}
        height="calc(100vh - 260px)"
      />

      <Modal
        title={editingExpense ? 'Edit Expense' : 'Add Expense'}
        open={modalOpen}
        onOk={handleSubmit}
        onCancel={handleCloseModal}
        okText={editingExpense ? 'Update' : 'Create'}
        confirmLoading={createMutation.isPending || updateMutation.isPending}
        width={600}
        destroyOnClose
      >
        <Form form={form} layout="vertical">
          <Form.Item name="description" label="Description" rules={[{ required: true, message: 'Please enter description' }]}>
            <TextArea rows={2} placeholder="Enter expense description" />
          </Form.Item>
          <Row gutter={16}>
            <Col span={12}>
              <Form.Item name="amount" label={`Amount (${getCurrency()})`} rules={[{ required: true, message: 'Please enter amount' }]}>
                <InputNumber min={0} precision={2} style={{ width: '100%' }} />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item name="expense_date" label="Date" rules={[{ required: true, message: 'Please select date' }]}>
                <DatePicker style={{ width: '100%' }} />
              </Form.Item>
            </Col>
          </Row>
          <Form.Item name="status" label="Status">
            <Select>
              <Option value="pending">Pending</Option>
              <Option value="approved">Approved</Option>
              <Option value="paid">Paid</Option>
              <Option value="rejected">Rejected</Option>
            </Select>
          </Form.Item>
          <Form.Item name="notes" label="Notes">
            <TextArea rows={2} placeholder="Additional notes (optional)" />
          </Form.Item>
        </Form>
      </Modal>
    </>
  )
}
