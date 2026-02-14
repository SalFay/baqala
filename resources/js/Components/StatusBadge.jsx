import { Tag } from 'antd';

const defaultColors = {
    // Order statuses
    pending: 'orange',
    processing: 'blue',
    completed: 'green',
    cancelled: 'red',
    refunded: 'purple',

    // Purchase Order statuses
    draft: 'default',
    ordered: 'blue',
    partial: 'cyan',
    received: 'green',

    // Stock Transfer statuses
    in_transit: 'blue',
    shipped: 'geekblue',

    // Payment statuses
    paid: 'green',
    unpaid: 'red',

    // Generic
    active: 'green',
    inactive: 'default',
};

const defaultLabels = {
    pending: 'Pending',
    processing: 'Processing',
    completed: 'Completed',
    cancelled: 'Cancelled',
    refunded: 'Refunded',
    draft: 'Draft',
    ordered: 'Ordered',
    partial: 'Partial',
    received: 'Received',
    in_transit: 'In Transit',
    shipped: 'Shipped',
    paid: 'Paid',
    unpaid: 'Unpaid',
    active: 'Active',
    inactive: 'Inactive',
};

export default function StatusBadge({ status, size = 'default' }) {
    if (!status) return null;

    const style = size === 'small' ? { fontSize: 12 } : {};

    // Handle both object status and string status
    if (typeof status === 'object') {
        return (
            <Tag color={status.color} style={style}>
                {status.name}
            </Tag>
        );
    }

    // String status - use default colors
    const statusKey = status.toLowerCase().replace(/-/g, '_');
    const color = defaultColors[statusKey] || 'default';
    const label = defaultLabels[statusKey] || status.charAt(0).toUpperCase() + status.slice(1);

    return (
        <Tag color={color} style={style}>
            {label}
        </Tag>
    );
}
