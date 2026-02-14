import { useState, useEffect } from 'react';
import { Modal, Input, List, Typography, Avatar, Tag, Empty, Spin, Button } from 'antd';
import { SearchOutlined, UserOutlined, PlusOutlined } from '@ant-design/icons';
import axios from 'axios';

const { Text } = Typography;

export default function CustomerSearchModal({ open, onClose, onSelect }) {
    const [searchTerm, setSearchTerm] = useState('');
    const [customers, setCustomers] = useState([]);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        if (open && searchTerm.length >= 2) {
            searchCustomers();
        }
    }, [searchTerm, open]);

    useEffect(() => {
        if (open) {
            setSearchTerm('');
            setCustomers([]);
        }
    }, [open]);

    const searchCustomers = async () => {
        setLoading(true);
        try {
            const response = await axios.get('/customers/search', {
                params: { q: searchTerm },
            });
            setCustomers(response.data.data || response.data || []);
        } catch (error) {
            console.error('Failed to search customers:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleSelect = (customer) => {
        onSelect(customer.id);
    };

    const handleWalkIn = () => {
        onSelect(null);
    };

    return (
        <Modal
            title="Select Customer"
            open={open}
            onCancel={onClose}
            footer={null}
            width={500}
        >
            <Input
                placeholder="Search by name, phone, or email..."
                prefix={<SearchOutlined />}
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                style={{ marginBottom: 16 }}
                autoFocus
                allowClear
            />

            <Button
                icon={<UserOutlined />}
                block
                style={{ marginBottom: 16 }}
                onClick={handleWalkIn}
            >
                Continue as Walk-in Customer
            </Button>

            {loading ? (
                <div style={{ textAlign: 'center', padding: 40 }}>
                    <Spin />
                </div>
            ) : customers.length === 0 ? (
                <Empty
                    description={searchTerm.length < 2 ? 'Enter at least 2 characters to search' : 'No customers found'}
                    image={Empty.PRESENTED_IMAGE_SIMPLE}
                />
            ) : (
                <List
                    dataSource={customers}
                    renderItem={(customer) => (
                        <List.Item
                            style={{ cursor: 'pointer', padding: '12px 8px' }}
                            onClick={() => handleSelect(customer)}
                            className="customer-list-item"
                        >
                            <List.Item.Meta
                                avatar={<Avatar icon={<UserOutlined />} />}
                                title={
                                    <span>
                                        {customer.full_name || `${customer.first_name} ${customer.last_name}`}
                                        {customer.loyalty_points > 0 && (
                                            <Tag color="gold" style={{ marginLeft: 8 }}>
                                                {customer.loyalty_points} pts
                                            </Tag>
                                        )}
                                    </span>
                                }
                                description={
                                    <div>
                                        {customer.phone && <Text type="secondary">{customer.phone}</Text>}
                                        {customer.phone && customer.email && ' • '}
                                        {customer.email && <Text type="secondary">{customer.email}</Text>}
                                    </div>
                                }
                            />
                        </List.Item>
                    )}
                    style={{ maxHeight: 300, overflow: 'auto' }}
                />
            )}

            <style>{`
                .customer-list-item:hover {
                    background: #f5f5f5;
                    border-radius: 8px;
                }
            `}</style>
        </Modal>
    );
}
