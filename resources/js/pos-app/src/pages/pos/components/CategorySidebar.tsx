import { Button, Tooltip, Badge } from 'antd';
import { AppstoreOutlined, TagOutlined } from '@ant-design/icons';
import { useTheme } from '@/contexts/ThemeContext';

interface Category {
  id: number;
  name: string;
  code: string;
  parent_id?: number;
  image?: string;
  product_count?: number;
}

interface CategorySidebarProps {
  categories: Category[];
  selectedCategoryId: number | null;
  onSelectCategory: (categoryId: number | null) => void;
  loading?: boolean;
}

export function CategorySidebar({
  categories,
  selectedCategoryId,
  onSelectCategory,
  loading = false,
}: CategorySidebarProps) {
  const { isDark, colors, isRTL } = useTheme();

  // Get root categories (no parent)
  const rootCategories = categories.filter(c => !c.parent_id);

  const buttonStyle = (isSelected: boolean) => ({
    width: '100%',
    height: 'auto',
    minHeight: 48,
    padding: '8px 12px',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'flex-start',
    textAlign: 'left' as const,
    whiteSpace: 'normal' as const,
    backgroundColor: isSelected
      ? colors.primaryColor
      : isDark ? '#262626' : '#fff',
    borderColor: isSelected
      ? colors.primaryColor
      : isDark ? '#434343' : '#d9d9d9',
    color: isSelected ? '#fff' : isDark ? '#fff' : '#000',
  });

  return (
    <div
      className="category-sidebar"
      style={{
        width: '100%',
        height: '100%',
        backgroundColor: isDark ? '#1f1f1f' : '#fafafa',
        borderRight: isRTL ? 'none' : `1px solid ${isDark ? '#303030' : '#f0f0f0'}`,
        borderLeft: isRTL ? `1px solid ${isDark ? '#303030' : '#f0f0f0'}` : 'none',
        padding: 8,
        overflowY: 'auto',
        display: 'flex',
        flexDirection: 'column',
        gap: 6,
      }}
    >
      {/* All Products Button */}
      <Button
        type={selectedCategoryId === null ? 'primary' : 'default'}
        icon={<AppstoreOutlined />}
        onClick={() => onSelectCategory(null)}
        style={buttonStyle(selectedCategoryId === null)}
        loading={loading}
      >
        <span style={{ marginLeft: 8, flex: 1 }}>
          All Products
        </span>
      </Button>

      {/* Category Buttons */}
      {rootCategories.map(category => (
        <Tooltip
          key={category.id}
          title={category.product_count !== undefined ? `${category.product_count} products` : undefined}
          placement={isRTL ? 'left' : 'right'}
        >
          <Badge
            count={category.product_count}
            size="small"
            style={{ backgroundColor: colors.primaryColor }}
            offset={[-5, 0]}
          >
            <Button
              type={selectedCategoryId === category.id ? 'primary' : 'default'}
              icon={category.image ? (
                <img
                  src={category.image}
                  alt={category.name}
                  style={{
                    width: 24,
                    height: 24,
                    objectFit: 'cover',
                    borderRadius: 4,
                  }}
                />
              ) : (
                <TagOutlined />
              )}
              onClick={() => onSelectCategory(category.id)}
              style={buttonStyle(selectedCategoryId === category.id)}
            >
              <span style={{
                marginLeft: 8,
                flex: 1,
                overflow: 'hidden',
                textOverflow: 'ellipsis',
                display: '-webkit-box',
                WebkitLineClamp: 2,
                WebkitBoxOrient: 'vertical',
              }}>
                {category.name}
              </span>
            </Button>
          </Badge>
        </Tooltip>
      ))}

      {/* Empty State */}
      {rootCategories.length === 0 && !loading && (
        <div
          style={{
            textAlign: 'center',
            padding: 20,
            color: isDark ? '#8c8c8c' : '#999',
          }}
        >
          No categories
        </div>
      )}
    </div>
  );
}

export default CategorySidebar;
