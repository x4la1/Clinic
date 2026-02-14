import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { Button, Card, Typography, Spin, Empty } from 'antd';
import { ArrowLeftOutlined, CalendarOutlined } from '@ant-design/icons';
import { useAppStore } from '../../store';
import type { Staff, Clinic } from '../../types';
import styles from './DoctorDetailPage.module.scss';
import { apiRequest } from '../../utils/api';

const { Title, Text } = Typography;

export const DoctorDetailPage: React.FC = () => {
    const { id } = useParams<{ id: string }>();
    const navigate = useNavigate();
    const clinics = useAppStore((state) => state.clinics);

    const [doctor, setDoctor] = useState<Staff | null>(null);
    const [clinic, setClinic] = useState<Clinic | null>(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!id) {
            navigate('/');
            return;
        }

        loadDoctorData();
    }, [id, navigate, clinics]);

    const loadDoctorData = async () => {
        try {
            setLoading(true);

            const doctorId = Number(id);

            const staffResponse = await apiRequest<Staff[]>(`/api/staffs`); //ok
            const foundDoctor = staffResponse.find(s => s.id === doctorId);

            if (!foundDoctor) {
                navigate('/');
                return;
            }

            setDoctor(foundDoctor);

            const foundClinic = clinics.find(c => c.id === foundDoctor.clinic?.id);
            setClinic(foundClinic || null);

        } catch (e) {
            console.error('Failed to load doctor data', e);
            navigate('/');
        } finally {
            setLoading(false);
        }
    };

    const handleBookAppointment = () => {
        if (!doctor) return;

        navigate('/patient/book', {
            state: {
                doctorId: doctor.id,
                clinicId: doctor.clinic?.id
            }
        });
    };

    if (loading) {
        return (
            <div className={styles.loading}>
                <Spin size="large" />
            </div>
        );
    }

    if (!doctor) {
        return <div className={styles.notFound}>Врач не найден</div>;
    }

    return (
        <div className={styles.container}>
            <Button
                type="text"
                icon={<ArrowLeftOutlined />}
                onClick={() => navigate(-1)}
                className={styles.backButton}
            >
                Назад
            </Button>

            <Card className={styles.doctorCard}>
                <div className={styles.header}>
                    <div>
                        <Title level={2} className={styles.name}>
                            {doctor.lastname} {doctor.firstname} {doctor.patronymic || ''}
                        </Title>
                    </div>
                </div>

                <div className={styles.info}>
                    <Text strong>Опыт:</Text> <Text>{doctor.experienceYears} лет</Text>
                </div>

                {clinic && (
                    <div className={styles.clinicInfo}>
                        <Text strong>Поликлиника:</Text> <Text>{clinic.name}</Text>
                    </div>
                )}

                <Button
                    type="primary"
                    icon={<CalendarOutlined />}
                    size="large"
                    block
                    onClick={handleBookAppointment}
                    className={styles.bookButton}
                >
                    Записаться на приём
                </Button>
            </Card>

            <div className={styles.scheduleSection}>
                <Title level={3}>Специализации</Title>
                {doctor.specializations && doctor.specializations.length > 0 ? (
                    <div className={styles.specializations}>
                        {doctor.specializations.map(spec => (
                            <span key={spec.id} className={styles.specTag}>
                                {spec.name}
                            </span>
                        ))}
                    </div>
                ) : (
                    <Empty description="Нет специализаций" />
                )}
            </div>

            <div className={styles.servicesSection}>
                <Title level={3}>Услуги</Title>
                {doctor.services && doctor.services.length > 0 ? (
                    <div className={styles.services}>
                        {doctor.services.map(service => (
                            <div key={service.id} className={styles.serviceItem}>
                                {service.name}
                            </div>
                        ))}
                    </div>
                ) : (
                    <Empty description="Нет услуг" />
                )}
            </div>
        </div>
    );
};
