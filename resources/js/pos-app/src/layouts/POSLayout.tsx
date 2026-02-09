import { ReactNode } from 'react';

interface POSLayoutProps {
  children: ReactNode;
}

export default function POSLayout({ children }: POSLayoutProps) {
  return <div className="pos-container">{children}</div>;
}
