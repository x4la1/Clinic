import React, { useEffect, useState } from 'react';
import type { Clinic } from '../../types';
import { useAppStore } from '../../store';
import styles from './HomePage.module.scss';
import { ClinicCard } from '../../components/ClinicCard/ClinicCard';
import { apiRequest } from '../../utils/api';

export const HomePage: React.FC = () => {
  const { setClinics, clinics } = useAppStore();
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadClinics();
  }, []);

  const loadClinics = async () => {
    try {
      setLoading(true);

      const response = await apiRequest<{ clinics: Clinic[] }>('/api/clinics/all'); //ok
      setClinics(response.clinics || []);

    } catch (e) {
      console.error('Failed to load clinics', e);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className={styles.homePage}>
      <section className={styles.hero}>
        <div className={styles.heroContent}>
          <h1 className={styles.title}>Запись к врачу онлайн</h1>
          <p className={styles.subtitle}>
            Быстрая и удобная запись в лучшие поликлиники города
          </p>
        </div>
      </section>

      <section className={styles.content}>
        <h2 className={styles.sectionTitle}>Наши поликлиники</h2>

        {loading ? (
          <div className={styles.loading}>Загрузка поликлиник...</div>
        ) : (
          <div className={styles.clinicsGrid}>
            {clinics.map((clinic) => (
              <ClinicCard key={clinic.id} clinic={clinic} />
            ))}
          </div>
        )}
      </section>
    </div>
  );
};
