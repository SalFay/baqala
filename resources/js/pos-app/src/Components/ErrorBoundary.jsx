import { Component } from 'react';
import { Button, Result } from 'antd';

/**
 * Error Boundary component to catch and handle React errors gracefully
 */
class ErrorBoundary extends Component {
  constructor(props) {
    super(props);
    this.state = {
      hasError: false,
      error: null,
      errorInfo: null,
    };
  }

  static getDerivedStateFromError(error) {
    return { hasError: true, error };
  }

  componentDidCatch(error, errorInfo) {
    this.setState({ errorInfo });

    // Log error to console in development
    if (process.env.NODE_ENV === 'development') {
      console.error('Error caught by ErrorBoundary:', error);
      console.error('Component stack:', errorInfo?.componentStack);
    }

    // TODO: Send error to error tracking service (e.g., Sentry)
    // if (window.Sentry) {
    //   window.Sentry.captureException(error, { extra: errorInfo });
    // }
  }

  handleReload = () => {
    window.location.reload();
  };

  handleGoHome = () => {
    window.location.href = '/';
  };

  handleRetry = () => {
    this.setState({
      hasError: false,
      error: null,
      errorInfo: null,
    });
  };

  render() {
    if (this.state.hasError) {
      const isDevelopment = process.env.NODE_ENV === 'development';

      return (
        <div
          style={{
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            minHeight: '100vh',
            padding: 24,
            backgroundColor: '#f5f5f5',
          }}
        >
          <Result
            status="error"
            title="Something went wrong"
            subTitle="An unexpected error occurred. Please try again or contact support if the problem persists."
            extra={[
              <Button key="retry" type="primary" onClick={this.handleRetry}>
                Try Again
              </Button>,
              <Button key="reload" onClick={this.handleReload}>
                Reload Page
              </Button>,
              <Button key="home" onClick={this.handleGoHome}>
                Go to Home
              </Button>,
            ]}
          >
            {isDevelopment && this.state.error && (
              <div
                style={{
                  marginTop: 24,
                  padding: 16,
                  backgroundColor: '#fff1f0',
                  borderRadius: 8,
                  textAlign: 'left',
                  maxWidth: 600,
                  margin: '24px auto 0',
                }}
              >
                <h4 style={{ color: '#cf1322', marginBottom: 8 }}>
                  Error Details (Development Only)
                </h4>
                <pre
                  style={{
                    fontSize: 12,
                    whiteSpace: 'pre-wrap',
                    wordBreak: 'break-word',
                    color: '#595959',
                    margin: 0,
                  }}
                >
                  {this.state.error.toString()}
                  {this.state.errorInfo?.componentStack}
                </pre>
              </div>
            )}
          </Result>
        </div>
      );
    }

    return this.props.children;
  }
}

export default ErrorBoundary;
