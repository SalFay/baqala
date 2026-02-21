import { Button, ConfigProvider, theme } from 'antd'
import { useRecoilValue } from 'recoil'
import { themeAtom } from '@/Helpers/atom.js'

const Button1 = ({ children, size = 'small', type, danger, ...props }) => {
  const { token } = theme.useToken()
  const currentTheme = useRecoilValue(themeAtom)
  const color = currentTheme === 'light' ? '#FFFFFF' : token.colorBgContainerDisabled

  // Handle 'danger' as a type (legacy support) or as a separate prop
  const isDanger = type === 'danger' || danger
  const buttonType = type === 'danger' ? 'default' : type

  return (
    <ConfigProvider
      theme={{
        components: {
          Button: {
            defaultBg: color,
            borderRadius: 8,
            defaultShadow: 'none',
            primaryShadow: 'none',
          },
        },
      }}
    >
      <Button size={size} type={buttonType} danger={isDanger} {...props}>
        {children}
      </Button>
    </ConfigProvider>
  )
}

export default Button1
