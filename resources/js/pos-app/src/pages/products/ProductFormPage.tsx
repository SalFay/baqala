import { useNavigate, useParams } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  Form,
  Input,
  InputNumber,
  Select,
  Switch,
  Button,
  Card,
  Row,
  Col,
  Typography,
  message,
  Spin,
  Upload,
} from 'antd';
import { ArrowLeftOutlined, UploadOutlined } from '@ant-design/icons';
import { productService } from '../../api/services/product.service';
import { categoryService } from '../../api/services/category.service';

const { Title } = Typography;

export default function ProductFormPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const [form] = Form.useForm();
  const isEdit = Boolean(id);

  const { data: product, isLoading: productLoading } = useQuery({
    queryKey: ['product', id],
    queryFn: () => productService.getProduct(parseInt(id!)),
    enabled: isEdit,
  });

  const { data: categories = [] } = useQuery({
    queryKey: ['categories'],
    queryFn: () => categoryService.getCategories(),
  });

  const saveMutation = useMutation({
    mutationFn: (data: any) =>
      isEdit ? productService.updateProduct(parseInt(id!), data) : productService.createProduct(data),
    onSuccess: () => {
      message.success(`Product ${isEdit ? 'updated' : 'created'} successfully`);
      queryClient.invalidateQueries({ queryKey: ['products'] });
      navigate('/products');
    },
    onError: (error: any) => {
      message.error(error.response?.data?.message || 'Failed to save product');
    },
  });

  const onFinish = (values: any) => {
    saveMutation.mutate(values);
  };

  if (productLoading) {
    return (
      <div style={{ textAlign: 'center', padding: 100 }}>
        <Spin size="large" />
      </div>
    );
  }

  return (
    <div>
      <div style={{ display: 'flex', alignItems: 'center', marginBottom: 24 }}>
        <Button
          icon={<ArrowLeftOutlined />}
          onClick={() => navigate('/products')}
          type="text"
        />
        <Title level={4} style={{ margin: 0, marginLeft: 8 }}>
          {isEdit ? 'Edit Product' : 'Add Product'}
        </Title>
      </div>

      <Card>
        <Form
          form={form}
          layout="vertical"
          onFinish={onFinish}
          initialValues={
            product || {
              type: 'simple',
              status: 'active',
              taxable: true,
              track_inventory: true,
              low_stock_threshold: 5,
            }
          }
        >
          <Row gutter={24}>
            <Col xs={24} md={12}>
              <Form.Item
                name="name"
                label="Product Name"
                rules={[{ required: true, message: 'Please enter product name' }]}
              >
                <Input />
              </Form.Item>
            </Col>
            <Col xs={24} md={12}>
              <Form.Item name="arabic_name" label="Arabic Name">
                <Input />
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={24}>
            <Col xs={24} md={8}>
              <Form.Item
                name="category_id"
                label="Category"
                rules={[{ required: true, message: 'Please select category' }]}
              >
                <Select
                  options={categories.map((cat: any) => ({
                    label: cat.name,
                    value: cat.id,
                  }))}
                />
              </Form.Item>
            </Col>
            <Col xs={24} md={8}>
              <Form.Item name="sku" label="SKU">
                <Input />
              </Form.Item>
            </Col>
            <Col xs={24} md={8}>
              <Form.Item name="barcode" label="Barcode">
                <Input />
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={24}>
            <Col xs={24} md={8}>
              <Form.Item
                name="purchase_price"
                label="Purchase Price"
                rules={[{ required: true, message: 'Please enter purchase price' }]}
              >
                <InputNumber min={0} precision={2} style={{ width: '100%' }} />
              </Form.Item>
            </Col>
            <Col xs={24} md={8}>
              <Form.Item
                name="sale_price"
                label="Sale Price"
                rules={[{ required: true, message: 'Please enter sale price' }]}
              >
                <InputNumber min={0} precision={2} style={{ width: '100%' }} />
              </Form.Item>
            </Col>
            <Col xs={24} md={8}>
              <Form.Item
                name="type"
                label="Product Type"
                rules={[{ required: true }]}
              >
                <Select
                  options={[
                    { label: 'Simple', value: 'simple' },
                    { label: 'Variable', value: 'variable' },
                  ]}
                />
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={24}>
            <Col xs={24} md={6}>
              <Form.Item
                name="taxable"
                label="Taxable"
                valuePropName="checked"
              >
                <Switch />
              </Form.Item>
            </Col>
            <Col xs={24} md={6}>
              <Form.Item
                name="track_inventory"
                label="Track Inventory"
                valuePropName="checked"
              >
                <Switch />
              </Form.Item>
            </Col>
            <Col xs={24} md={6}>
              <Form.Item name="low_stock_threshold" label="Low Stock Threshold">
                <InputNumber min={0} style={{ width: '100%' }} />
              </Form.Item>
            </Col>
            <Col xs={24} md={6}>
              <Form.Item name="status" label="Status">
                <Select
                  options={[
                    { label: 'Active', value: 'active' },
                    { label: 'Inactive', value: 'inactive' },
                  ]}
                />
              </Form.Item>
            </Col>
          </Row>

          <Form.Item>
            <Button
              type="primary"
              htmlType="submit"
              loading={saveMutation.isPending}
            >
              {isEdit ? 'Update Product' : 'Create Product'}
            </Button>
            <Button
              style={{ marginLeft: 8 }}
              onClick={() => navigate('/products')}
            >
              Cancel
            </Button>
          </Form.Item>
        </Form>
      </Card>
    </div>
  );
}
