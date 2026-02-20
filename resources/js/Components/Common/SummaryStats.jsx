import { Row, Col, Card, Statistic } from 'antd';

export default function SummaryStats({ stats = [], gutter = [16, 16], colSpan = { xs: 24, sm: 12, md: 6 } }) {
  if (!stats.length) return null;

  return (
    <Row gutter={gutter} style={{ marginBottom: 24 }}>
      {stats.map((stat, index) => (
        <Col key={stat.key || index} {...colSpan}>
          <Card size="small">
            <Statistic
              title={stat.title}
              value={stat.value}
              precision={stat.precision ?? 2}
              suffix={stat.suffix}
              prefix={stat.prefix}
              valueStyle={stat.color ? { color: stat.color } : undefined}
            />
          </Card>
        </Col>
      ))}
    </Row>
  );
}
