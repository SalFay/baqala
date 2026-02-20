import { Modal } from 'antd';
import { ExclamationCircleOutlined } from '@ant-design/icons';

const { confirm } = Modal;

export function showConfirm({
  title,
  content,
  onOk,
  onCancel,
  okText = 'Yes',
  cancelText = 'No',
  okType = 'primary',
  danger = false,
}) {
  return confirm({
    title,
    icon: <ExclamationCircleOutlined />,
    content,
    okText,
    cancelText,
    okType: danger ? 'danger' : okType,
    onOk,
    onCancel,
  });
}

export function showDeleteConfirm({ title = 'Delete', itemName, onOk, onCancel }) {
  return showConfirm({
    title,
    content: `Are you sure you want to delete ${itemName || 'this item'}?`,
    okText: 'Delete',
    danger: true,
    onOk,
    onCancel,
  });
}
