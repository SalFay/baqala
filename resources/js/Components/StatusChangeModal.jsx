import { useState } from 'react';
import { Modal, Form, Select, Input, message } from 'antd';
import StatusBadge from './StatusBadge';

const { TextArea } = Input;

export default function StatusChangeModal({
    open,
    onClose,
    currentStatus,
    availableStatuses = [],
    onStatusChange,
    loading = false,
}) {
    const [form] = Form.useForm();
    const [submitting, setSubmitting] = useState(false);

    const handleSubmit = async () => {
        try {
            const values = await form.validateFields();
            setSubmitting(true);

            await onStatusChange(values.status, values.reason);

            message.success('Status updated successfully');
            form.resetFields();
            onClose();
        } catch (error) {
            if (error.errorFields) return; // Validation error
            message.error(error.response?.data?.message || 'Failed to update status');
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <Modal
            title="Change Status"
            open={open}
            onCancel={onClose}
            onOk={handleSubmit}
            confirmLoading={submitting || loading}
            okText="Update Status"
        >
            <div style={{ marginBottom: 16 }}>
                <strong>Current Status: </strong>
                <StatusBadge status={currentStatus} />
            </div>

            <Form form={form} layout="vertical">
                <Form.Item
                    name="status"
                    label="New Status"
                    rules={[{ required: true, message: 'Please select a status' }]}
                >
                    <Select placeholder="Select new status">
                        {availableStatuses.map((status) => (
                            <Select.Option key={status.code} value={status.code}>
                                <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                                    <span
                                        style={{
                                            width: 12,
                                            height: 12,
                                            borderRadius: '50%',
                                            backgroundColor: status.color,
                                        }}
                                    />
                                    {status.name}
                                </div>
                            </Select.Option>
                        ))}
                    </Select>
                </Form.Item>

                <Form.Item
                    name="reason"
                    label="Reason (Optional)"
                >
                    <TextArea
                        rows={3}
                        placeholder="Enter reason for status change..."
                    />
                </Form.Item>
            </Form>
        </Modal>
    );
}
