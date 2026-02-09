import { useCallback } from 'react';
import { useAuthStore } from '../store/authStore';

/**
 * Hook for checking user permissions
 * Uses the SparkCRM permission pattern
 */
export function usePermissions() {
  const user = useAuthStore((state) => state.user);

  /**
   * Check if user has a specific permission
   */
  const hasPermission = useCallback(
    (permission: string | string[]): boolean => {
      if (!user) return false;

      // Super users have all permissions
      if (user.is_super_user) return true;

      // Check if user has wildcard permission
      if (user.permissions?.includes('*')) return true;

      const permissions = user.permissions || [];

      // Check for single permission
      if (typeof permission === 'string') {
        return permissions.includes(permission);
      }

      // Check if user has ANY of the provided permissions
      return permission.some((p) => permissions.includes(p));
    },
    [user]
  );

  /**
   * Check if user has ALL of the given permissions
   */
  const hasAllPermissions = useCallback(
    (permissions: string[]): boolean => {
      if (!user) return false;
      if (user.is_super_user) return true;
      if (user.permissions?.includes('*')) return true;

      const userPermissions = user.permissions || [];
      return permissions.every((p) => userPermissions.includes(p));
    },
    [user]
  );

  /**
   * Check if user is an admin
   */
  const isAdmin = useCallback((): boolean => {
    if (!user) return false;
    return user.is_super_user || user.role?.slug === 'admin';
  }, [user]);

  /**
   * Check if user is a super user
   */
  const isSuperUser = useCallback((): boolean => {
    return user?.is_super_user || false;
  }, [user]);

  /**
   * Get user's role
   */
  const getRole = useCallback(() => {
    return user?.role;
  }, [user]);

  return {
    hasPermission,
    hasAllPermissions,
    isAdmin,
    isSuperUser,
    getRole,
    permissions: user?.permissions || [],
  };
}

/**
 * Permission constants for easy reference
 */
export const Permissions = {
  // Products
  VIEW_PRODUCTS: 'access products',
  ADD_PRODUCT: 'add product',
  EDIT_PRODUCT: 'edit product',
  DELETE_PRODUCT: 'delete product',
  PRINT_BARCODE: 'print barcode',

  // Orders
  ADD_ORDER: 'add order',
  ACCESS_INVOICE: 'access customer invoice',
  SHOW_CART: 'show order cart',
  ADD_TO_CART: 'add to order cart',
  REMOVE_FROM_CART: 'remove from order cart',
  EMPTY_CART: 'empty order cart',

  // Customers
  ACCESS_CUSTOMERS: 'access customers',
  ADD_CUSTOMER: 'add customer',
  EDIT_CUSTOMER: 'edit customer',
  DELETE_CUSTOMER: 'delete customer',

  // Inventory
  ACCESS_INVENTORY: 'access inventory',
  ADJUST_INVENTORY: 'adjust inventory',
  TRANSFER_STOCK: 'transfer stock',

  // Returns
  ACCESS_RETURNS: 'access returns',
  CREATE_RETURN: 'create return',
  APPROVE_RETURN: 'approve return',
  PROCESS_RETURN: 'process return',

  // Reports
  ACCESS_REPORTS: 'access reports',

  // Settings
  ACCESS_SETTINGS: 'access settings',
  EDIT_SETTINGS: 'edit general settings',

  // Users
  ACCESS_USERS: 'access users',
  ADD_USER: 'add user',
  EDIT_USER: 'edit user',
  DELETE_USER: 'delete user',

  // Roles
  ACCESS_ROLES: 'access roles',
  ADD_ROLE: 'add role',
  EDIT_ROLE: 'edit role',
  DELETE_ROLE: 'delete role',

  // Stores
  ACCESS_STORES: 'access stores',
  ADD_STORE: 'add store',
  EDIT_STORE: 'edit store',
  DELETE_STORE: 'delete store',

  // POS
  ACCESS_POS: 'access pos',
  APPLY_DISCOUNT: 'apply discount',
  HOLD_ORDER: 'hold order',
  PROCESS_REFUND: 'process refund at pos',

  // Purchase Orders
  ACCESS_PURCHASE_ORDERS: 'access purchase orders',
  CREATE_PURCHASE_ORDER: 'create purchase order',
  APPROVE_PURCHASE_ORDER: 'approve purchase order',
  RECEIVE_PURCHASE_ORDER: 'receive purchase order',

  // Stock Transfers
  ACCESS_STOCK_TRANSFERS: 'access stock transfers',
  CREATE_STOCK_TRANSFER: 'create stock transfer',
  APPROVE_STOCK_TRANSFER: 'approve stock transfer',
  RECEIVE_STOCK_TRANSFER: 'receive stock transfer',

  // Loyalty
  ACCESS_LOYALTY: 'access loyalty',
  MANAGE_LOYALTY_TIERS: 'manage loyalty tiers',
  ADJUST_LOYALTY_POINTS: 'adjust loyalty points',
  REDEEM_LOYALTY_POINTS: 'redeem loyalty points',
} as const;
