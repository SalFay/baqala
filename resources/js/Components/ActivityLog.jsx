import { Card, Timeline, Typography, Tag, Empty, Collapse } from 'antd';
import { formatDateTime } from '@/Helpers/formatters';

const { Text, Paragraph } = Typography;

const eventColors = {
    created: 'green',
    updated: 'blue',
    deleted: 'red',
};

const getEventLabel = (event) => {
    const labels = {
        created: 'Created',
        updated: 'Updated',
        deleted: 'Deleted',
    };
    return labels[event] || event;
};

const renderChanges = (changes) => {
    if (!changes?.old || !changes?.new) return null;

    const oldKeys = Object.keys(changes.old);
    const newKeys = Object.keys(changes.new);
    const allKeys = [...new Set([...oldKeys, ...newKeys])];

    if (allKeys.length === 0) return null;

    return (
        <Collapse size="small" ghost>
            <Collapse.Panel header="View Changes" key="1">
                <table style={{ width: '100%', fontSize: 12 }}>
                    <thead>
                        <tr>
                            <th style={{ textAlign: 'left', padding: 4 }}>Field</th>
                            <th style={{ textAlign: 'left', padding: 4 }}>Old Value</th>
                            <th style={{ textAlign: 'left', padding: 4 }}>New Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        {allKeys.map((key) => (
                            <tr key={key}>
                                <td style={{ padding: 4 }}>{key}</td>
                                <td style={{ padding: 4 }}>
                                    <Text type="secondary" delete>
                                        {JSON.stringify(changes.old[key])}
                                    </Text>
                                </td>
                                <td style={{ padding: 4 }}>
                                    <Text type="success">
                                        {JSON.stringify(changes.new[key])}
                                    </Text>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </Collapse.Panel>
        </Collapse>
    );
};

export default function ActivityLog({ activities = [], loading = false }) {
    if (!activities.length && !loading) {
        return <Empty description="No activity log" />;
    }

    return (
        <Card title="Activity Log" loading={loading}>
            <Timeline
                items={activities.map((activity) => ({
                    color: eventColors[activity.event] || 'gray',
                    children: (
                        <div>
                            <div style={{ marginBottom: 4 }}>
                                <Tag color={eventColors[activity.event] || 'default'}>
                                    {getEventLabel(activity.event)}
                                </Tag>
                                <Text>{activity.description}</Text>
                            </div>

                            {renderChanges(activity.changes)}

                            <div style={{ marginTop: 4 }}>
                                {activity.causer && (
                                    <Text type="secondary" style={{ marginRight: 8 }}>
                                        by {activity.causer.first_name} {activity.causer.last_name}
                                    </Text>
                                )}
                                <Text type="secondary">
                                    {formatDateTime(activity.created_at)}
                                </Text>
                            </div>
                        </div>
                    ),
                }))}
            />
        </Card>
    );
}
