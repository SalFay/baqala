import { useEffect, useState } from 'react'
import { Button, Checkbox, ColorPicker, Divider, Form, Input, Tabs, Typography, message } from 'antd'
import CustomModal from '@/Components/CustomModal'
import { createRole, updateRole } from '@/Helpers/api/roleService'

const { Text } = Typography

export default function RoleModal({
  visible,
  onCancel,
  record,
  onUpdate,
  permissions,
  permissionType,
}) {
  const [form] = Form.useForm()
  const [loading, setLoading] = useState(false)
  const [checkedPermissions, setCheckedPermissions] = useState({})

  const isViewMode = permissionType === 'view'

  useEffect(() => {
    if (record) {
      form.setFieldsValue({
        name: record.name,
        color: record.color || '#cdceca',
      })
      // Convert permissions array to object
      const permsObj = {}
      ;(record.permissions || []).forEach((p) => (permsObj[p] = true))
      setCheckedPermissions(permsObj)
    } else {
      form.resetFields()
      setCheckedPermissions({})
    }
  }, [record, form])

  const handlePermissionChange = (key, checked) => {
    setCheckedPermissions((prev) => {
      if (checked) {
        return { ...prev, [key]: true }
      } else {
        const updated = { ...prev }
        delete updated[key]
        return updated
      }
    })
  }

  const handleCheckAll = (groupKey, checked) => {
    const group = permissions[groupKey]
    if (!group) return

    setCheckedPermissions((prev) => {
      const updated = { ...prev }
      Object.keys(group.permissions || {}).forEach((key) => {
        if (checked) {
          updated[key] = true
        } else {
          delete updated[key]
        }
      })
      return updated
    })
  }

  const handleSubmit = async () => {
    try {
      const values = await form.validateFields()
      setLoading(true)

      const data = {
        ...values,
        color: typeof values.color === 'string' ? values.color : values.color?.toHexString?.() || '#cdceca',
        permissions: Object.keys(checkedPermissions),
      }

      if (record) {
        await updateRole(record.id, data)
        message.success('Role updated successfully')
      } else {
        await createRole(data)
        message.success('Role created successfully')
      }

      onUpdate()
      onCancel()
    } catch (error) {
      if (error.response?.data?.message) {
        message.error(error.response.data.message)
      }
    } finally {
      setLoading(false)
    }
  }

  const renderPermissionGroup = (groupKey) => {
    const group = permissions[groupKey]
    if (!group) return null

    const groupPerms = Object.keys(group.permissions || {})
    const allChecked = groupPerms.every((k) => checkedPermissions[k])

    return (
      <div style={{ padding: '12px 0' }}>
        <div
          style={{
            display: 'flex',
            justifyContent: 'space-between',
            alignItems: 'center',
            marginBottom: 12,
            padding: '8px 12px',
            background: '#f5f5f5',
            borderRadius: 4,
          }}
        >
          <Text strong>Allow All</Text>
          <Checkbox
            checked={allChecked}
            disabled={isViewMode}
            onChange={(e) => handleCheckAll(groupKey, e.target.checked)}
          />
        </div>

        <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
          {Object.entries(group.permissions || {}).map(([key, perm]) => (
            <div
              key={key}
              style={{
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center',
                padding: '8px 12px',
                border: '1px solid #f0f0f0',
                borderRadius: 4,
                cursor: isViewMode ? 'default' : 'pointer',
              }}
              onClick={() => !isViewMode && handlePermissionChange(key, !checkedPermissions[key])}
            >
              <div>
                <div style={{ fontWeight: 500 }}>{perm.title}</div>
                <div style={{ fontSize: 12, color: '#888' }}>{perm.description}</div>
              </div>
              <Checkbox
                checked={!!checkedPermissions[key]}
                disabled={isViewMode}
                onClick={(e) => e.stopPropagation()}
                onChange={(e) => handlePermissionChange(key, e.target.checked)}
              />
            </div>
          ))}
        </div>
      </div>
    )
  }

  const tabItems = Object.keys(permissions || {}).map((key) => ({
    key,
    label: permissions[key].title,
    children: renderPermissionGroup(key),
  }))

  return (
    <CustomModal
      title={record ? (isViewMode ? 'View Role' : 'Edit Role') : 'Create Role'}
      open={visible}
      onCancel={onCancel}
      width={800}
      showSave={!isViewMode}
      saveText={record ? 'Update' : 'Create'}
      loading={loading}
      onSave={handleSubmit}
      extraFooter={!isViewMode && <Button onClick={onCancel}>Cancel</Button>}
    >
      <div style={{ maxHeight: 'calc(100vh - 280px)', overflowY: 'auto' }}>
        <Form form={form} layout="vertical" disabled={isViewMode}>
          <Form.Item
            label="Name"
            name="name"
            rules={[{ required: true, message: 'Please enter role name' }]}
          >
            <Input placeholder="Enter role name" />
          </Form.Item>

          <Form.Item label="Color" name="color">
            <ColorPicker
              showText
              format="hex"
              presets={[
                {
                  label: 'Colors',
                  colors: [
                    '#d19999', '#db8e89', '#93c5fd', '#6b8de3', '#7dd3fc',
                    '#38bdf8', '#60a5fa', '#80C1A7', '#4ade80', '#86efac',
                    '#ed9314', '#efb700', '#F7DB80', '#5eead4', '#2dd4bf',
                    '#cdceca',
                  ],
                },
              ]}
            />
          </Form.Item>
        </Form>

        <Divider orientation="left">Permissions</Divider>

        {tabItems.length > 0 ? (
          <Tabs type="card" tabPosition="left" items={tabItems} style={{ minHeight: 300 }} />
        ) : (
          <Text type="secondary">No permissions configured</Text>
        )}
      </div>
    </CustomModal>
  )
}
