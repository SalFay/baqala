import { useEffect } from 'react'
import { ConfigProvider, App as AntApp, theme } from 'antd'
import { useRecoilState } from 'recoil'
import { themeAtom } from '@/Helpers/atom'

const ThemeProvider = ({ children }) => {
  const [currentTheme] = useRecoilState(themeAtom)
  const isDark = currentTheme === 'dark'

  useEffect(() => {
    const root = document.documentElement
    if (isDark) {
      root.classList.add('dark')
      root.classList.remove('light')
    } else {
      root.classList.add('light')
      root.classList.remove('dark')
    }
  }, [isDark])

  return (
    <ConfigProvider
      theme={{
        algorithm: isDark ? theme.darkAlgorithm : theme.defaultAlgorithm,
        token: {
          colorPrimary: '#1890ff',
          borderRadius: 6,
        },
      }}
    >
      <AntApp>
        {children}
      </AntApp>
    </ConfigProvider>
  )
}

export default ThemeProvider
