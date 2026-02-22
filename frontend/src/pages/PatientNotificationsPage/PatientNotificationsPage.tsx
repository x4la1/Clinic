import React, { useEffect, useState } from 'react';
import { Card, Typography, Spin, Empty, List, Badge, Button, message } from 'antd';
import { LeftOutlined, BellOutlined } from '@ant-design/icons';
import { Link, useNavigate } from 'react-router-dom';
import dayjs from 'dayjs';
import { useAppStore } from '../../store';
import type { Appointment, Staff } from '../../types';
import styles from './PatientNotificationsPage.module.scss';
import { ROLE_IDS } from '../../utils/roles';
import { apiRequest } from '../../utils/api';

const { Title, Text } = Typography;

interface Notification {
  id: number;
  type: 'upcoming_appointment';
  title: string;
  message: string;
  date: string;
  isRead: boolean;
}

export const PatientNotificationsPage: React.FC = () => {
    const navigate = useNavigate();
    const user = useAppStore((state) => state.auth.user);
    const [notifications, setNotifications] = useState<Notification[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!user || user.roleId !== ROLE_IDS.PATIENT) {
            navigate('/');
            return;
        }

        loadNotifications();
    }, [user, navigate]);

    const loadNotifications = async () => {
        try {
            setLoading(true);

            const [
                appointmentsResponse,
                staffResponse
            ] = await Promise.all([
                apiRequest<{ appointments: Appointment[] }>(`/api/user/appointments/${user!.id}`),
                apiRequest<Staff[]>('/api/staffs')
            ]);

            const appointments = appointmentsResponse.appointments || [];
            const staff = staffResponse;
            const now = dayjs();
            const upcomingNotifications: Notification[] = [];

            appointments.forEach(app => {
                if (app.status.name === 'CANCELED' || app.status.name === 'COMPLETED') {
                    return;
                }

                const doctor = staff.find(s => s.id === app.staff?.id);
                const appointmentDate = dayjs(app.date);
                const diffHours = appointmentDate.diff(now, 'hour');

                if (diffHours > 0 && diffHours <= 24) {
                    let title = 'Предстоящий приём';
                    let messageText = `У вас запись к врачу ${doctor ? `${doctor.lastname} ${doctor.firstname}` : 'врачу'} ${appointmentDate.format('DD.MM.YYYY HH:mm')}`;
                    
                    if (diffHours <= 2) {
                        title = 'Скоро приём!';
                        messageText = `Ваш приём начнётся через ${Math.abs(diffHours)} час(а/ов)`;
                    }

                    upcomingNotifications.push({
                        id: app.id,
                        type: 'upcoming_appointment',
                        title,
                        message: messageText,
                        date: app.date,
                        isRead: false,
                    });
                }
            });

            upcomingNotifications.sort((a, b) =>
                dayjs(a.date).valueOf() - dayjs(b.date).valueOf()
            );

            setNotifications(upcomingNotifications);
        } catch (e) {
            console.error('Failed to load notifications', e);
            message.error('Ошибка загрузки уведомлений');
            setNotifications([]);
        } finally {
            setLoading(false);
        }
    };

    const markAllAsRead = () => {
        setNotifications(prev => prev.map(n => ({ ...n, isRead: true })));
        message.success('Все уведомления прочитаны');
    };

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

                <div className={styles.header}>
                    <Title level={2} className={styles.title}>
                        Уведомления
                    </Title>
                    {notifications.some(n => !n.isRead) && (
                        <Button size="small" onClick={markAllAsRead}>
                            Отметить всё как прочитанное
                        </Button>
                    )}
                </div>

                {notifications.length === 0 ? (
                    <Empty
                        description="Нет уведомлений"
                        image={<BellOutlined style={{ fontSize: 48, color: '#d9d9d9' }} />}
                    />
                ) : (
                    <List
                        dataSource={notifications}
                        renderItem={(notification) => (
                            <List.Item key={notification.id} className={styles.notificationItem}>
                                <Badge dot={!notification.isRead} offset={[-8, 8]}>
                                    <div className={styles.notificationContent}>
                                        <Text strong>{notification.title}</Text>
                                        <Text type="secondary">{notification.message}</Text>
                                        <Text type="secondary" className={styles.notificationDate}>
                                            {dayjs(notification.date).format('DD.MM.YYYY HH:mm')}
                                        </Text>
                                    </div>
                                </Badge>
                            </List.Item>
                        )}
                    />
                )}
            </Card>
        </div>
    );
};