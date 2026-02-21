import { message } from 'antd'
import toast from 'react-hot-toast'

export function handleApiError(error) {
  if (!error || !error.response || !error.response.data) {
    if (error.errorFields) {
      error.errorFields.forEach(({ name, errors }) => {
        toast.error(errors)
      })
    } else {
      toast.error(error.message)
    }
    return
  }

  const { errors, message: errorMessage } = error.response.data

  if (errors && typeof errors === 'object') {
    Object.keys(errors).forEach((field) => {
      toast.error(`${errors[field]}`)
    })
  } else if (errorMessage) {
    message.open({
      type: 'error',
      content: errorMessage,
    })
  } else {
    toast.error(errors)
  }
}

export function handleApiSuccess(response) {
  if (!response || !response.data) {
    return
  }

  const { notifications } = response.data
  if (notifications && notifications.length > 0) {
    notifications
      .filter((notification) => notification.type === 'success' && notification.message)
      .forEach((notification) => {
        message.success(notification.message)
      })
  } else {
    // Fallback: check for direct message in response
    const { message: successMessage } = response.data
    if (successMessage) {
      message.success(successMessage)
    }
  }
}

export const delay = (ms) => new Promise((resolve) => setTimeout(resolve, ms))

export const isEmpty = (value) => {
  if (value == null) return true
  if (typeof value === 'number' && value === 0) return true
  if (typeof value === 'string' || Array.isArray(value)) return value.length === 0
  if (typeof value.size === 'number') return value.size === 0
  if (typeof value === 'object') return Object.keys(value).length === 0
  return false
}

export const formatCurrency = (number, fractionDigits = 2, prefix = '$') => {
  if (number === undefined || number === null || isNaN(number)) {
    number = 0
  }
  const formattedNumber = Number(number).toFixed(fractionDigits)
  const [integerPart, decimalPart] = formattedNumber.split('.')
  const formattedInteger = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',')
  return prefix + ' ' + formattedInteger + (decimalPart ? '.' + decimalPart : '')
}

export const getInitials = (name) => {
  if (!name || typeof name !== 'string') return '?'
  const nameParts = name.trim().split(' ')
  const firstInitial = nameParts[0]?.[0] || ''
  const secondPart = nameParts[1]
  let secondInitial = ''
  if (secondPart && secondPart[0] !== '(') {
    secondInitial = secondPart[0] || ''
  }
  return `${firstInitial}${secondInitial}`.toUpperCase()
}

export const MODAL_TITLE_STYLE = (token) => ({
  padding: '6px 10px',
  borderBottom: `1px solid ${token.colorBorder}`,
  backgroundColor: token.colorBorderSecondary,
  borderTopLeftRadius: token.borderRadiusLG,
  borderTopRightRadius: token.borderRadiusLG,
})
