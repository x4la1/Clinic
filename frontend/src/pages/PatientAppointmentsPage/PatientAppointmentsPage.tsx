import React, { useEffect, useState } from 'react';
import { Card, Table, Typography, Spin, Empty, Tag, Button, Modal, message } from 'antd';
import { LeftOutlined, CheckCircleOutlined } from '@ant-design/icons';
import { Link, useNavigate } from 'react-router-dom';
import dayjs from 'dayjs';
import { useAppStore } from '../../store';
import type { Appointment, Staff, Clinic } from '../../types';
import styles from './PatientAppointmentsPage.module.scss';
import { ROLE_IDS } from '../../utils/roles';
import { apiRequest } from '../../utils/api';

const { Title } = Typography;

export const PatientAppointmentsPage: React.FC = () => {
  const navigate = useNavigate();
  const user = useAppStore((state) => state.auth.user);
  const [appointments, setAppointments] = useState<Appointment[]>([]);
  const [loading, setLoading] = useState(true);
  const [staff, setStaff] = useState<Staff[]>([]);
  const [clinics, setClinics] = useState<Clinic[]>([]);

  useEffect(() => {
    if (!user || user.roleId !== ROLE_IDS.PATIENT) {
      navigate('/');
      return;
    }

    loadAppointments();
  }, [user, navigate]);

  const loadAppointments = async () => {
    try {
      setLoading(true);

      const [
        appointmentsResponse,
        staffResponse,
        clinicsResponse
      ] = await Promise.all([
        apiRequest<{ appointments: Appointment[] }>(`/api/user/appointments/${user!.id}`), //neok
        apiRequest<Staff[]>('/api/staffs'), //ok
        apiRequest<{ clinics: Clinic[] }>('/api/clinics/all')//ok
      ]);

      setAppointments(appointmentsResponse.appointments || []);
      setStaff(staffResponse);
      setClinics(clinicsResponse.clinics || []);

    } catch (e) {
      console.error('Failed to load appointments', e);
      setAppointments([]);
    } finally {
      setLoading(false);
    }
  };

  const handleCancel = (record: Appointment) => {
    Modal.confirm({
      title: 'Отменить запись?',
      icon: <CheckCircleOutlined style={{ color: '#ff4d4f' }} />,
      content: `Вы уверены, что хотите отменить запись на ${dayjs(record.date).format('DD.MM.YYYY HH:mm')}?`,
      okText: 'Да, отменить',
      okType: 'danger',
      cancelText: 'Нет',
      onOk: async () => {
        try {
          await apiRequest('/api/appointment/cancel', { //ne ok
            method: 'POST',
            body: JSON.stringify({ id: record.id }),
          });
          message.success('Запись отменена');
          loadAppointments();
        } catch (e) {
          message.error('Не удалось отменить запись');
          console.error(e);
        }
      },
    });
  };

  const getDoctorName = (staffId: number) => {
    const doctor = staff.find(s => s.id === staffId);
    return doctor ? `${doctor.lastName} ${doctor.firstName} ${doctor.patronymic || ''}` : `ID: ${staffId}`;
  };

  const getClinicName = (clinicId: number) => {
    const clinic = clinics.find(c => c.id === clinicId);
    return clinic ? clinic.name : `ID: ${clinicId}`;
  };

  const getStatusTag = (statusName: string) => {
    let color = 'default';
    let text = statusName;

    switch (statusName) {
      case 'SCHEDULED':
        color = 'blue';
        text = 'Запланировано';
        break;
      case 'COMPLETED':
        color = 'green';
        text = 'Завершено';
        break;
      case 'CANCELED':
        color = 'red';
        text = 'Отменено';
        break;
    }

    return <Tag color={color}>{text}</Tag>;
  };

  const showResult = (result: string) => {
    Modal.info({
      title: 'Результат приёма',
      content: result || 'Результат ещё не добавлен',
      width: 500,
    });
  };

  const columns = [
    {
      title: 'Дата',
      key: 'date',
      render: (_: any, record: Appointment) => dayjs(record.date).format('DD.MM.YYYY HH:mm'),
    },
    {
      title: 'Врач',
      key: 'doctor',
      render: (_: any, record: Appointment) => getDoctorName(record.staff?.id || 0),
    },
    {
      title: 'Поликлиника',
      key: 'clinic',
      render: (_: any, record: Appointment) => {
        const staffMember = staff.find(s => s.id === record.staff?.id);
        const clinicId = staffMember?.clinic?.id;
        return clinicId ? getClinicName(clinicId) : '—';
      },
    },
    {
      title: 'Статус',
      key: 'status',
      render: (_: any, record: Appointment) => getStatusTag(record.status?.name || ''),
    },
    {
      title: 'Действия',
      key: 'actions',
      render: (_: any, record: Appointment) => (
        <>
          {record.status?.name !== 'CANCELED' && (
            <Button
              type="link"
              danger
              onClick={() => handleCancel(record)}
              size="small"
            >
              Отменить
            </Button>
          )}
          {record.status?.name === 'COMPLETED' && (
            <Button
              type="link"
              onClick={() => showResult(record.result)}
              size="small"
            >
              Результат
            </Button>
          )}
        </>
      ),
    },
  ];

  if (loading) {
    return (
      <div className={styles.loading}>
        <Spin size="large" />
      </div>
    );
  }

  return (
    <div className={styles.container}>
      <Card className={styles.card}>
        <Link to="/patient" className={styles.backLink}>
          <LeftOutlined /> Назад в личный кабинет
        </Link>

        <Title level={2} className={styles.title}>Мои записи</Title>

        {appointments.length === 0 ? (
          <Empty
            description="У вас пока нет записей к врачу"
            image={Empty.PRESENTED_IMAGE_SIMPLE}
          />
        ) : (
          <Table
            dataSource={appointments}
            columns={columns}
            rowKey="id"
            pagination={{ pageSize: 10 }}
          />
        )}
      </Card>
    </div>
  );
};
