import React, { useState, useMemo, useEffect } from 'react';
import { Input, Select, Card, Typography, Row, Col, Empty, Avatar, Spin } from 'antd';
import { SearchOutlined } from '@ant-design/icons';
import { Link } from 'react-router-dom';
import type { Staff, Clinic } from '../../types';
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
                apiRequest<Staff[]>('/api/staffs'), //ok
                apiRequest<{ clinics: Clinic[] }>('/api/clinics/all') //ok
            ]);

            setDoctors(staffResponse);
            setClinics(clinicsResponse.clinics || []);

        } catch (e) {
            console.error('Failed to load data', e);
        } finally {
            setLoading(false);
        }
    };

    const getDoctorSpecializations = (doctor: Staff): string[] => {
        return doctor.specializations?.map(spec => spec.name) || [];
    };

    const allSpecializations = useMemo(() => {
        const specs = new Set<string>();
        doctors.forEach(doctor => {
            const doctorSpecs = getDoctorSpecializations(doctor);
            doctorSpecs.forEach(spec => specs.add(spec));
        });
        return Array.from(specs);
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

            const doctorSpecs = getDoctorSpecializations(doctor);
            const matchesSpecialization = selectedSpecialization
                ? doctorSpecs.some(spec =>
                    doctor.specializations?.find(s => s.id === selectedSpecialization)?.name === spec
                )
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
                        {allSpecializations.map((spec, idx) => (
                            <Option key={idx} value={doctors.flatMap(d => d.specializations || []).find(s => s.name === spec)?.id}>
                                {spec}
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
                                                {getDoctorSpecializations(doctor).map((spec, idx) => (
                                                    <span key={idx} className={styles.specTag}>
                                                        {spec}
                                                    </span>
                                                ))}
                                            </div>
                                            <Text type="secondary" className={styles.experience}>
                                                Опыт: {doctor.experienceYears} лет
                                            </Text>
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
