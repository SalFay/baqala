import { Typography, Space } from 'antd';

const { Title } = Typography;

export default function PageHeader({ title, children, extra }) {
  return (
    <div
      style={{
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 24,
      }}
    >
      <Title level={4} style={{ margin: 0 }}>
        {title}
      </Title>
      {extra || children ? <Space>{extra || children}</Space> : null}
    </div>
  );
}
