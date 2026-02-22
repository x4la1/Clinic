import React, { useState, useMemo, useEffect } from 'react';
import { Input, Select, Card, Typography, Row, Col, Empty, Avatar, Spin } from 'antd';
import { SearchOutlined } from '@ant-design/icons';
import { Link } from 'react-router-dom';
import type { Staff, Clinic, Specialization } from '../../types';
import styles from './DoctorsSearchPage.module.scss';
import { apiRequest } from '../../utils/api';

const { Title, Text } = Typography;
const { Option } = Select;

export const DoctorsSearchPage: React.FC = () => {
    const [searchTerm, setSearchTerm] = useState('');
    const [selectedSpecialization, setSelectedSpecialization] = useState<number | null>(null);
    const [selectedClinic, setSelectedClinic] = useState<number | null>(null);
    const [loading, setLoading] = useState(true);

    const [doctors, setDoctors] = useState<Staff[]>([]);
    const [clinics, setClinics] = useState<Clinic[]>([]);

    useEffect(() => {
        loadAllData();
    }, []);

    const loadAllData = async () => {
        try {
            setLoading(true);

            const [
                staffResponse,
                clinicsResponse
            ] = await Promise.all([
                apiRequest<Staff[]>('/api/staffs'),
                apiRequest<{ clinics: Clinic[] }>('/api/clinics/all')
            ]);

            setDoctors(staffResponse);
            setClinics(clinicsResponse.clinics || []);

        } catch (e) {
            console.error('Failed to load data', e);
        } finally {
            setLoading(false);
        }
    };

    const allSpecializations = useMemo(() => {
        const specMap = new Map<number, string>();
        doctors.forEach(doctor => {
            doctor.specializations?.forEach(spec => {
                specMap.set(spec.id, spec.name);
            });
        });
        return Array.from(specMap.entries()).map(([id, name]) => ({ id, name }));
    }, [doctors]);

    const filteredDoctors = useMemo(() => {
        return doctors.filter(doctor => {
            const matchesSearch =
                `${doctor.lastname} ${doctor.firstname} ${doctor.patronymic || ''}`
                    .toLowerCase()
                    .includes(searchTerm.toLowerCase());
            const matchesClinic = selectedClinic
                ? doctor.clinic?.id === selectedClinic
                : true;
            const matchesSpecialization = selectedSpecialization
                ? doctor.specializations?.some(spec => spec.id === selectedSpecialization)
                : true;

            return matchesSearch && matchesClinic && matchesSpecialization;
        });
    }, [searchTerm, selectedClinic, selectedSpecialization, doctors]);

    if (loading) {
        return (
            <div className={styles.container}>
                <Spin size="large" />
            </div>
        );
    }

    return (
        <div className={styles.container}>
            <Title level={2} className={styles.pageTitle}>
                Поиск врачей
            </Title>

            <Card className={styles.searchCard}>
                <div className={styles.searchRow}>
                    <Input
                        prefix={<SearchOutlined />}
                        placeholder="Поиск по ФИО..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                        allowClear
                        className={styles.searchInput}
                    />
                </div>

                <div className={styles.filters}>
                    <Select
                        placeholder="Выберите специализацию"
                        value={selectedSpecialization}
                        onChange={setSelectedSpecialization}
                        allowClear
                        style={{ width: '100%', maxWidth: 300 }}
                    >
                        {allSpecializations.map(spec => (
                            <Option key={spec.id} value={spec.id}>
                                {spec.name}
                            </Option>
                        ))}
                    </Select>

                    <Select
                        placeholder="Выберите поликлинику"
                        value={selectedClinic}
                        onChange={setSelectedClinic}
                        allowClear
                        style={{ width: '100%', maxWidth: 300 }}
                    >
                        {clinics.map(clinic => (
                            <Option key={clinic.id} value={clinic.id}>
                                {clinic.name}
                            </Option>
                        ))}
                    </Select>
                </div>
            </Card>

            <div className={styles.resultsSection}>
                <Title level={3}>
                    Найдено врачей: {filteredDoctors.length}
                </Title>

                {filteredDoctors.length === 0 ? (
                    <Empty description="Врачи не найдены" />
                ) : (
                    <Row gutter={[16, 16]}>
                        {filteredDoctors.map(doctor => (
                            <Col xs={24} sm={12} md={8} lg={6} key={doctor.id}>
                                <Link to={`/doctors/${doctor.id}`} className={styles.doctorLink}>
                                    <Card className={styles.doctorCard}>
                                        <Avatar size={60} style={{ backgroundColor: '#1890ff', marginBottom: '12px' }}>
                                            {doctor.lastname.charAt(0)}
                                        </Avatar>
                                        <div className={styles.doctorInfo}>
                                            <Text strong className={styles.doctorName}>
                                                {doctor.lastname} {doctor.firstname.charAt(0)}. {doctor.patronymic?.charAt(0)}.
                                            </Text>
                                            <div className={styles.specializations}>
                                                {doctor.specializations?.map(spec => (
                                                    <span key={spec.id} className={styles.specTag}>
                                                        {spec.name}
                                                    </span>
                                                ))}
                                            </div>
                                        </div>
                                    </Card>
                                </Link>
                            </Col>
                        ))}
                    </Row>
                )}
            </div>
        </div>
    );
};