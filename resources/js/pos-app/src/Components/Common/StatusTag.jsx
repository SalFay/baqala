import { Tag } from 'antd';

// Default status color mapping
const defaultStatusColors = {
  // Common statuses
  draft: 'default',
  pending: 'warning',
  approved: 'success',
  rejected: 'error',
  completed: 'success',
  cancelled: 'error',
  paid: 'blue',
  // Order statuses
  processing: 'processing',
  shipped: 'cyan',
  delivered: 'green',
  refunded: 'orange',
  // Stock take statuses
  in_progress: 'processing',
  // Return statuses
  received: 'cyan',
  // Active/Inactive
  active: 'success',
  inactive: 'default',
};

export default function StatusTag({ status, label, colors = {}, ...props }) {
  const colorMap = { ...defaultStatusColors, ...colors };
  const color = colorMap[status?.toLowerCase()] || 'default';
  const displayLabel = label || status?.replace(/_/g, ' ').toUpperCase();

  return (
    <Tag color={color} {...props}>
      {displayLabel}
    </Tag>
  );
}
