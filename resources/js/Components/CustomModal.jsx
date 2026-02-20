import { Modal, Flex, Typography, Button, Space, theme } from 'antd';
import { CloseOutlined } from '@ant-design/icons';

const { Text } = Typography;
const { useToken } = theme;

/**
 * CustomModal - Modal wrapper component similar to SparkCRM
 *
 * @param {Object} props
 * @param {boolean} props.open - Modal visibility
 * @param {Function} props.onCancel - Close handler
 * @param {string} props.title - Modal title
 * @param {React.ReactNode} props.children - Modal content
 * @param {number} props.width - Modal width (default: 600)
 * @param {string} props.saveText - Save button text
 * @param {boolean} props.loading - Save button loading state
 * @param {boolean} props.disableSave - Disable save button
 * @param {Function} props.onSave - Save handler
 * @param {boolean} props.showSave - Show save button
 * @param {React.ReactNode} props.extraFooter - Extra footer content
 * @param {React.ReactNode} props.footer - Custom footer (null to hide)
 * @param {boolean} props.destroyOnClose - Destroy on close
 */
const CustomModal = ({
  open,
  onCancel,
  title,
  children,
  width = 600,
  saveText = 'Save',
  loading = false,
  disableSave = false,
  onSave,
  showSave = false,
  extraFooter = null,
  footer = undefined,
  destroyOnClose = true,
  maskClosable = true,
  style,
}) => {
  const { token } = useToken();

  // Build footer
  const resolvedFooter = footer !== undefined
    ? footer
    : (showSave || extraFooter)
      ? (
        <Flex justify="flex-end">
          <Space>
            {extraFooter}
            {showSave && (
              <Button
                type="primary"
                onClick={onSave}
                loading={loading}
                disabled={disableSave}
              >
                {saveText}
              </Button>
            )}
          </Space>
        </Flex>
      )
      : null;

  return (
    <Modal
      width={width}
      centered
      open={open}
      onCancel={onCancel}
      closable={false}
      maskClosable={maskClosable}
      destroyOnClose={destroyOnClose}
      style={style || { borderRadius: 8 }}
      title={
        <Flex
          align="center"
          justify="space-between"
          style={{
            padding: '12px 0',
            borderBottom: `1px solid ${token.colorBorderSecondary}`,
            marginBottom: 16,
          }}
        >
          <Text strong style={{ fontSize: 16 }}>{title}</Text>
          <Button
            type="text"
            icon={<CloseOutlined />}
            onClick={onCancel}
            style={{ color: token.colorTextSecondary }}
          />
        </Flex>
      }
      footer={resolvedFooter}
    >
      {children}
    </Modal>
  );
};

export default CustomModal;
