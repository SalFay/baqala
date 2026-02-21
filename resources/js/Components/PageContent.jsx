import { PageContainer } from '@ant-design/pro-components'
import { Head } from '@inertiajs/react'
import { theme } from 'antd'

const { useToken } = theme

const PageContent = ({
  children,
  title = 'Baqala POS',
  pageTitle,
  subtitle,
  actions,
  loading = false,
  canvas = false,
}) => {
  const { token } = useToken()

  return (
    <>
      <Head title={title} />
      <PageContainer
        loading={loading}
        title={canvas ? null : <span style={{ color: token.colorText }}>{pageTitle || title}</span>}
        extra={actions}
        subTitle={canvas ? null : <span style={{ color: token.colorText }}>{subtitle}</span>}
        breadcrumb={null}
        className={canvas ? 'page-container' : 'page-container-mobile'}
        header={canvas ? { title: null } : undefined}
        style={{
          height: 'calc(100dvh - 56px)',
          overflowY: 'auto',
          borderRadius: '6px',
          backgroundColor: token.colorBgBase,
          margin: '0 10px 0 0',
        }}
      >
        {children}
      </PageContainer>
    </>
  )
}

export default PageContent
