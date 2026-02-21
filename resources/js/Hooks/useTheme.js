/**
 * useTheme Hook
 *
 * Provides theme state and toggle functionality.
 * Wraps the Recoil themeAtom for easier consumption.
 */

import { useRecoilState } from 'recoil'
import { themeAtom } from '../Helpers/atom'

export function useTheme() {
  const [theme, setTheme] = useRecoilState(themeAtom)

  const isDark = theme === 'dark'

  const toggleTheme = () => {
    setTheme((current) => (current === 'light' ? 'dark' : 'light'))
  }

  const setDarkMode = (dark) => {
    setTheme(dark ? 'dark' : 'light')
  }

  return {
    theme,
    isDark,
    isLight: !isDark,
    toggleTheme,
    setTheme,
    setDarkMode,
  }
}

export default useTheme
