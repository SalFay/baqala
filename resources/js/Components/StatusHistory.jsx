import { Timeline, Card, Tag, Typography, Empty } from 'antd';
import { ClockCircleOutlined, UserOutlined, RobotOutlined } from '@ant-design/icons';
import { formatDateTime } from '@/Helpers/formatters';

const { Text } = Typography;

export default function StatusHistory({ histories = [], loading = false }) {
    if (!histories.length && !loading) {
        return <Empty description="No status history" />;
    }

    return (
        <Card title="Status History" loading={loading}>
            <Timeline
                items={histories.map((history) => ({
                    color: history.status?.color || 'gray',
                    dot: history.is_system_change ? <RobotOutlined /> : <ClockCircleOutlined />,
                    children: (
                        <div>
                            <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 4 }}>
                                {history.previous_status && (
                                    <>
                                        <Tag color={history.previous_status.color}>
                                            {history.previous_status.name}
                                        </Tag>
                                        <span>→</span>
                                    </>
                                )}
                                <Tag color={history.status?.color}>
                                    {history.status?.name}
                                </Tag>
                            </div>
                            {history.reason && (
                                <Text type="secondary" style={{ display: 'block', marginBottom: 4 }}>
                                    {history.reason}
                                </Text>
                            )}
                            <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                                {history.user && (
                                    <Text type="secondary">
                                        <UserOutlined /> {history.user.first_name} {history.user.last_name}
                                    </Text>
                                )}
                                <Text type="secondary">
                                    {formatDateTime(history.created_at)}
                                </Text>
                            </div>
                        </div>
                    ),
                }))}
            />
        </Card>
    );
}
