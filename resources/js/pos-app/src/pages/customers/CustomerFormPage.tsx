import { useNavigate, useParams } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  Form,
  Input,
  Select,
  DatePicker,
  Switch,
  Button,
  Card,
  Row,
  Col,
  Typography,
  message,
  Spin,
} from 'antd';
import { ArrowLeftOutlined } from '@ant-design/icons';
import dayjs from 'dayjs';
import { customerService } from '../../api/services/customer.service';

const { Title } = Typography;

export default function CustomerFormPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const [form] = Form.useForm();
  const isEdit = Boolean(id);

  const { data: customer, isLoading } = useQuery({
    queryKey: ['customer', id],
    queryFn: () => customerService.getCustomer(parseInt(id!)),
    enabled: isEdit,
  });

  const saveMutation = useMutation({
    mutationFn: (data: any) =>
      isEdit
        ? customerService.updateCustomer(parseInt(id!), data)
        : customerService.createCustomer(data),
    onSuccess: () => {
      message.success(`Customer ${isEdit ? 'updated' : 'created'} successfully`);
      queryClient.invalidateQueries({ queryKey: ['customers'] });
      navigate('/customers');
    },
    onError: (error: any) => {
      message.error(error.response?.data?.message || 'Failed to save customer');
    },
  });

  const onFinish = (values: any) => {
    if (values.date_of_birth) {
      values.date_of_birth = values.date_of_birth.format('YYYY-MM-DD');
    }
    saveMutation.mutate(values);
  };

  if (isLoading) {
    return (
      <div style={{ textAlign: 'center', padding: 100 }}>
        <Spin size="large" />
      </div>
    );
  }

  const initialValues = customer
    ? {
        ...customer,
        date_of_birth: customer.date_of_birth ? dayjs(customer.date_of_birth) : null,
      }
    : { accepts_marketing: false };

  return (
    <div>
      <div style={{ display: 'flex', alignItems: 'center', marginBottom: 24 }}>
        <Button
          icon={<ArrowLeftOutlined />}
          onClick={() => navigate('/customers')}
          type="text"
        />
        <Title level={4} style={{ margin: 0, marginLeft: 8 }}>
          {isEdit ? 'Edit Customer' : 'Add Customer'}
        </Title>
      </div>

      <Card>
        <Form
          form={form}
          layout="vertical"
          onFinish={onFinish}
          initialValues={initialValues}
        >
          <Row gutter={24}>
            <Col xs={24} md={12}>
              <Form.Item
                name="first_name"
                label="First Name"
                rules={[{ required: true, message: 'Please enter first name' }]}
              >
                <Input />
              </Form.Item>
            </Col>
            <Col xs={24} md={12}>
              <Form.Item name="last_name" label="Last Name">
                <Input />
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={24}>
            <Col xs={24} md={12}>
              <Form.Item
                name="email"
                label="Email"
                rules={[{ type: 'email', message: 'Please enter a valid email' }]}
              >
                <Input />
              </Form.Item>
            </Col>
            <Col xs={24} md={12}>
              <Form.Item name="phone_mobile" label="Mobile Phone">
                <Input />
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={24}>
            <Col xs={24} md={12}>
              <Form.Item name="business_name" label="Business Name">
                <Input />
              </Form.Item>
            </Col>
            <Col xs={24} md={6}>
              <Form.Item name="gender" label="Gender">
                <Select
                  allowClear
                  options={[
                    { label: 'Male', value: 'male' },
                    { label: 'Female', value: 'female' },
                  ]}
                />
              </Form.Item>
            </Col>
            <Col xs={24} md={6}>
              <Form.Item name="date_of_birth" label="Date of Birth">
                <DatePicker style={{ width: '100%' }} />
              </Form.Item>
            </Col>
          </Row>

          <Form.Item name="address" label="Address">
            <Input.TextArea rows={2} />
          </Form.Item>

          <Form.Item
            name="accepts_marketing"
            label="Accepts Marketing"
            valuePropName="checked"
          >
            <Switch />
          </Form.Item>

          <Form.Item>
            <Button
              type="primary"
              htmlType="submit"
              loading={saveMutation.isPending}
            >
              {isEdit ? 'Update Customer' : 'Create Customer'}
            </Button>
            <Button
              style={{ marginLeft: 8 }}
              onClick={() => navigate('/customers')}
            >
              Cancel
            </Button>
          </Form.Item>
        </Form>
      </Card>
    </div>
  );
}
