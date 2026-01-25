import React from 'react';
import { Header } from '../header/header.tsx';
import styles from './Layout.module.scss';

interface LayoutProps {
  children: React.ReactNode;
}

export const Layout: React.FC<LayoutProps> = ({ children }) => {
  return (
    <div className={styles.layout}>
      
      <Header />
      <main className={styles.main}>
        {children}
      </main>
    </div>
  );
};