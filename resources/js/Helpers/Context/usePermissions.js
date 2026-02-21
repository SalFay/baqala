import { useRecoilValue } from 'recoil'
import { permissionsAtom, userAtom } from '@/Helpers/atom.js'
import { useCallback } from 'react'

const usePermissions = () => {
  const permissions = useRecoilValue(permissionsAtom) || []
  const user = useRecoilValue(userAtom) || {}

  const hasPermission = useCallback(
    (permissionToCheck) => {
      if (user.root_user) return true
      if (permissions.includes('*')) return true
      if (Array.isArray(permissionToCheck)) {
        return permissionToCheck.some((ability) => permissions.includes(ability))
      }
      return permissions.includes(permissionToCheck)
    },
    [permissions, user.root_user]
  )

  return { hasPermission, permissions }
}

export default usePermissions
