import { useMemo, useState } from 'react'
import {
  Alert,
  Card,
  Checkbox,
  Collapse,
  Input,
  Space,
  Table,
  theme,
  Tooltip,
  Typography,
} from 'antd'
import {
  ArrowLeftOutlined,
  SaveOutlined,
  SecurityScanOutlined,
} from '@ant-design/icons'
import { Head, usePage } from '@inertiajs/react'
import useIsMobile from '@/Hooks/useIsMobile.js'
import { useRecoilValue } from 'recoil'
import { themeAtom } from '@/Helpers/atom.js'
import { storePermissions } from '@/Helpers/api/roleService.js'
import { transformPermissions } from '@/Helpers/transformers'
import PageContent from '@/Components/PageContent.jsx'
import PersistentLayout from '@/Layouts/PersistentLayout.jsx'
import GlobalPageHeader from '@/Components/GlobalPageHeader.jsx'
import { handleApiError, handleApiSuccess } from '@/Helpers/CONSTANT'
import usePermissions from '@/Helpers/Context/usePermissions.js'
import { router } from '@inertiajs/react'

const { Text } = Typography

const Permissions = () => {
  const { roles, permissions, previousPermissions } = usePage().props
  const { hasPermission } = usePermissions()
  const { token } = theme.useToken()
  const isMobile = useIsMobile()
  const currentTheme = useRecoilValue(themeAtom)

  const [checkedPermissions, setCheckedPermissions] = useState(previousPermissions || {})
  const [loading, setLoading] = useState(false)
  const [searchText, setSearchText] = useState('')

  // Filter permissions by search text
  const filteredPermissions = useMemo(() => {
    if (!searchText.trim()) {
      return permissions
    }

    const lowerSearch = searchText.toLowerCase()

    return Object.keys(permissions).reduce((acc, key) => {
      const permissionGroup = permissions[key]

      // Check if the module title matches
      const titleMatches = permissionGroup.title.toLowerCase().includes(lowerSearch)

      // Check if any individual permission matches
      const individualPermissions = permissionGroup.permissions || {}
      const hasMatchingPermission = Object.keys(individualPermissions).some(permKey => {
        const permission = individualPermissions[permKey]
        return permission.title.toLowerCase().includes(lowerSearch) ||
               permKey.toLowerCase().includes(lowerSearch)
      })

      // Include the permission group if title matches OR any individual permission matches
      if (titleMatches || hasMatchingPermission) {
        if (!titleMatches && hasMatchingPermission) {
          const filteredGroup = { ...permissionGroup }
          filteredGroup.permissions = Object.keys(individualPermissions).reduce((permAcc, permKey) => {
            const permission = individualPermissions[permKey]
            if (permission.title.toLowerCase().includes(lowerSearch) ||
                permKey.toLowerCase().includes(lowerSearch)) {
              permAcc[permKey] = permission
            }
            return permAcc
          }, {})
          acc[key] = filteredGroup
        } else {
          acc[key] = permissionGroup
        }
      }

      return acc
    }, {})
  }, [searchText, permissions])


  const handleCheckboxChange = (roleId, permission, checked) => {
    setCheckedPermissions(prevState => {
      const updatedPermissions = { ...prevState }
      if (checked) {
        updatedPermissions[roleId] = [...(updatedPermissions[roleId] || []), permission]
      } else {
        updatedPermissions[roleId] = (updatedPermissions[roleId] || []).filter(item => item !== permission)
      }
      return updatedPermissions
    })
  }

  const handleCheckAllChange = (roleId, checked, moduleKey) => {
    setCheckedPermissions(prevState => {
      const updatedPermissions = { ...prevState }
      const currentTabPermissions = filteredPermissions[moduleKey]

      if (!currentTabPermissions) return updatedPermissions

      let permissionsToUpdate = Object.keys(currentTabPermissions.permissions || {})

      if (checked) {
        updatedPermissions[roleId] = [
          ...new Set([...(updatedPermissions[roleId] || []), ...permissionsToUpdate]),
        ]
      } else {
        updatedPermissions[roleId] = (updatedPermissions[roleId] || []).filter(
          permission => !permissionsToUpdate.includes(permission),
        )
      }

      return updatedPermissions
    })
  }

  const handleSubmit = async () => {
    setLoading(true)
    try {
      const structuredPermissions = transformPermissions(checkedPermissions)
      if (Object.keys(checkedPermissions).length !== 0) {
        const response = await storePermissions(structuredPermissions)
        handleApiSuccess(response)
      }
    } catch (error) {
      handleApiError(error)
    } finally {
      setLoading(false)
    }
  }

  const generateColumns = (moduleKey) => {
    const sortedRoles = [...roles].sort((a, b) => a.name.localeCompare(b.name))

    return [
      {
        title: (
          <Space>
            <SecurityScanOutlined />
            <Text strong>Permissions</Text>
          </Space>
        ),
        dataIndex: 'permission',
        key: 'permission',
        fixed: 'left',
        width: 300,
        render: (text) => (
          <Tooltip title={text}>
            <Text strong style={{
              display: 'block',
              overflow: 'hidden',
              textOverflow: 'ellipsis',
              whiteSpace: 'nowrap',
              maxWidth: '100%'
            }}>
              {text}
            </Text>
          </Tooltip>
        ),
      },
      ...sortedRoles.map(role => {
        const currentTabPermissions = filteredPermissions[moduleKey]?.permissions || {}

        // Calculate checkbox states
        const tabPermissions = Object.keys(currentTabPermissions)
        let permissionsToCheck = [...tabPermissions]

        const checkedInTab = permissionsToCheck.filter(p => checkedPermissions[role.id]?.includes(p))
        const allChecked = permissionsToCheck.length > 0 && checkedInTab.length === permissionsToCheck.length
        const someChecked = checkedInTab.length > 0 && checkedInTab.length < permissionsToCheck.length

        return {
          title: (
            <div style={{ padding: '2px 0' }}>
              <Space direction="vertical" size={1} style={{ width: '100%' }}>
                <Text strong style={{
                  fontSize: '11px',
                  display: 'block',
                  textAlign: 'center',
                  lineHeight: '1.1',
                  wordBreak: 'break-word'
                }}>
                  {role.name}
                </Text>
                <div style={{ textAlign: 'center' }}>
                  <Tooltip title={allChecked ? 'Uncheck all' : 'Check all'}>
                    <Checkbox
                      onChange={e => handleCheckAllChange(role.id, e.target.checked, moduleKey)}
                      checked={allChecked}
                      indeterminate={someChecked}
                    />
                  </Tooltip>
                </div>
              </Space>
            </div>
          ),
          dataIndex: role.id,
          key: role.id,
          width: 110,
          align: 'center',
        }
      }),
    ]
  }

  const renderTableData = objectPermissions => {
    const data = []

    Object.keys(objectPermissions).forEach(objectKey => {
      const object = objectPermissions[objectKey]

      // Regular permissions
      Object.keys(object.permissions || {}).forEach(permissionKey => {
        const permission = object.permissions[permissionKey]
        const checkboxes = {}

        roles.forEach(role => {
          const isChecked = checkedPermissions[role.id]?.includes(permissionKey)
          checkboxes[role.id] = (
            <Tooltip title={isChecked ? 'Remove permission' : 'Grant permission'}>
              <Checkbox
                onChange={e => handleCheckboxChange(role.id, permissionKey, e.target.checked)}
                checked={isChecked}
              />
            </Tooltip>
          )
        })

        data.push({
          key: permissionKey,
          permission: permission.title,
          ...checkboxes,
        })
      })
    })

    return data
  }

  const collapseItems = Object.keys(filteredPermissions).map((key) => {
    const permissionObj = filteredPermissions[key]
    const totalWidth = Math.max(roles.length * 110 + 300, 1000)
    const tableHeight = 'calc(100vh - 280px)'

    return {
      key: key,
      label: (
        <Space>
          <Text strong>{permissionObj.title}</Text>
        </Space>
      ),
      children: (
        <Table
          columns={generateColumns(key)}
          dataSource={renderTableData({ [key]: permissionObj })}
          pagination={false}
          size="small"
          bordered
          scroll={{
            x: totalWidth,
            y: tableHeight,
          }}
        />
      ),
    }
  })

  // Permission check - root users bypass this check
  if (!hasPermission('access all permissions')) {
    return (
      <PageContent title="Permission Management">
        <Head title="Permission Management" />
        <Card>
          <Alert
            message="Access Denied"
            description="You don't have permission to manage system permissions."
            type="error"
            showIcon
          />
        </Card>
      </PageContent>
    )
  }

  return (
    <PageContent
      title="Permission Management"
      canvas={true}
    >
      <Head title="Permission Management" />
      <GlobalPageHeader
        title="Permission Management"
        parentPageTitle="Manage Roles"
        parentPageRoute="role.index"
        searchConfig={{
          value: searchText,
          onChange: e => setSearchText(e.target.value),
          placeholder: 'Search modules or permissions...',
          resultText: searchText.trim()
            ? Object.keys(filteredPermissions).length === 0
              ? 'No results found'
              : `Showing ${Object.keys(filteredPermissions).length} module(s)`
            : null,
        }}
        actionButtons={[
          {
            title: 'Back',
            icon: <ArrowLeftOutlined />,
            onClick: () => router.visit(route('role.index')),
            hasPermission: true,
            showButton: true,
          },
          {
            title: 'Save',
            icon: <SaveOutlined />,
            onClick: handleSubmit,
            hasPermission: true,
            showButton: true,
            disabled: loading,
          },
        ]}
      />

      {/* Scrollable Collapse Content */}
      <div
        style={{
          background: currentTheme === 'dark' ? '#191919' : '#f6f6f6',
          borderRadius: '10px',
          padding: '10px',
          border: `1px solid ${token.colorBorderSecondary}`,
          margin: isMobile ? '10px' : '12px 0 0 0',
          height: 'calc(100vh - 230px)',
          overflow: 'auto',
        }}
      >
        <Collapse
          accordion
          items={collapseItems}
          defaultActiveKey={[Object.keys(filteredPermissions)[0]]}
          style={{ background: 'transparent' }}
        />
      </div>
    </PageContent>
  )
}

// Define persistent layout - Header and Sidebar will stay mounted
Permissions.layout = page => <PersistentLayout>{page}</PersistentLayout>

export default Permissions
