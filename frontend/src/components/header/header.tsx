import React from 'react';
import { Link } from 'react-router-dom';
import { useAppStore } from '../../store';
import styles from './Header.module.scss';
import { ROLE_IDS } from '../../utils/roles';

export const Header: React.FC = () => {
  const { auth } = useAppStore();

  return (
    <header className={styles.header}>
      <div className={styles.container}>
        <Link to="/" className={styles.logo}>
          МедЗапись
        </Link>

        <nav className={styles.nav}>
          <Link to="/" className={styles.navLink}>Главная</Link>
          {auth.isAuthenticated ? (
            <>
              {auth.user?.roleId === ROLE_IDS.PATIENT && (
                <>
                  <Link to="/doctors" className={styles.navLink}>Поиск врачей</Link>
                  <Link to="/patient" className={styles.navLink}>Личный кабинет</Link>
                </>
              )}
              {auth.user?.roleId === ROLE_IDS.ADMIN && (
                <>
                  <Link to="/admin" className={styles.navLink}>Админ-панель</Link>
                  <Link to="/admin/reports" className={styles.navLink}>Отчёты</Link>
                  <Link to="/admin/reviews" className={styles.navLink}>Отзывы</Link>
                </>
              )}
            </>
          ) : (
            <>
              <Link to="/login" className={styles.navLink}>Вход</Link>
              <Link to="/register" className={styles.navLink}>Регистрация</Link>
            </>
          )}
        </nav>
      </div>
    </header>
  );
};