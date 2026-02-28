import { useCallback, useEffect } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { useRecoilState, useRecoilValue } from 'recoil'
import { message } from 'antd'
import posService from '../Helpers/api/posService'
import {
  cartAtom,
  cartSummarySelector,
  cartItemCountSelector,
  heldCartsAtom,
  posUIAtom,
} from '../Helpers/posAtoms'

export function useCart() {
  const queryClient = useQueryClient()
  const [cart, setCart] = useRecoilState(cartAtom)
  const [heldCarts, setHeldCarts] = useRecoilState(heldCartsAtom)
  const [posUI, setPosUI] = useRecoilState(posUIAtom)
  const cartSummary = useRecoilValue(cartSummarySelector)
  const itemCount = useRecoilValue(cartItemCountSelector)

  // Fetch cart from server
  const {
    data: cartData,
    isLoading: isCartLoading,
    refetch: refetchCart,
  } = useQuery({
    queryKey: ['cart'],
    queryFn: async () => {
      const response = await posService.getCart()
      // API returns { cart: {...}, summary: {...} }
      const { cart: cartObj, summary } = response.data
      return {
        id: cartObj?.id,
        items: cartObj?.items || [],
        customer: cartObj?.customer,
        subtotal: summary?.subtotal || 0,
        tax_amount: summary?.tax_amount || 0,
        tax_rate: summary?.tax_rate || 0,
        discount: summary?.discount || 0,
        discount_value: summary?.discount_value || 0,
        discount_type: summary?.discount_type || null,
        discount_reason: summary?.discount_reason || null,
        total: summary?.total || 0,
      }
    },
    staleTime: 0, // Always refetch
  })

  // Sync cart data to Recoil state
  useEffect(() => {
    if (cartData) {
      setCart(cartData)
    }
  }, [cartData, setCart])

  // Fetch held carts
  const { refetch: refetchHeldCarts } = useQuery({
    queryKey: ['heldCarts'],
    queryFn: async () => {
      const response = await posService.getHeldCarts()
      return response.data
    },
    onSuccess: (data) => {
      setHeldCarts(data || [])
    },
  })

  // Helper to parse cart response
  const parseCartResponse = (response) => {
    const { cart: cartObj, summary } = response.data
    return {
      id: cartObj?.id,
      items: cartObj?.items || [],
      customer: cartObj?.customer,
      subtotal: summary?.subtotal || 0,
      tax_amount: summary?.tax_amount || 0,
      tax_rate: summary?.tax_rate || 0,
      discount: summary?.discount || 0,
      discount_value: summary?.discount_value || 0,
      discount_type: summary?.discount_type || null,
      discount_reason: summary?.discount_reason || null,
      total: summary?.total || 0,
    }
  }

  // Add item mutation
  const addItemMutation = useMutation({
    mutationFn: ({ productId, quantity, variantId }) =>
      posService.addItem(productId, quantity, variantId),
    onSuccess: (response) => {
      setCart(parseCartResponse(response))
      queryClient.invalidateQueries({ queryKey: ['cart'] })
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to add item')
    },
  })

  // Update item mutation
  const updateItemMutation = useMutation({
    mutationFn: ({ itemId, quantity }) =>
      posService.updateItem(itemId, quantity),
    onSuccess: (response) => {
      setCart(parseCartResponse(response))
      queryClient.invalidateQueries({ queryKey: ['cart'] })
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to update item')
    },
  })

  // Remove item mutation
  const removeItemMutation = useMutation({
    mutationFn: (itemId) => posService.removeItem(itemId),
    onSuccess: (response) => {
      setCart(parseCartResponse(response))
      queryClient.invalidateQueries({ queryKey: ['cart'] })
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to remove item')
    },
  })

  // Clear cart mutation
  const clearCartMutation = useMutation({
    mutationFn: () => posService.clearCart(),
    onSuccess: () => {
      setCart({
        id: null,
        items: [],
        customer: null,
        subtotal: 0,
        tax_amount: 0,
        discount: 0,
        total: 0,
      })
      queryClient.invalidateQueries({ queryKey: ['cart'] })
      message.success('Cart cleared')
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to clear cart')
    },
  })

  // Set customer mutation
  const setCustomerMutation = useMutation({
    mutationFn: (customerId) => posService.setCustomer(customerId),
    onSuccess: (response) => {
      setCart(parseCartResponse(response))
      queryClient.invalidateQueries({ queryKey: ['cart'] })
      message.success('Customer assigned')
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to assign customer')
    },
  })

  // Remove customer mutation
  const removeCustomerMutation = useMutation({
    mutationFn: () => posService.removeCustomer(),
    onSuccess: (response) => {
      setCart(parseCartResponse(response))
      queryClient.invalidateQueries({ queryKey: ['cart'] })
      message.success('Customer removed')
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to remove customer')
    },
  })

  // Scan barcode mutation
  const scanBarcodeMutation = useMutation({
    mutationFn: (barcode) => posService.scanBarcode(barcode),
    onSuccess: (response) => {
      setCart(parseCartResponse(response))
      queryClient.invalidateQueries({ queryKey: ['cart'] })
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Product not found')
    },
  })

  // Apply discount mutation
  const applyDiscountMutation = useMutation({
    mutationFn: ({ amount, type, reason }) => posService.applyDiscount(amount, type, reason),
    onSuccess: (response) => {
      setCart(parseCartResponse(response))
      queryClient.invalidateQueries({ queryKey: ['cart'] })
      message.success('Discount applied')
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to apply discount')
    },
  })

  // Remove discount mutation
  const removeDiscountMutation = useMutation({
    mutationFn: () => posService.removeDiscount(),
    onSuccess: (response) => {
      setCart(parseCartResponse(response))
      queryClient.invalidateQueries({ queryKey: ['cart'] })
      message.success('Discount removed')
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to remove discount')
    },
  })

  // Hold cart mutation
  const holdCartMutation = useMutation({
    mutationFn: (name) => posService.holdCart(name),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['cart'] })
      queryClient.invalidateQueries({ queryKey: ['heldCarts'] })
      refetchCart()
      refetchHeldCarts()
      message.success('Cart held')
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to hold cart')
    },
  })

  // Restore held cart mutation
  const restoreHeldCartMutation = useMutation({
    mutationFn: (cartId) => posService.restoreHeldCart(cartId),
    onSuccess: (response) => {
      setCart(parseCartResponse(response))
      queryClient.invalidateQueries({ queryKey: ['cart'] })
      queryClient.invalidateQueries({ queryKey: ['heldCarts'] })
      refetchHeldCarts()
      message.success('Cart restored')
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to restore cart')
    },
  })

  // Checkout mutation
  const checkoutMutation = useMutation({
    mutationFn: (checkoutData) => posService.checkout(checkoutData),
    onSuccess: (response) => {
      queryClient.invalidateQueries({ queryKey: ['cart'] })
      refetchCart()
      setPosUI((prev) => ({
        ...prev,
        isCheckoutModalOpen: false,
        isReceiptModalOpen: true,
        lastCompletedOrder: response.data,
      }))
      message.success('Order completed!')
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Checkout failed')
    },
  })

  // Actions
  const addItem = useCallback(
    (productId, quantity = 1, variantId = null) => {
      addItemMutation.mutate({ productId, quantity, variantId })
    },
    [addItemMutation]
  )

  const updateItem = useCallback(
    (itemId, quantity) => {
      if (quantity <= 0) {
        removeItemMutation.mutate(itemId)
      } else {
        updateItemMutation.mutate({ itemId, quantity })
      }
    },
    [updateItemMutation, removeItemMutation]
  )

  const removeItem = useCallback(
    (itemId) => {
      removeItemMutation.mutate(itemId)
    },
    [removeItemMutation]
  )

  const clearCart = useCallback(() => {
    clearCartMutation.mutate()
  }, [clearCartMutation])

  const setCustomer = useCallback(
    (customerId) => {
      if (customerId) {
        setCustomerMutation.mutate(customerId)
      } else {
        removeCustomerMutation.mutate()
      }
    },
    [setCustomerMutation, removeCustomerMutation]
  )

  const scanBarcode = useCallback(
    (barcode) => {
      scanBarcodeMutation.mutate(barcode)
    },
    [scanBarcodeMutation]
  )

  const applyDiscount = useCallback(
    (amount, type, reason) => {
      applyDiscountMutation.mutate({ amount, type, reason })
    },
    [applyDiscountMutation]
  )

  const removeDiscount = useCallback(() => {
    removeDiscountMutation.mutate()
  }, [removeDiscountMutation])

  const holdCart = useCallback(
    (name) => {
      holdCartMutation.mutate(name)
    },
    [holdCartMutation]
  )

  const restoreHeldCart = useCallback(
    (cartId) => {
      restoreHeldCartMutation.mutate(cartId)
    },
    [restoreHeldCartMutation]
  )

  const checkout = useCallback(
    (checkoutData) => {
      checkoutMutation.mutate(checkoutData)
    },
    [checkoutMutation]
  )

  // UI helpers
  const openCheckoutModal = useCallback(() => {
    if (cart.items?.length === 0) {
      message.warning('Cart is empty')
      return
    }
    setPosUI((prev) => ({ ...prev, isCheckoutModalOpen: true }))
  }, [cart.items, setPosUI])

  const closeCheckoutModal = useCallback(() => {
    setPosUI((prev) => ({ ...prev, isCheckoutModalOpen: false }))
  }, [setPosUI])

  const openCustomerModal = useCallback(() => {
    setPosUI((prev) => ({ ...prev, isCustomerModalOpen: true }))
  }, [setPosUI])

  const closeCustomerModal = useCallback(() => {
    setPosUI((prev) => ({ ...prev, isCustomerModalOpen: false }))
  }, [setPosUI])

  const openHeldCartsModal = useCallback(() => {
    refetchHeldCarts()
    setPosUI((prev) => ({ ...prev, isHeldCartsModalOpen: true }))
  }, [setPosUI, refetchHeldCarts])

  const closeHeldCartsModal = useCallback(() => {
    setPosUI((prev) => ({ ...prev, isHeldCartsModalOpen: false }))
  }, [setPosUI])

  const openHoldCartModal = useCallback(() => {
    if (cart.items?.length === 0) {
      message.warning('Cart is empty')
      return
    }
    setPosUI((prev) => ({ ...prev, isHoldCartModalOpen: true }))
  }, [cart.items, setPosUI])

  const closeHoldCartModal = useCallback(() => {
    setPosUI((prev) => ({ ...prev, isHoldCartModalOpen: false }))
  }, [setPosUI])

  const openDiscountModal = useCallback(() => {
    if (cart.items?.length === 0) {
      message.warning('Cart is empty')
      return
    }
    setPosUI((prev) => ({ ...prev, isDiscountModalOpen: true }))
  }, [cart.items, setPosUI])

  const closeDiscountModal = useCallback(() => {
    setPosUI((prev) => ({ ...prev, isDiscountModalOpen: false }))
  }, [setPosUI])

  const closeReceiptModal = useCallback(() => {
    setPosUI((prev) => ({
      ...prev,
      isReceiptModalOpen: false,
      lastCompletedOrder: null,
    }))
  }, [setPosUI])

  return {
    // Cart data
    cart,
    items: cart.items || [],
    customer: cart.customer,
    cartSummary,
    itemCount,
    isCartLoading,

    // Held carts
    heldCarts,

    // UI state
    posUI,

    // Cart actions
    addItem,
    updateItem,
    removeItem,
    clearCart,
    setCustomer,
    scanBarcode,
    applyDiscount,
    removeDiscount,
    holdCart,
    restoreHeldCart,
    checkout,
    refetchCart,

    // Loading states
    isAddingItem: addItemMutation.isPending,
    isUpdatingItem: updateItemMutation.isPending,
    isRemovingItem: removeItemMutation.isPending,
    isClearingCart: clearCartMutation.isPending,
    isSettingCustomer: setCustomerMutation.isPending,
    isScanningBarcode: scanBarcodeMutation.isPending,
    isApplyingDiscount: applyDiscountMutation.isPending,
    isRemovingDiscount: removeDiscountMutation.isPending,
    isHoldingCart: holdCartMutation.isPending,
    isRestoringCart: restoreHeldCartMutation.isPending,
    isCheckingOut: checkoutMutation.isPending,

    // UI actions
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
    closeReceiptModal,
  }
}

export default useCart
