import React, { useEffect, useState } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { Button, Card, Typography, Spin, Row, Col, Empty, Form, Input, message } from 'antd';
import { ArrowLeftOutlined } from '@ant-design/icons';
import { useAppStore } from '../../store';
import type { Clinic, Staff, Review, User } from '../../types';
import styles from './ClinicDetailPage.module.scss';
import { ROLE_IDS } from '../../utils/roles';
import { apiRequest } from '../../utils/api';

const { Title, Text } = Typography;
const { TextArea } = Input;

export const ClinicDetailPage: React.FC = () => {
    const { id } = useParams<{ id: string }>();
    const navigate = useNavigate();
    const clinics = useAppStore((state) => state.clinics);
    const user = useAppStore((state) => state.auth.user);
    const [clinic, setClinic] = useState<Clinic | null>(null);
    const [doctors, setDoctors] = useState<Staff[]>([]);
    const [reviews, setReviews] = useState<Review[]>([]);
    const [loading, setLoading] = useState(true);
    const [reviewForm] = Form.useForm();
    const [users, setUsers] = useState<User[]>([]);

    useEffect(() => {
        if (!id) {
            navigate('/');
            return;
        }

        loadClinicData();
    }, [id, clinics, navigate]);

    const loadClinicData = async () => {
        try {
            setLoading(true);

            const clinicId = Number(id);
            const foundClinic = clinics.find(c => c.id === clinicId);
            if (!foundClinic) {
                navigate('/');
                return;
            }
            setClinic(foundClinic);

            const [
                staffResponse,
                reviewsResponse,
                usersResponse
            ] = await Promise.all([
                apiRequest<Staff[]>('/api/staffs'),//ok
                apiRequest<{ reviews: Review[] }>('/api/reviews/all'), //ne ok
                apiRequest<{ users: User[] }>('/api/users') //ok
            ]);

            const clinicDoctors = staffResponse.filter(s => s.clinic?.id === clinicId);
            setDoctors(clinicDoctors);

            const approvedReviews = (reviewsResponse.reviews || [])
                .filter(r => r.clinicId === clinicId);
            setReviews(approvedReviews);

            setUsers(usersResponse.users);

        } catch (e) {
            console.error('Failed to load data', e);
        } finally {
            setLoading(false);
        }
    };

    const handleBookAppointment = () => {
        if (!clinic) return;
        navigate('/patient/book', { state: { clinicId: clinic.id } });
    };

    const handleAddReview = async (values: any) => {
        if (!user || !clinic) return;

        try {
            await apiRequest('/api/review/create', { //ok
                method: 'POST',
                body: JSON.stringify({
                    user_id: user.id,
                    clinic_id: clinic.id,
                    description: values.description
                }),
            });

            message.success('Отзыв добавлен!');
            reviewForm.resetFields();
            loadClinicData();
        } catch (error: any) {
            message.error(error.message || 'Ошибка при добавлении отзыва');
        }
    };

    if (loading) {
        return (
            <div className={styles.loading}>
                <Spin size="large" />
            </div>
        );
    }

    if (!clinic) {
        return <div className={styles.notFound}>Поликлиника не найдена</div>;
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

            <Card className={styles.clinicCard}>
                <Title level={2} className={styles.clinicName}>
                    {clinic.name}
                </Title>

                <div className={styles.clinicInfo}>
                    <div className={styles.infoRow}>
                        <Text strong>Адрес:</Text>
                        <Text>{clinic.address}</Text>
                    </div>
                    <div className={styles.infoRow}>
                        <Text strong>Телефон:</Text>
                        <Text>{clinic.phone}</Text>
                    </div>
                    <div className={styles.infoRow}>
                        <Text strong>Email:</Text>
                        <Text>{clinic.email}</Text>
                    </div>
                </div>

                <Button
                    type="primary"
                    size="large"
                    block
                    onClick={handleBookAppointment}
                    className={styles.bookButton}
                >
                    Записаться к врачу
                </Button>
            </Card>
            <div className={styles.doctorsSection}>
                <Title level={3}>Врачи поликлиники</Title>
                {doctors.length === 0 ? (
                    <Empty description="Нет врачей" />
                ) : (
                    <Row gutter={[16, 16]}>
                        {doctors.map((doctor) => (
                            <Col xs={24} sm={12} md={8} key={doctor.id}>
                                <Link to={`/doctors/${doctor.id}`} className={styles.doctorLink}>
                                    <Card className={styles.doctorCard}>
                                        <div className={styles.doctorName}>
                                            {doctor.lastName} {doctor.firstName} {doctor.patronymic || ''}
                                        </div>
                                        <div className={styles.doctorExperience}>
                                            Опыт: {doctor.experienceYears} лет
                                        </div>
                                    </Card>
                                </Link>
                            </Col>
                        ))}
                    </Row>
                )}
            </div>
            <div className={styles.reviewsSection}>
                <Title level={3}>Отзывы</Title>
                {reviews.length > 0 ? (
                    <div>
                        {reviews.map((review) => {
                            const author = users.find(u => u.id === review.userId);
                            const authorName = author ? `${author.lastName} ${author.firstName}` : 'Аноним';

                            return (
                                <div key={review.id} className={styles.reviewItem}>
                                    <div className={styles.reviewText}>{review.description}</div>
                                    <div className={styles.reviewAuthor}>— {authorName}</div>
                                </div>
                            );
                        })}
                    </div>
                ) : (
                    <Empty description="Нет отзывов" />
                )}

                {user && user.roleId === ROLE_IDS.PATIENT && (
                    <Card style={{ marginTop: 16 }}>
                        <Form form={reviewForm} onFinish={handleAddReview}>
                            <Form.Item
                                name="description"
                                rules={[{ required: true, message: 'Введите отзыв' }]}
                            >
                                <TextArea rows={3} placeholder="Ваш отзыв о поликлинике..." />
                            </Form.Item>
                            <Button type="primary" htmlType="submit">Оставить отзыв</Button>
                        </Form>
                    </Card>
                )}
            </div>
        </div>
    );
};
