import React from 'react';
import { Link } from 'react-router-dom';
import type { Clinic } from '../../types';
import styles from './ClinicCard.module.scss';

interface ClinicCardProps {
  clinic: Clinic;
}

export const ClinicCard: React.FC<ClinicCardProps> = ({ clinic }) => {
  return (
    <Link to={`/clinics/${clinic.id}`} className={styles.card}>
      <div className={styles.content}>
        <h3 className={styles.name}>{clinic.name}</h3>
        <p className={styles.address}>{clinic.address}</p>
        <p className={styles.contact}>
          📞 {clinic.phone}
        </p>
        <p className={styles.contact}>
          ✉️ {clinic.email}
        </p>
      </div>
    </Link>
  );
};