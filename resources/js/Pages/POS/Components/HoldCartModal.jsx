import { useState } from 'react'
import { Modal, Input, Typography } from 'antd'

const { Text } = Typography

export default function HoldCartModal({
  open,
  onClose,
  onHold,
  loading,
}) {
  const [name, setName] = useState('')

  const handleOk = () => {
    if (name.trim()) {
      onHold(name.trim())
      setName('')
      onClose()
    }
  }

  const handleCancel = () => {
    setName('')
    onClose()
  }

  return (
    <Modal
      title="Hold Cart"
      open={open}
      onOk={handleOk}
      onCancel={handleCancel}
      okText="Hold Cart"
      okButtonProps={{ loading, disabled: !name.trim() }}
      destroyOnClose
    >
      <Text style={{ display: 'block', marginBottom: 8 }}>
        Enter a name for this cart so you can find it later
      </Text>
      <Input
        placeholder="e.g., Customer name or table number"
        value={name}
        onChange={(e) => setName(e.target.value)}
        onPressEnter={handleOk}
        autoFocus
      />
    </Modal>
  )
}
