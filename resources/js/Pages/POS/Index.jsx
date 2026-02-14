import { useState, useEffect, useCallback } from 'react';
import { Head, router } from '@inertiajs/react';
import {
    Row, Col, Input, Button, Tabs, Badge, Spin, Modal, Empty, message,
} from 'antd';
import {
    SearchOutlined, ShoppingCartOutlined, PauseOutlined, DeleteOutlined,
    ArrowLeftOutlined, BarcodeOutlined,
} from '@ant-design/icons';
import { useRecoilState } from 'recoil';
import axios from 'axios';
import ProductGrid from './Components/ProductGrid';
import Cart from './Components/Cart';
import PaymentPanel from './Components/PaymentPanel';
import HoldOrdersModal from './Components/HoldOrdersModal';
import CustomerSearchModal from './Components/CustomerSearchModal';
import { cartAtom } from '@/Helpers/atoms/cartAtom';

export default function POSIndex({ categories = [], initialCart = null }) {
    const [cart, setCart] = useRecoilState(cartAtom);
    const [searchTerm, setSearchTerm] = useState('');
    const [selectedCategory, setSelectedCategory] = useState('all');
    const [showPayment, setShowPayment] = useState(false);
    const [showHoldOrders, setShowHoldOrders] = useState(false);
    const [showCustomerSearch, setShowCustomerSearch] = useState(false);
    const [holdName, setHoldName] = useState('');
    const [showHoldModal, setShowHoldModal] = useState(false);
    const [products, setProducts] = useState([]);
    const [productsLoading, setProductsLoading] = useState(false);
    const [heldOrders, setHeldOrders] = useState([]);

    // Initialize cart
    useEffect(() => {
        if (initialCart) {
            setCart(initialCart);
        }
        fetchCart();
        fetchHeldOrders();
    }, []);

    // Fetch cart from server
    const fetchCart = async () => {
        try {
            const response = await axios.get('/pos/cart');
            setCart(response.data);
        } catch (error) {
            console.error('Failed to fetch cart:', error);
        }
    };

    // Fetch products
    useEffect(() => {
        fetchProducts();
    }, [selectedCategory, searchTerm]);

    const fetchProducts = async () => {
        setProductsLoading(true);
        try {
            const params = { status: 'active', per_page: 100 };
            if (selectedCategory !== 'all') params.category_id = selectedCategory;
            if (searchTerm) params.search = searchTerm;

            const response = await axios.get('/pos/products', { params });
            setProducts(response.data.data || []);
        } catch (error) {
            console.error('Failed to fetch products:', error);
        } finally {
            setProductsLoading(false);
        }
    };

    // Fetch held orders
    const fetchHeldOrders = async () => {
        try {
            const response = await axios.get('/pos/cart/hold');
            setHeldOrders(response.data?.data || []);
        } catch (error) {
            console.error('Failed to fetch held orders:', error);
        }
    };

    // Add item to cart
    const addItem = async (product, quantity = 1, variantId = null) => {
        try {
            const response = await axios.post('/pos/cart/items', {
                product_id: product.id,
                variant_id: variantId,
                quantity,
            });
            setCart(response.data);
            message.success('Added to cart');
        } catch (error) {
            message.error(error.response?.data?.message || 'Failed to add item');
        }
    };

    // Update item quantity
    const updateItemQuantity = async (itemId, quantity) => {
        try {
            const response = await axios.put(`/pos/cart/items/${itemId}`, { quantity });
            setCart(response.data);
        } catch (error) {
            message.error('Failed to update quantity');
        }
    };

    // Remove item from cart
    const removeItem = async (itemId) => {
        try {
            const response = await axios.delete(`/pos/cart/items/${itemId}`);
            setCart(response.data);
        } catch (error) {
            message.error('Failed to remove item');
        }
    };

    // Clear cart
    const clearCart = async () => {
        try {
            const response = await axios.delete('/pos/cart');
            setCart(response.data);
        } catch (error) {
            message.error('Failed to clear cart');
        }
    };

    // Hold cart
    const holdCart = async () => {
        if (!cart?.items?.length) {
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
        try {
            await axios.post('/pos/cart/hold', { name: holdName });
            setHoldName('');
            setShowHoldModal(false);
            fetchCart();
            fetchHeldOrders();
            message.success('Order held successfully');
        } catch (error) {
            message.error('Failed to hold order');
        }
    };

    // Restore held order
    const restoreHeldOrder = async (cartId) => {
        try {
            const response = await axios.post(`/pos/cart/hold/${cartId}/restore`);
            setCart(response.data);
            fetchHeldOrders();
            setShowHoldOrders(false);
            message.success('Order restored');
        } catch (error) {
            message.error('Failed to restore order');
        }
    };

    // Set customer
    const setCustomer = async (customerId) => {
        try {
            const response = await axios.post('/pos/cart/customer', { customer_id: customerId });
            setCart(response.data);
            setShowCustomerSearch(false);
            message.success('Customer added');
        } catch (error) {
            message.error('Failed to set customer');
        }
    };

    // Handle barcode scan
    const handleBarcodeInput = async (barcode) => {
        try {
            const response = await axios.post('/pos/cart/scan', { barcode });
            setCart(response.data);
            message.success('Product added');
        } catch (error) {
            message.error(error.response?.data?.message || 'Product not found');
        }
    };

    // Handle clear cart confirmation
    const handleClearCart = () => {
        Modal.confirm({
            title: 'Clear Cart',
            content: 'Are you sure you want to clear all items from the cart?',
            onOk: clearCart,
        });
    };

    // Checkout
    const handleCheckout = async (paymentMethod, cashReceived) => {
        try {
            const response = await axios.post('/pos/cart/checkout', {
                payment_method: paymentMethod,
                cash_received: cashReceived,
            });
            return response.data;
        } catch (error) {
            throw error.response?.data || error;
        }
    };

    // Keyboard shortcuts
    useEffect(() => {
        const handleKeyDown = (e) => {
            if (e.key === 'F2') {
                e.preventDefault();
                holdCart();
            }
            if (e.key === 'F3') {
                e.preventDefault();
                setShowHoldOrders(true);
            }
            if (e.key === 'F9') {
                e.preventDefault();
                if (cart?.items?.length) {
                    setShowPayment(true);
                }
            }
            if (e.key === 'Escape') {
                if (showPayment) setShowPayment(false);
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

    const cartSummary = {
        subtotal: cart?.subtotal || 0,
        tax_amount: cart?.tax_amount || 0,
        discount: cart?.discount || 0,
        total: cart?.total || 0,
        items_count: cart?.items?.length || 0,
    };

    return (
        <>
            <Head title="Point of Sale" />

            <div style={{ height: '100vh', display: 'flex', flexDirection: 'column', background: '#f5f5f5' }}>
                {/* Header */}
                <div style={{
                    padding: '12px 16px',
                    background: '#fff',
                    borderBottom: '1px solid #e8e8e8',
                    display: 'flex',
                    alignItems: 'center',
                    gap: 16,
                }}>
                    <Button icon={<ArrowLeftOutlined />} onClick={() => router.visit('/dashboard')}>
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

                    <Button icon={<PauseOutlined />} onClick={holdCart}>
                        Hold (F2)
                    </Button>
                    <Badge count={heldOrders.length}>
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
                    <Col xs={24} lg={16} style={{
                        height: '100%',
                        display: 'flex',
                        flexDirection: 'column',
                        borderRight: '1px solid #e8e8e8',
                    }}>
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
                                <ProductGrid products={products} onProductClick={addItem} />
                            )}
                        </div>
                    </Col>

                    {/* Cart section */}
                    <Col xs={24} lg={8} style={{
                        height: '100%',
                        display: 'flex',
                        flexDirection: 'column',
                        background: '#fff',
                    }}>
                        {showPayment ? (
                            <PaymentPanel
                                cart={cart}
                                summary={cartSummary}
                                onBack={() => setShowPayment(false)}
                                onCheckout={handleCheckout}
                                onComplete={() => {
                                    setShowPayment(false);
                                    fetchCart();
                                }}
                            />
                        ) : (
                            <Cart
                                cart={cart}
                                summary={cartSummary}
                                onUpdateQuantity={updateItemQuantity}
                                onRemoveItem={removeItem}
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
                    heldOrders={heldOrders}
                    onRestore={restoreHeldOrder}
                />

                <CustomerSearchModal
                    open={showCustomerSearch}
                    onClose={() => setShowCustomerSearch(false)}
                    onSelect={setCustomer}
                />
            </div>

            <style>{`
                .product-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                    gap: 12px;
                }
                .product-card {
                    cursor: pointer;
                    transition: transform 0.2s, box-shadow 0.2s;
                }
                .product-card:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                }
            `}</style>
        </>
    );
}
