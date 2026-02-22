import React, { useEffect, useState } from 'react';
import { Card, Table, Button, Select, Spin, message, Modal } from 'antd';
import { DeleteOutlined, EyeOutlined } from '@ant-design/icons';
import { useNavigate } from 'react-router-dom';
import { useAppStore } from '../../store';
import type { User, Review } from '../../types';
import styles from './AdminReviewsPage.module.scss';
import { ROLE_IDS } from '../../utils/roles';
import { apiRequest } from '../../utils/api';

const { Option } = Select;

export const AdminReviewsPage: React.FC = () => {
    const navigate = useNavigate();
    const { auth, clinics } = useAppStore();
    const [loading, setLoading] = useState(true);

    const [reviews, setReviews] = useState<Review[]>([]);
    const [users, setUsers] = useState<User[]>([]);

    useEffect(() => {
        if (!auth.user || auth.user.roleId !== ROLE_IDS.ADMIN) {
            navigate('/');
            return;
        }

        loadData();
    }, [auth.user, navigate]);

    const loadData = async () => {
        try {
            setLoading(true);

            const [
                reviewsResponse,
                usersResponse
            ] = await Promise.all([
                apiRequest<{ reviews: Review[] }>('/api/reviews/all'),
                apiRequest<{ users: User[] }>('/api/users')
            ]);

            setReviews(reviewsResponse.reviews || []);
            setUsers(usersResponse.users);
        } catch (e) {
            console.error('Failed to load reviews', e);
            message.error('Ошибка загрузки отзывов');
        } finally {
            setLoading(false);
        }
    };

    const handleDelete = (id: number) => {
        Modal.confirm({
            title: 'Удалить отзыв?',
            content: 'Вы уверены, что хотите удалить этот отзыв?',
            okText: 'Да',
            okType: 'danger',
            cancelText: 'Нет',
            onOk: async () => {
                try {
                    await apiRequest('/api/review/delete', {
                        method: 'POST',
                        body: JSON.stringify({ id })
                    });
                    message.success('Отзыв удалён');
                    loadData();
                } catch (error: any) {
                    message.error(error.message || 'Ошибка при удалении отзыва');
                }
            },
        });
    };

    const getAuthorName = (userId: number) => {
        const user = users.find(u => u.id === userId);
        return user ? `${user.lastName} ${user.firstName}` : `ID: ${userId}`;
    };

    const getClinicName = (clinicId: number) => {
        const clinic = clinics.find(c => c.id === clinicId);
        return clinic ? clinic.name : `Поликлиника ID: ${clinicId}`;
    };

    const columns = [
        {
            title: 'Автор',
            key: 'author',
            render: (_: any, record: Review) => getAuthorName(record.userId),
        },
        {
            title: 'Поликлиника',
            key: 'clinic',
            render: (_: any, record: Review) => getClinicName(record.clinicId),
        },
        {
            title: 'Текст',
            key: 'description',
            render: (_: any, record: Review) => (
                <Button
                    type="link"
                    icon={<EyeOutlined />}
                    onClick={() => {
                        Modal.info({
                            title: 'Текст отзыва',
                            content: <div style={{ whiteSpace: 'pre-wrap' }}>{record.description}</div>,
                            width: 500,
                        });
                    }}
                >
                    Просмотреть
                </Button>
            ),
        },
        {
            title: 'Действия',
            key: 'actions',
            render: (_: any, record: Review) => (
                <Button
                    type="link"
                    danger
                    icon={<DeleteOutlined />}
                    onClick={() => handleDelete(record.id)}
                >
                    Удалить
                </Button>
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
            <h1 className={styles.title}>Модерация отзывов</h1>

            <Card title={`Все отзывы (${reviews.length})`}>
                {reviews.length === 0 ? (
                    <div style={{ textAlign: 'center', padding: 24 }}>
                        Нет отзывов
                    </div>
                ) : (
                    <Table
                        dataSource={reviews}
                        columns={columns}
                        rowKey="id"
                        pagination={{ pageSize: 10 }}
                    />
                )}
            </Card>
        </div>
    );
};