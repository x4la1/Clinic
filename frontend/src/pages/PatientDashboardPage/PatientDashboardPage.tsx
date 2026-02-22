import React, { useEffect } from 'react';
import { Button, Card, Typography, Row, Col, Spin, message } from 'antd';
import { UserOutlined, CalendarOutlined, HistoryOutlined, LogoutOutlined, MailOutlined, PhoneOutlined, BellOutlined } from '@ant-design/icons';
import { Link, useNavigate } from 'react-router-dom';
import { useAppStore } from '../../store';
import styles from './PatientDashboardPage.module.scss';
import { ROLE_IDS } from '../../utils/roles';

const { Title, Text } = Typography;

export const PatientDashboardPage: React.FC = () => {
    const navigate = useNavigate();
    const user = useAppStore((state) => state.auth.user);
    const setUser = useAppStore((state) => state.setUser);

    useEffect(() => {
        if (!user || user.roleId !== ROLE_IDS.PATIENT) {
            navigate('/');
        }
    }, [user, navigate]);

    const handleLogout = () => {
        localStorage.removeItem('user');
        setUser(null);
        message.success('Вы вышли из системы');
        navigate('/');
    };

    if (!user || user.roleId !== ROLE_IDS.PATIENT) {
        return (
            <div className={styles.loading}>
                <Spin size="large" />
            </div>
        );
    }

    return (
        <div className={styles.container}>
            <Row gutter={[24, 24]} justify="center">
                <Col xs={24} md={20} lg={16}>
                    <div className={styles.header}>
                        <Title level={2}>Личный кабинет</Title>
                        <Text>Добро пожаловать, {user.firstName || 'Пациент'}!</Text>
                    </div>

                    <Row gutter={[16, 16]} style={{ marginBottom: 24 }}>
                        <Col xs={24} sm={12}>
                            <Card className={styles.actionCard} hoverable>
                                <Link to="/patient/book" className={styles.cardLink}>
                                    <CalendarOutlined className={styles.cardIcon} />
                                    <div>
                                        <Title level={5} className={styles.cardTitle}>Записаться к врачу</Title>
                                        <Text>Выберите поликлинику, отделение и врача</Text>
                                    </div>
                                </Link>
                            </Card>
                        </Col>

                        <Col xs={24} sm={12}>
                            <Card className={styles.actionCard} hoverable>
                                <Link to="/patient/appointments" className={styles.cardLink}>
                                    <HistoryOutlined className={styles.cardIcon} />
                                    <div>
                                        <Title level={5} className={styles.cardTitle}>Мои записи</Title>
                                        <Text>Просмотр текущих и прошедших записей</Text>
                                    </div>
                                </Link>
                            </Card>
                        </Col>
                        <Col xs={24} sm={12}>
                            <Card className={styles.actionCard} hoverable>
                                <Link to="/patient/notifications" className={styles.cardLink}>
                                    <BellOutlined className={styles.cardIcon} />
                                    <div>
                                        <Title level={5} className={styles.cardTitle}>Уведомления</Title>
                                        <Text>О предстоящих приёмах</Text>
                                    </div>
                                </Link>
                            </Card>
                        </Col>
                    </Row>
                    <Card className={styles.profileCard}>
                        <div className={styles.profileHeader}>
                            <UserOutlined className={styles.profileIcon} />
                            <Title level={4} style={{ margin: 0 }}>Мои данные</Title>
                        </div>
                        <div className={styles.profileInfo}>
                            <div className={styles.infoRow}>
                                <Text strong>Фамилия:</Text>
                                <Text>{user.lastName || '—'}</Text>
                            </div>
                            <div className={styles.infoRow}>
                                <Text strong>Имя:</Text>
                                <Text>{user.firstName || '—'}</Text>
                            </div>
                            <div className={styles.infoRow}>
                                <Text strong>Отчество:</Text>
                                <Text>{user.patronymic || '—'}</Text>
                            </div>
                            <div className={styles.infoRow}>
                                <Text strong>Телефон:</Text>
                                <Text>
                                    <PhoneOutlined style={{ marginRight: 6 }} />
                                    {user.phone}
                                </Text>
                            </div>
                            <div className={styles.infoRow}>
                                <Text strong>Email:</Text>
                                <Text>
                                    <MailOutlined style={{ marginRight: 6 }} />
                                    {user.login}
                                </Text>
                            </div>
                        </div>
                    </Card>

                    <div className={styles.logoutButton}>
                        <Button
                            type="default"
                            icon={<LogoutOutlined />}
                            onClick={handleLogout}
                            block
                        >
                            Выйти из аккаунта
                        </Button>
                    </div>
                </Col>
            </Row>
        </div>
    );
};