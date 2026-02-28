import { useState, useEffect, useRef } from 'react'
import { Head, usePage } from '@inertiajs/react'
import { useQuery } from '@tanstack/react-query'
import { Layout, Input, Button, Typography, Space, Badge, Empty, theme, Segmented, Spin } from 'antd'
import {
  SearchOutlined,
  UserOutlined,
  ShoppingCartOutlined,
  GiftOutlined,
  HistoryOutlined,
  RollbackOutlined,
  PauseCircleOutlined,
  PlayCircleOutlined,
  DeleteOutlined,
  ClearOutlined,
  ScanOutlined,
} from '@ant-design/icons'
import { useRecoilValue } from 'recoil'
import useCart from '@/Hooks/useCart'
import posService from '@/Helpers/api/posService'
import { formatCurrency } from '@/Helpers/formatters'

// Components
import ProductGrid from './Components/ProductGrid'
import CartItemRow from './Components/CartItemRow'
import CheckoutModal from './Components/CheckoutModal'
import CustomerSelectModal from './Components/CustomerSelectModal'
import HeldCartsModal from './Components/HeldCartsModal'
import HoldCartModal from './Components/HoldCartModal'
import ReceiptModal from './Components/ReceiptModal'
import DiscountModal from './Components/DiscountModal'
import LoyaltyPanel from './Components/LoyaltyPanel'
import RecentOrdersDrawer from './Components/RecentOrdersDrawer'
import ProductQuickView from './Components/ProductQuickView'
import ReturnModal from './Components/ReturnModal'

const { Content } = Layout
const { Title, Text } = Typography

export default function POS() {
  const { token } = theme.useToken()
  const { categories, initialCart } = usePage().props
  const searchInputRef = useRef(null)
  const productGridRef = useRef(null)

  // Local state
  const [selectedCategory, setSelectedCategory] = useState('all')
  const [searchQuery, setSearchQuery] = useState('')
  const [barcodeInput, setBarcodeInput] = useState('')
  const [keyboardMode, setKeyboardMode] = useState(false)
  const [selectedProductIndex, setSelectedProductIndex] = useState(-1)
  const [selectedCartItemIndex, setSelectedCartItemIndex] = useState(-1)
  const [isOrdersDrawerOpen, setIsOrdersDrawerOpen] = useState(false)
  const [reprintOrder, setReprintOrder] = useState(null)
  const [reprintReceipt, setReprintReceipt] = useState(null)
  const [quickViewProduct, setQuickViewProduct] = useState(null)
  const [isReturnModalOpen, setIsReturnModalOpen] = useState(false)

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
    openDiscountModal,
    closeDiscountModal,
    applyDiscount,
    removeDiscount,
    isApplyingDiscount,
    isRemovingDiscount,
    closeReceiptModal,
  } = useCart()

  // Fetch products
  const { data: productsData, isLoading: isLoadingProducts } = useQuery({
    queryKey: ['pos-products', selectedCategory, searchQuery],
    queryFn: async () => {
      const params = {}
      if (selectedCategory && selectedCategory !== 'all') params.category_id = selectedCategory
      if (searchQuery) params.search = searchQuery
      const response = await posService.getProducts(params)
      return response.data.data || []
    },
    staleTime: 30000,
  })

  const products = productsData || []

  // Handle barcode scan
  const handleBarcodeScan = (e) => {
    if (e.key === 'Enter' && barcodeInput.trim()) {
      scanBarcode(barcodeInput.trim())
      setBarcodeInput('')
    }
  }

  // Handle product add
  const handleAddProduct = (product) => {
    addItem(product.id, 1)
  }

  // Category options for Segmented
  const categoryOptions = [
    { label: 'All', value: 'all' },
    ...(categories || []).map((cat) => ({
      label: cat.name,
      value: String(cat.id),
    })),
  ]

  // Keyboard shortcuts
  useEffect(() => {
    const handleKeyDown = (e) => {
      const isInputFocused = ['INPUT', 'TEXTAREA'].includes(document.activeElement?.tagName)

      if (e.key === 'F1') { e.preventDefault(); setKeyboardMode(prev => !prev); return }
      if (e.key === 'F3') { e.preventDefault(); searchInputRef.current?.focus(); return }
      if (e.key === 'F4' && items.length > 0) { e.preventDefault(); openCheckoutModal(); return }
      if (e.key === 'F6' && items.length > 0) { e.preventDefault(); openHoldCartModal(); return }
      if (e.key === 'F7') { e.preventDefault(); openHeldCartsModal(); return }
      if (e.key === 'F8') { e.preventDefault(); openCustomerModal(); return }
      if (e.ctrlKey && e.key === 'Enter' && items.length > 0) { e.preventDefault(); openCheckoutModal(); return }

      if (e.key === 'Escape') {
        if (posUI.isCheckoutModalOpen) closeCheckoutModal()
        else if (posUI.isCustomerModalOpen) closeCustomerModal()
        else if (posUI.isHeldCartsModalOpen) closeHeldCartsModal()
        else if (posUI.isHoldCartModalOpen) closeHoldCartModal()
        else if (posUI.isReceiptModalOpen) closeReceiptModal()
        else if (keyboardMode) { setKeyboardMode(false); setSelectedProductIndex(-1) }
        return
      }

      if (keyboardMode && !isInputFocused) {
        if (e.key === 'ArrowUp') { e.preventDefault(); productGridRef.current?.navigateUp(); return }
        if (e.key === 'ArrowDown') { e.preventDefault(); productGridRef.current?.navigateDown(); return }
        if (e.key === 'ArrowLeft') { e.preventDefault(); productGridRef.current?.navigateLeft(); return }
        if (e.key === 'ArrowRight') { e.preventDefault(); productGridRef.current?.navigateRight(); return }
        if (e.key === 'Enter') { e.preventDefault(); productGridRef.current?.selectCurrent(); return }
      }
    }

    window.addEventListener('keydown', handleKeyDown)
    return () => window.removeEventListener('keydown', handleKeyDown)
  }, [keyboardMode, items, posUI, openCheckoutModal, openHoldCartModal, openHeldCartsModal, openCustomerModal, closeCheckoutModal, closeCustomerModal, closeHeldCartsModal, closeHoldCartModal, closeReceiptModal])

  const isCartLoading = isAddingItem || isUpdatingItem || isRemovingItem

  // Styles
  const styles = {
    container: {
      display: 'flex',
      height: 'calc(100vh - 112px)',
      gap: 16,
      padding: '0 8px',
    },
    leftPanel: {
      flex: 1,
      display: 'flex',
      flexDirection: 'column',
      background: token.colorBgContainer,
      borderRadius: 16,
      overflow: 'hidden',
      boxShadow: '0 2px 8px rgba(0,0,0,0.06)',
    },
    header: {
      padding: '16px 20px',
      borderBottom: `1px solid ${token.colorBorderSecondary}`,
      display: 'flex',
      gap: 12,
      alignItems: 'center',
      flexWrap: 'wrap',
    },
    searchBox: {
      flex: 1,
      minWidth: 200,
      maxWidth: 400,
    },
    categoryBar: {
      padding: '12px 20px',
      borderBottom: `1px solid ${token.colorBorderSecondary}`,
      overflowX: 'auto',
    },
    productArea: {
      flex: 1,
      padding: 16,
      overflow: 'auto',
    },
    rightPanel: {
      width: 400,
      display: 'flex',
      flexDirection: 'column',
      background: token.colorBgContainer,
      borderRadius: 16,
      overflow: 'hidden',
      boxShadow: '0 2px 8px rgba(0,0,0,0.06)',
    },
    cartHeader: {
      padding: '16px 20px',
      borderBottom: `1px solid ${token.colorBorderSecondary}`,
      display: 'flex',
      justifyContent: 'space-between',
      alignItems: 'center',
    },
    customerSection: {
      padding: '12px 20px',
      borderBottom: `1px solid ${token.colorBorderSecondary}`,
    },
    cartItems: {
      flex: 1,
      padding: '12px 16px',
      overflow: 'auto',
    },
    cartFooter: {
      padding: 20,
      borderTop: `1px solid ${token.colorBorderSecondary}`,
      background: token.colorBgLayout,
    },
    summaryRow: {
      display: 'flex',
      justifyContent: 'space-between',
      marginBottom: 8,
    },
    totalRow: {
      display: 'flex',
      justifyContent: 'space-between',
      alignItems: 'center',
      paddingTop: 12,
      marginTop: 12,
      borderTop: `2px solid ${token.colorBorder}`,
    },
    checkoutBtn: {
      height: 56,
      fontSize: 18,
      fontWeight: 600,
      borderRadius: 12,
      marginTop: 16,
    },
    actionBtn: {
      height: 44,
      width: 44,
      borderRadius: 10,
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
    },
    quickAction: {
      display: 'flex',
      flexDirection: 'column',
      alignItems: 'center',
      padding: '8px 12px',
      borderRadius: 10,
      cursor: 'pointer',
      transition: 'all 0.2s',
      minWidth: 70,
    },
  }

  return (
    <>
      <Head title="POS" />

      <div style={styles.container}>
        {/* Left Panel - Products */}
        <div style={styles.leftPanel}>
          {/* Search Header */}
          <div style={styles.header}>
            <Input
              ref={searchInputRef}
              placeholder="Search products..."
              prefix={<SearchOutlined style={{ color: token.colorTextSecondary }} />}
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              allowClear
              size="large"
              style={styles.searchBox}
            />

            <Input
              placeholder="Scan barcode"
              prefix={<ScanOutlined style={{ color: token.colorTextSecondary }} />}
              value={barcodeInput}
              onChange={(e) => setBarcodeInput(e.target.value)}
              onKeyDown={handleBarcodeScan}
              size="large"
              style={{ width: 180 }}
            />

            <Space size={8}>
              <Button
                icon={<HistoryOutlined />}
                size="large"
                style={styles.actionBtn}
                onClick={() => setIsOrdersDrawerOpen(true)}
              />
              <Button
                icon={<RollbackOutlined />}
                size="large"
                style={styles.actionBtn}
                onClick={() => setIsReturnModalOpen(true)}
              />
            </Space>

            {keyboardMode && (
              <Badge status="processing" text="Keyboard Mode" style={{ marginLeft: 'auto' }} />
            )}
          </div>

          {/* Category Bar */}
          <div style={styles.categoryBar}>
            <Segmented
              value={selectedCategory}
              onChange={setSelectedCategory}
              options={categoryOptions}
              size="large"
              style={{ fontWeight: 500 }}
            />
          </div>

          {/* Product Grid */}
          <div style={styles.productArea}>
            {isLoadingProducts ? (
              <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100%' }}>
                <Spin size="large" />
              </div>
            ) : (
              <ProductGrid
                ref={productGridRef}
                products={products}
                onAddProduct={handleAddProduct}
                loading={isLoadingProducts}
                cartItems={items}
                selectedIndex={selectedProductIndex}
                onSelectedIndexChange={setSelectedProductIndex}
                keyboardEnabled={keyboardMode}
                onQuickView={setQuickViewProduct}
              />
            )}
          </div>
        </div>

        {/* Right Panel - Cart */}
        <div style={styles.rightPanel}>
          {/* Cart Header */}
          <div style={styles.cartHeader}>
            <Space>
              <Badge count={itemCount} overflowCount={99} offset={[-2, 2]}>
                <ShoppingCartOutlined style={{ fontSize: 24, color: token.colorPrimary }} />
              </Badge>
              <Title level={4} style={{ margin: 0 }}>Cart</Title>
            </Space>

            <Space size={4}>
              <Button
                type={cartSummary.discount > 0 ? 'primary' : 'default'}
                icon={<GiftOutlined />}
                onClick={openDiscountModal}
                disabled={items.length === 0}
                style={styles.actionBtn}
              />
              <Badge count={heldCarts?.length || 0} size="small" offset={[-4, 4]}>
                <Button
                  icon={<PlayCircleOutlined />}
                  onClick={openHeldCartsModal}
                  style={styles.actionBtn}
                />
              </Badge>
              <Button
                icon={<PauseCircleOutlined />}
                onClick={openHoldCartModal}
                disabled={items.length === 0}
                style={styles.actionBtn}
              />
              <Button
                icon={<ClearOutlined />}
                onClick={clearCart}
                disabled={items.length === 0}
                danger
                style={styles.actionBtn}
              />
            </Space>
          </div>

          {/* Customer Selection */}
          <div
            style={{
              ...styles.customerSection,
              cursor: 'pointer',
              background: customer ? token.colorPrimaryBg : 'transparent',
            }}
            onClick={openCustomerModal}
          >
            <Space>
              <div
                style={{
                  width: 40,
                  height: 40,
                  borderRadius: 10,
                  background: customer ? token.colorPrimary : token.colorBgLayout,
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                }}
              >
                <UserOutlined style={{ color: customer ? '#fff' : token.colorTextSecondary, fontSize: 18 }} />
              </div>
              <div>
                <Text strong style={{ display: 'block' }}>
                  {customer ? customer.full_name : 'Walk-in Customer'}
                </Text>
                <Text type="secondary" style={{ fontSize: 12 }}>
                  {customer?.phone || 'Tap to assign customer'}
                </Text>
              </div>
            </Space>
          </div>

          {/* Loyalty Panel */}
          {customer && (
            <div style={{ padding: '0 16px' }}>
              <LoyaltyPanel
                customerId={customer.id}
                cartTotal={cartSummary.total}
                onRedeemPoints={(points) => console.log('Redeem points:', points)}
              />
            </div>
          )}

          {/* Cart Items */}
          <div style={styles.cartItems}>
            {items.length === 0 ? (
              <Empty
                description={
                  <div style={{ textAlign: 'center' }}>
                    <Text type="secondary" style={{ display: 'block' }}>Cart is empty</Text>
                    <Text type="secondary" style={{ fontSize: 12 }}>Tap products to add</Text>
                  </div>
                }
                image={Empty.PRESENTED_IMAGE_SIMPLE}
                style={{ marginTop: 40 }}
              />
            ) : (
              items.map((item, index) => (
                <CartItemRow
                  key={item.id}
                  item={item}
                  onUpdateQuantity={updateItem}
                  onRemove={removeItem}
                  loading={isCartLoading}
                  isSelected={keyboardMode && selectedCartItemIndex === index}
                />
              ))
            )}
          </div>

          {/* Cart Footer */}
          <div style={styles.cartFooter}>
            <div style={styles.summaryRow}>
              <Text type="secondary">Subtotal</Text>
              <Text>{formatCurrency(cartSummary.subtotal)}</Text>
            </div>

            {cartSummary.discount > 0 && (
              <div style={styles.summaryRow}>
                <Text type="secondary">Discount</Text>
                <Text type="success">-{formatCurrency(cartSummary.discount)}</Text>
              </div>
            )}

            {cartSummary.tax > 0 && (
              <div style={styles.summaryRow}>
                <Text type="secondary">Tax ({cartSummary.tax_rate}%)</Text>
                <Text>{formatCurrency(cartSummary.tax)}</Text>
              </div>
            )}

            <div style={styles.totalRow}>
              <Title level={4} style={{ margin: 0 }}>Total</Title>
              <Title level={3} style={{ margin: 0, color: token.colorPrimary }}>
                {formatCurrency(cartSummary.total)}
              </Title>
            </div>

            <Button
              type="primary"
              block
              size="large"
              onClick={openCheckoutModal}
              disabled={items.length === 0}
              icon={<ShoppingCartOutlined />}
              style={styles.checkoutBtn}
            >
              Checkout {items.length > 0 && `(${formatCurrency(cartSummary.total)})`}
            </Button>
          </div>
        </div>
      </div>

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

      <DiscountModal
        open={posUI.isDiscountModalOpen}
        onClose={closeDiscountModal}
        onApply={(amount, type, reason) => {
          applyDiscount(amount, type, reason)
          closeDiscountModal()
        }}
        onRemove={removeDiscount}
        subtotal={cartSummary.subtotal}
        currentDiscount={{
          discount: cartSummary.discount,
          discount_value: cartSummary.discount_value,
          discount_type: cartSummary.discount_type,
          discount_reason: cartSummary.discount_reason,
        }}
        loading={isApplyingDiscount || isRemovingDiscount}
      />

      <RecentOrdersDrawer
        open={isOrdersDrawerOpen}
        onClose={() => setIsOrdersDrawerOpen(false)}
        onReprintReceipt={(order, receipt) => {
          setReprintOrder(order)
          setReprintReceipt(receipt)
        }}
      />

      {reprintReceipt && (
        <ReceiptModal
          open={!!reprintReceipt}
          onClose={() => {
            setReprintOrder(null)
            setReprintReceipt(null)
          }}
          order={reprintOrder}
          receipt={reprintReceipt}
        />
      )}

      <ProductQuickView
        open={!!quickViewProduct}
        onClose={() => setQuickViewProduct(null)}
        product={quickViewProduct}
        onAddToCart={(product, variant) => {
          if (variant) addItem(variant.id, 1, { variant_id: variant.id })
          else addItem(product.id, 1)
          setQuickViewProduct(null)
        }}
        loading={isAddingItem}
      />

      <ReturnModal
        open={isReturnModalOpen}
        onClose={() => setIsReturnModalOpen(false)}
        onReturnComplete={(result) => console.log('Return completed:', result)}
      />
    </>
  )
}
