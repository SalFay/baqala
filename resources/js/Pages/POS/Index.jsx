import { useState, useEffect, useRef } from 'react'
import { Head, usePage } from '@inertiajs/react'
import { useQuery } from '@tanstack/react-query'
import {
  Layout,
  Input,
  Tabs,
  Button,
  Card,
  Typography,
  Space,
  Badge,
  Empty,
  Divider,
  Tooltip,
} from 'antd'
import {
  SearchOutlined,
  BarcodeOutlined,
  UserOutlined,
  DeleteOutlined,
  PauseCircleOutlined,
  PlayCircleOutlined,
  ShoppingCartOutlined,
} from '@ant-design/icons'
import { useRecoilValue } from 'recoil'
import useCart from '@/Hooks/useCart'
import posService from '@/Helpers/api/posService'
import { formatCurrency } from '@/Helpers/formatters'

// Components
import ProductGrid from './Components/ProductGrid'
import CartItemRow from './Components/CartItemRow'
import CartSummary from './Components/CartSummary'
import CheckoutModal from './Components/CheckoutModal'
import CustomerSelectModal from './Components/CustomerSelectModal'
import HeldCartsModal from './Components/HeldCartsModal'
import HoldCartModal from './Components/HoldCartModal'
import ReceiptModal from './Components/ReceiptModal'

const { Sider, Content } = Layout
const { Title, Text } = Typography
const { Search } = Input

export default function POS() {
  const { categories, initialCart } = usePage().props
  const barcodeInputRef = useRef(null)

  // Local state
  const [selectedCategory, setSelectedCategory] = useState(null)
  const [searchQuery, setSearchQuery] = useState('')
  const [barcodeInput, setBarcodeInput] = useState('')

  // Cart hook
  const {
    cart,
    items,
    customer,
    cartSummary,
    itemCount,
    heldCarts,
    posUI,
    addItem,
    updateItem,
    removeItem,
    clearCart,
    setCustomer,
    scanBarcode,
    holdCart,
    restoreHeldCart,
    checkout,
    isAddingItem,
    isUpdatingItem,
    isRemovingItem,
    isCheckingOut,
    isHoldingCart,
    isRestoringCart,
    openCheckoutModal,
    closeCheckoutModal,
    openCustomerModal,
    closeCustomerModal,
    openHeldCartsModal,
    closeHeldCartsModal,
    openHoldCartModal,
    closeHoldCartModal,
    closeReceiptModal,
  } = useCart()

  // Fetch products
  const { data: productsData, isLoading: isLoadingProducts } = useQuery({
    queryKey: ['pos-products', selectedCategory, searchQuery],
    queryFn: async () => {
      const params = {}
      if (selectedCategory) params.category_id = selectedCategory
      if (searchQuery) params.search = searchQuery
      const response = await posService.getProducts(params)
      return response.data.data || []
    },
    staleTime: 30000, // 30 seconds
  })

  const products = productsData || []

  // Handle barcode scan
  const handleBarcodeScan = (e) => {
    e.preventDefault()
    if (barcodeInput.trim()) {
      scanBarcode(barcodeInput.trim())
      setBarcodeInput('')
    }
  }

  // Handle product add
  const handleAddProduct = (product) => {
    addItem(product.id, 1)
  }

  // Category tabs
  const categoryItems = [
    { key: 'all', label: 'All Products' },
    ...(categories || []).map((cat) => ({
      key: String(cat.id),
      label: cat.name,
    })),
  ]

  // Focus barcode input on mount
  useEffect(() => {
    if (barcodeInputRef.current) {
      barcodeInputRef.current.focus()
    }
  }, [])

  // Keyboard shortcut for barcode focus
  useEffect(() => {
    const handleKeyDown = (e) => {
      // F2 to focus barcode input
      if (e.key === 'F2') {
        e.preventDefault()
        barcodeInputRef.current?.focus()
      }
      // F4 to checkout
      if (e.key === 'F4') {
        e.preventDefault()
        openCheckoutModal()
      }
    }
    window.addEventListener('keydown', handleKeyDown)
    return () => window.removeEventListener('keydown', handleKeyDown)
  }, [openCheckoutModal])

  const isCartLoading = isAddingItem || isUpdatingItem || isRemovingItem

  return (
    <>
      <Head title="POS" />

      <Layout style={{ height: 'calc(100vh - 112px)', background: 'transparent' }}>
        {/* Left Panel - Products */}
        <Content style={{ padding: '0 16px 0 0', overflow: 'hidden' }}>
          <Card
            style={{ height: '100%', display: 'flex', flexDirection: 'column' }}
            styles={{ body: { flex: 1, overflow: 'hidden', display: 'flex', flexDirection: 'column' } }}
          >
            {/* Search & Barcode */}
            <div style={{ marginBottom: 16 }}>
              <Space.Compact style={{ width: '100%' }}>
                <Search
                  placeholder="Search products..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  onSearch={setSearchQuery}
                  allowClear
                  style={{ flex: 1 }}
                  prefix={<SearchOutlined />}
                />
                <Tooltip title="Press F2 to focus">
                  <Input
                    ref={barcodeInputRef}
                    placeholder="Scan barcode..."
                    value={barcodeInput}
                    onChange={(e) => setBarcodeInput(e.target.value)}
                    onPressEnter={handleBarcodeScan}
                    prefix={<BarcodeOutlined />}
                    style={{ width: 200 }}
                  />
                </Tooltip>
              </Space.Compact>
            </div>

            {/* Category Tabs */}
            <Tabs
              activeKey={selectedCategory ? String(selectedCategory) : 'all'}
              onChange={(key) => setSelectedCategory(key === 'all' ? null : parseInt(key))}
              items={categoryItems}
              style={{ marginBottom: 16 }}
              size="small"
            />

            {/* Product Grid */}
            <div style={{ flex: 1, overflow: 'auto' }}>
              <ProductGrid
                products={products}
                onAddProduct={handleAddProduct}
                loading={isLoadingProducts}
              />
            </div>
          </Card>
        </Content>

        {/* Right Panel - Cart */}
        <Sider
          width={400}
          style={{
            background: 'transparent',
          }}
        >
          <Card
            style={{ height: '100%', display: 'flex', flexDirection: 'column' }}
            styles={{
              body: {
                flex: 1,
                display: 'flex',
                flexDirection: 'column',
                overflow: 'hidden',
                padding: 16,
              }
            }}
          >
            {/* Cart Header */}
            <div
              style={{
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center',
                marginBottom: 16,
              }}
            >
              <Space>
                <Badge count={itemCount} overflowCount={99}>
                  <ShoppingCartOutlined style={{ fontSize: 24 }} />
                </Badge>
                <Title level={4} style={{ margin: 0 }}>
                  Cart
                </Title>
              </Space>
              <Space>
                <Tooltip title="Held Carts">
                  <Badge count={heldCarts?.length || 0} size="small">
                    <Button
                      icon={<PlayCircleOutlined />}
                      onClick={openHeldCartsModal}
                      size="small"
                    />
                  </Badge>
                </Tooltip>
                <Tooltip title="Hold Cart">
                  <Button
                    icon={<PauseCircleOutlined />}
                    onClick={openHoldCartModal}
                    disabled={items.length === 0}
                    size="small"
                  />
                </Tooltip>
                <Tooltip title="Clear Cart">
                  <Button
                    icon={<DeleteOutlined />}
                    onClick={clearCart}
                    disabled={items.length === 0}
                    danger
                    size="small"
                  />
                </Tooltip>
              </Space>
            </div>

            {/* Customer */}
            <div
              style={{
                padding: '8px 12px',
                background: '#f5f5f5',
                borderRadius: 8,
                marginBottom: 16,
                cursor: 'pointer',
              }}
              onClick={openCustomerModal}
            >
              <Space>
                <UserOutlined />
                {customer ? (
                  <Text strong>{customer.full_name}</Text>
                ) : (
                  <Text type="secondary">Walk-in Customer (Click to assign)</Text>
                )}
              </Space>
            </div>

            {/* Cart Items */}
            <div style={{ flex: 1, overflow: 'auto' }}>
              {items.length === 0 ? (
                <Empty
                  description="Cart is empty"
                  image={Empty.PRESENTED_IMAGE_SIMPLE}
                  style={{ marginTop: 60 }}
                />
              ) : (
                items.map((item) => (
                  <CartItemRow
                    key={item.id}
                    item={item}
                    onUpdateQuantity={updateItem}
                    onRemove={removeItem}
                    loading={isCartLoading}
                  />
                ))
              )}
            </div>

            {/* Cart Summary */}
            <CartSummary summary={cartSummary} />

            {/* Checkout Button */}
            <Button
              type="primary"
              size="large"
              block
              onClick={openCheckoutModal}
              disabled={items.length === 0}
              style={{ height: 56, fontSize: 18 }}
            >
              Checkout ({formatCurrency(cartSummary.total)})
            </Button>
          </Card>
        </Sider>
      </Layout>

      {/* Modals */}
      <CheckoutModal
        open={posUI.isCheckoutModalOpen}
        onClose={closeCheckoutModal}
        onCheckout={checkout}
        total={cartSummary.total}
        loading={isCheckingOut}
      />

      <CustomerSelectModal
        open={posUI.isCustomerModalOpen}
        onClose={closeCustomerModal}
        onSelect={setCustomer}
        currentCustomer={customer}
        onRemove={() => setCustomer(null)}
      />

      <HeldCartsModal
        open={posUI.isHeldCartsModalOpen}
        onClose={closeHeldCartsModal}
        heldCarts={heldCarts}
        onRestore={restoreHeldCart}
        loading={isRestoringCart}
      />

      <HoldCartModal
        open={posUI.isHoldCartModalOpen}
        onClose={closeHoldCartModal}
        onHold={holdCart}
        loading={isHoldingCart}
      />

      <ReceiptModal
        open={posUI.isReceiptModalOpen}
        onClose={closeReceiptModal}
        order={posUI.lastCompletedOrder?.order}
        receipt={posUI.lastCompletedOrder?.receipt}
      />
    </>
  )
}
