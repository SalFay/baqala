import { useState, useEffect, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  Row,
  Col,
  Card,
  Input,
  Button,
  Tabs,
  Badge,
  Spin,
  message,
  Modal,
  Empty,
} from 'antd';
import {
  SearchOutlined,
  ShoppingCartOutlined,
  PauseOutlined,
  DeleteOutlined,
  ArrowLeftOutlined,
  BarcodeOutlined,
} from '@ant-design/icons';
import { useQuery } from '@tanstack/react-query';
import { useCartStore } from '../../store/cartStore';
import { productService } from '../../api/services/product.service';
import { categoryService } from '../../api/services/category.service';
import ProductGrid from './components/ProductGrid';
import Cart from './components/Cart';
import PaymentPanel from './components/PaymentPanel';
import HoldOrdersModal from './components/HoldOrdersModal';
import CustomerSearchModal from './components/CustomerSearchModal';

export default function POSPage() {
  const navigate = useNavigate();
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedCategory, setSelectedCategory] = useState('all');
  const [showPayment, setShowPayment] = useState(false);
  const [showHoldOrders, setShowHoldOrders] = useState(false);
  const [showCustomerSearch, setShowCustomerSearch] = useState(false);
  const [holdName, setHoldName] = useState('');
  const [showHoldModal, setShowHoldModal] = useState(false);

  const {
    cart,
    summary,
    fetchCart,
    addItem,
    clearCart,
    holdCart,
    scanBarcode,
    isLoading,
  } = useCartStore();

  // Fetch cart on mount
  useEffect(() => {
    fetchCart();
  }, [fetchCart]);

  // Fetch categories
  const { data: categories = [] } = useQuery({
    queryKey: ['categories'],
    queryFn: () => categoryService.getCategories(),
  });

  // Fetch products
  const { data: productsData, isLoading: productsLoading } = useQuery({
    queryKey: ['products', selectedCategory, searchTerm],
    queryFn: () =>
      productService.getProducts({
        category_id: selectedCategory !== 'all' ? parseInt(selectedCategory) : undefined,
        search: searchTerm || undefined,
        status: 'active',
        per_page: 100,
      }),
  });

  const products = productsData?.data || [];

  // Handle barcode scan
  const handleBarcodeInput = useCallback(
    async (barcode) => {
      try {
        await scanBarcode(barcode);
        message.success('Product added');
      } catch (error) {
        message.error(error.message || 'Product not found');
      }
    },
    [scanBarcode]
  );

  // Handle product click
  const handleProductClick = async (product, variant) => {
    try {
      await addItem(product, 1, variant);
    } catch (error) {
      message.error(error.message || 'Failed to add product');
    }
  };

  // Handle hold order
  const handleHoldOrder = async () => {
    if (!cart?.items.length) {
      message.warning('Cart is empty');
      return;
    }
    setShowHoldModal(true);
  };

  const confirmHoldOrder = async () => {
    if (!holdName.trim()) {
      message.warning('Please enter a name for this order');
      return;
    }
    await holdCart(holdName);
    setHoldName('');
    setShowHoldModal(false);
    message.success('Order held successfully');
  };

  // Handle clear cart
  const handleClearCart = () => {
    Modal.confirm({
      title: 'Clear Cart',
      content: 'Are you sure you want to clear all items from the cart?',
      onOk: clearCart,
    });
  };

  // Keyboard shortcuts
  useEffect(() => {
    const handleKeyDown = (e) => {
      // F2 - Hold order
      if (e.key === 'F2') {
        e.preventDefault();
        handleHoldOrder();
      }
      // F3 - Recall held orders
      if (e.key === 'F3') {
        e.preventDefault();
        setShowHoldOrders(true);
      }
      // F9 - Checkout
      if (e.key === 'F9') {
        e.preventDefault();
        if (cart?.items.length) {
          setShowPayment(true);
        }
      }
      // Escape - Clear/Cancel
      if (e.key === 'Escape') {
        if (showPayment) {
          setShowPayment(false);
        }
      }
    };

    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, [cart, showPayment]);

  // Category tabs
  const categoryTabs = [
    { key: 'all', label: 'All Products' },
    ...categories.map((cat) => ({
      key: cat.id.toString(),
      label: cat.name,
    })),
  ];

  return (
    <div
      style={{
        height: '100vh',
        display: 'flex',
        flexDirection: 'column',
        background: '#f5f5f5',
      }}
    >
      {/* Header */}
      <div
        style={{
          padding: '12px 16px',
          background: '#fff',
          borderBottom: '1px solid #e8e8e8',
          display: 'flex',
          alignItems: 'center',
          gap: 16,
        }}
      >
        <Button icon={<ArrowLeftOutlined />} onClick={() => navigate('/dashboard')}>
          Back
        </Button>

        <Input
          placeholder="Search products or scan barcode..."
          prefix={<SearchOutlined />}
          suffix={<BarcodeOutlined />}
          value={searchTerm}
          onChange={(e) => setSearchTerm(e.target.value)}
          onPressEnter={(e) => {
            const value = e.target.value;
            if (value.match(/^\d{8,14}$/)) {
              handleBarcodeInput(value);
              setSearchTerm('');
            }
          }}
          style={{ maxWidth: 400 }}
          allowClear
        />

        <div style={{ flex: 1 }} />

        <Button icon={<PauseOutlined />} onClick={handleHoldOrder}>
          Hold (F2)
        </Button>
        <Badge count={useCartStore.getState().heldCarts.length}>
          <Button icon={<ShoppingCartOutlined />} onClick={() => setShowHoldOrders(true)}>
            Recall (F3)
          </Button>
        </Badge>
        <Button danger icon={<DeleteOutlined />} onClick={handleClearCart}>
          Clear
        </Button>
      </div>

      {/* Main content */}
      <Row style={{ flex: 1, overflow: 'hidden' }}>
        {/* Products section */}
        <Col
          xs={24}
          lg={16}
          style={{
            height: '100%',
            display: 'flex',
            flexDirection: 'column',
            borderRight: '1px solid #e8e8e8',
          }}
        >
          <Tabs
            activeKey={selectedCategory}
            onChange={setSelectedCategory}
            items={categoryTabs}
            style={{ padding: '0 16px', marginBottom: 0 }}
          />

          <div style={{ flex: 1, overflow: 'auto', padding: 16 }}>
            {productsLoading ? (
              <div style={{ textAlign: 'center', paddingTop: 100 }}>
                <Spin size="large" />
              </div>
            ) : products.length === 0 ? (
              <Empty description="No products found" />
            ) : (
              <ProductGrid products={products} onProductClick={handleProductClick} />
            )}
          </div>
        </Col>

        {/* Cart section */}
        <Col
          xs={24}
          lg={8}
          style={{
            height: '100%',
            display: 'flex',
            flexDirection: 'column',
            background: '#fff',
          }}
        >
          {showPayment ? (
            <PaymentPanel
              cart={cart}
              summary={summary}
              onBack={() => setShowPayment(false)}
              onComplete={() => {
                setShowPayment(false);
                message.success('Order completed successfully!');
              }}
            />
          ) : (
            <Cart
              onCheckout={() => setShowPayment(true)}
              onCustomerClick={() => setShowCustomerSearch(true)}
            />
          )}
        </Col>
      </Row>

      {/* Modals */}
      <Modal
        title="Hold Order"
        open={showHoldModal}
        onOk={confirmHoldOrder}
        onCancel={() => setShowHoldModal(false)}
        okText="Hold"
      >
        <Input
          placeholder="Enter order name/reference"
          value={holdName}
          onChange={(e) => setHoldName(e.target.value)}
          autoFocus
        />
      </Modal>

      <HoldOrdersModal
        open={showHoldOrders}
        onClose={() => setShowHoldOrders(false)}
      />

      <CustomerSearchModal
        open={showCustomerSearch}
        onClose={() => setShowCustomerSearch(false)}
      />
    </div>
  );
}
