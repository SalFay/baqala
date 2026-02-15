import { Button, Dropdown } from 'antd';
import { MoreOutlined } from '@ant-design/icons';

export default function ActionDropdown({ items, trigger = ['click'] }) {
  if (!items || items.length === 0) return null;

  return (
    <Dropdown menu={{ items }} trigger={trigger}>
      <Button type="text" icon={<MoreOutlined />} />
    </Dropdown>
  );
}
