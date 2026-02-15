import PersistentLayout from '@/Layouts/PersistentLayout';

/**
 * MainLayout - SparkCRM Pattern
 * Uses PersistentLayout as the base wrapper
 */
export default function MainLayout({ children }) {
    return (
        <PersistentLayout>
            {children}
        </PersistentLayout>
    );
}
