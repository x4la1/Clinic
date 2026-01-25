import React, { useEffect, useState } from 'react';
import { Card, Row, Col, Statistic, Spin, Empty } from 'antd';
import { useNavigate } from 'react-router-dom';
import { useAppStore } from '../../store';
import type { User, Staff, Appointment } from '../../types';
import styles from './AdminReportsPage.module.scss';
import { ROLE_IDS } from '../../utils/roles';
import { apiRequest } from '../../utils/api';

export const AdminReportsPage: React.FC = () => {
  const navigate = useNavigate();
  const { auth, clinics } = useAppStore();
  const [loading, setLoading] = useState(true);

  const [users, setUsers] = useState<User[]>([]);
  const [staff, setStaff] = useState<Staff[]>([]);
  const [appointments, setAppointments] = useState<Appointment[]>([]);

  useEffect(() => {
    if (!auth.user || auth.user.roleId !== ROLE_IDS.ADMIN) {
      navigate('/');
      return;
    }

    loadReportData();
  }, [auth.user, navigate]);

  const loadReportData = async () => {
    try {
      setLoading(true);
      const [
        usersResponse,
        staffResponse,
        appointmentsResponse
      ] = await Promise.all([
        apiRequest<{ users: User[] }>('/api/users'),
        apiRequest<Staff[]>('/api/staffs'),
        apiRequest<{ appointments: Appointment[] }>('/api/appointments/all')
      ]);

      setUsers(usersResponse.users);
      setStaff(staffResponse);
      setAppointments(appointmentsResponse.appointments || []);
      
    } catch (error: any) {
      console.error('Failed to load report data', error);
    } finally {
      setLoading(false);
    }
  };

  const getStatusName = (statusName: string) => {
    switch (statusName) {
      case 'SCHEDULED': return 'Запланировано';
      case 'COMPLETED': return 'Завершено';
      case 'CANCELED': return 'Отменено';
      default: return statusName;
    }
  };

  const totalPatients = users.filter(u => u.roleId === ROLE_IDS.PATIENT).length;
  const totalDoctors = staff.length; 
  const totalClinics = clinics.length;
  const totalAppointments = appointments.length;
  
  const confirmedAppointments = appointments.filter(a => a.status?.name === 'COMPLETED').length;
  const cancelledAppointments = appointments.filter(a => a.status?.name === 'CANCELED').length;
  const pendingAppointments = appointments.filter(a => a.status?.name === 'SCHEDULED').length;

  if (loading) {
    return (
      <div className={styles.loading}>
        <Spin size="large" />
      </div>
    );
  }

  return (
    <div className={styles.container}>
      <h1 className={styles.title}>Отчёты</h1>
      
      <Row gutter={[16, 16]} style={{ marginBottom: 24 }}>
        <Col xs={24} sm={12} md={6}>
          <Card>
            <Statistic title="Пациенты" value={totalPatients} />
          </Card>
        </Col>
        <Col xs={24} sm={12} md={6}>
          <Card>
            <Statistic title="Врачи" value={totalDoctors} />
          </Card>
        </Col>
        <Col xs={24} sm={12} md={6}>
          <Card>
            <Statistic title="Поликлиники" value={totalClinics} />
          </Card>
        </Col>
        <Col xs={24} sm={12} md={6}>
          <Card>
            <Statistic title="Всего записей" value={totalAppointments} />
          </Card>
        </Col>
      </Row>

      <Card title="Записи по статусам" style={{ marginBottom: 24 }}>
        <table className={styles.table}>
          <thead>
            <tr>
              <th>Статус</th>
              <th>Количество</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Завершено</td>
              <td>{confirmedAppointments}</td>
            </tr>
            <tr>
              <td>Отменено</td>
              <td>{cancelledAppointments}</td>
            </tr>
            <tr>
              <td>Запланировано</td>
              <td>{pendingAppointments}</td>
            </tr>
          </tbody>
        </table>
      </Card>

      <Card title="Статистика по врачам">
        {totalDoctors > 0 ? (
          <table className={styles.table}>
            <thead>
              <tr>
                <th>Врач</th>
                <th>Всего записей</th>
                <th>Завершено</th>
                <th>Отменено</th>
              </tr>
            </thead>
            <tbody>
              {staff.map(doctor => {
                const doctorApps = appointments.filter(a => a.staff?.id === doctor.id);
                const completed = doctorApps.filter(a => a.status?.name === 'COMPLETED').length;
                const cancelled = doctorApps.filter(a => a.status?.name === 'CANCELED').length;
                
                return (
                  <tr key={doctor.id}>
                    <td>{doctor.lastName} {doctor.firstName.charAt(0)}.</td>
                    <td>{doctorApps.length}</td>
                    <td>{completed}</td>
                    <td>{cancelled}</td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        ) : (
          <Empty description="Нет врачей" />
        )}
      </Card>
    </div>
  );
};