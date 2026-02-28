import React, { useState, useEffect } from 'react';
import { Card, Select, DatePicker, Button, Typography, message, Spin } from 'antd';
import { LeftOutlined } from '@ant-design/icons';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import dayjs from 'dayjs';
import { useAppStore } from '../../store';
import type { Clinic, Staff, Service, TimeSlot } from '../../types';
import styles from './BookAppointmentPage.module.scss';
import { ROLE_IDS } from '../../utils/roles';
import { apiRequest } from '../../utils/api';

const { Title } = Typography;
const { Option } = Select;

export const BookAppointmentPage: React.FC = () => {
  const navigate = useNavigate();
  const location = useLocation();
  const user = useAppStore((state) => state.auth.user);

  useEffect(() => {
    if (!user || user.roleId !== ROLE_IDS.PATIENT) {
      navigate('/');
    }
  }, [user, navigate]);

  const preselectedClinicId = (location.state as any)?.clinicId;
  const preselectedDoctorId = (location.state as any)?.doctorId;
  const [clinicId, setClinicId] = useState<number | null>(preselectedClinicId || null);
  const [doctorId, setDoctorId] = useState<number | null>(preselectedDoctorId || null);
  const [selectedDate, setSelectedDate] = useState<dayjs.Dayjs | null>(null);
  const [timeSlot, setTimeSlot] = useState<string | null>(null);
  const [serviceId, setServiceId] = useState<number | null>(null);
  const [loading, setLoading] = useState(false);

  const [clinics, setClinics] = useState<Clinic[]>([]);
  const [staff, setStaff] = useState<Staff[]>([]);
  const [services, setServices] = useState<Service[]>([]);
  const [availableTimeSlots, setAvailableTimeSlots] = useState<TimeSlot[]>([]);

  useEffect(() => {
    loadAllData();
  }, []);

  const loadAllData = async () => {
    try {
      const [
        clinicsResponse,
        staffResponse,
        servicesResponse
      ] = await Promise.all([
        apiRequest<{ clinics: Clinic[] }>('/api/clinics/all'),
        apiRequest<Staff[]>('/api/staffs'),
        apiRequest<Service[]>('/api/services')
      ]);

      setClinics(clinicsResponse.clinics || []);
      setStaff(staffResponse);
      setServices(servicesResponse);
    } catch (e) {
      console.error('Failed to load data', e);
      message.error('Ошибка загрузки данных');
    }
  };

  useEffect(() => {
    if (preselectedClinicId) {
      setClinicId(preselectedClinicId);
    }
    if (preselectedDoctorId) {
      setDoctorId(preselectedDoctorId);
      const doctor = staff.find(d => d.id === preselectedDoctorId);
      if (doctor && !preselectedClinicId) {
        setClinicId(doctor.clinic?.id || null);
      }
    }
  }, [preselectedClinicId, preselectedDoctorId, staff]);

  useEffect(() => {
    if (doctorId && selectedDate) {
      loadAvailableTimeSlots(doctorId, selectedDate);
    } else {
      setAvailableTimeSlots([]);
      setTimeSlot(null);
    }
  }, [doctorId, selectedDate]);

  const loadAvailableTimeSlots = async (staffId: number, date: dayjs.Dayjs) => {
    try {
      const formattedDate = date.format('YYYY-MM-DD');
      const response = await apiRequest<{ time_slots: TimeSlot[] }>(
        `/api/staff/timeslots/${staffId}/${formattedDate}`
      );
      console.log(response.time_slots );
      setAvailableTimeSlots(response.time_slots || []);
    } catch (e) {
      console.error('Failed to load available time slots', e);
      message.error('Не удалось загрузить доступное время');
      setAvailableTimeSlots([]);
    }
  };

  const availableDoctors = clinicId ? staff.filter(s => s.clinic?.id === clinicId) : [];

  const doctorServices = doctorId
    ? staff.find(s => s.id === doctorId)?.services || []
    : [];

  const isWeekend = (date: dayjs.Dayjs) => date.day() === 0 || date.day() === 6;

  const handleBook = async () => {
    if (!doctorId || !selectedDate || !timeSlot || !serviceId) {
      message.error('Заполните все поля');
      return;
    }

    try {
      setLoading(true);

      const dateTimeStr = `${selectedDate.format('YYYY-MM-DD')} ${timeSlot}:00`;

      await apiRequest('/api/appointment/create', {
        method: 'POST',
        body: JSON.stringify({
          user_id: user!.id,
          staff_id: doctorId,
          service_id: serviceId,
          date: dateTimeStr
        })
      });

      message.success('Вы успешно записаны!');
      setTimeout(() => navigate('/patient'), 1500);
    } catch (error: any) {
      message.error(error.message || 'Ошибка при записи');
    } finally {
      setLoading(false);
    }
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
      <Card className={styles.card}>
        <Link to="/patient" className={styles.backLink}>
          <LeftOutlined /> Назад в личный кабинет
        </Link>

        <Title level={2} className={styles.title}>Запись к врачу</Title>

        <div className={styles.formGroup}>
          <label>Выберите поликлинику</label>
          <Select
            placeholder="Выберите поликлинику"
            value={clinicId}
            onChange={(value) => {
              setClinicId(value);
              setDoctorId(null);
              setSelectedDate(null);
              setTimeSlot(null);
              setServiceId(null);
            }}
            style={{ width: '100%' }}
          >
            {clinics.map(clinic => (
              <Option key={clinic.id} value={clinic.id}>
                {clinic.name}
              </Option>
            ))}
          </Select>
        </div>

        <div className={styles.formGroup}>
          <label>Выберите врача</label>
          <Select
            placeholder="Сначала выберите поликлинику"
            value={doctorId}
            onChange={(value) => {
              setDoctorId(value);
              setSelectedDate(null);
              setTimeSlot(null);
              setServiceId(null);
            }}
            disabled={!clinicId}
            style={{ width: '100%' }}
          >
            {availableDoctors.map(doctor => (
              <Option key={doctor.id} value={doctor.id}>
                {doctor.lastname} {doctor.firstname} {doctor.patronymic}
              </Option>
            ))}
          </Select>
        </div>

        <div className={styles.formGroup}>
          <label>Выберите услугу</label>
          <Select
            placeholder="Сначала выберите врача"
            value={serviceId}
            onChange={(value) => {
              setServiceId(value);
            }}
            disabled={!doctorId}
            style={{ width: '100%' }}
          >
            {doctorServices.map(service => (
              <Option key={service.id} value={service.id}>
                {service.name}
              </Option>
            ))}
          </Select>
        </div>

        <div className={styles.formGroup}>
          <label>Выберите дату</label>
          <DatePicker
            placeholder="Выберите дату"
            value={selectedDate}
            onChange={(date) => {
              setSelectedDate(date);
              setTimeSlot(null);
            }}
            disabled={!serviceId}
            style={{ width: '100%' }}
            disabledDate={(current) => {
              return current && (current < dayjs().startOf('day') || isWeekend(current));
            }}
          />
        </div>

        <div className={styles.formGroup}>
          <label>Выберите время</label>
          <Select
            placeholder="Сначала выберите дату"
            value={timeSlot}
            onChange={setTimeSlot}
            disabled={!selectedDate || isWeekend(selectedDate!) || availableTimeSlots.length === 0}
            style={{ width: '100%' }}
          >
            {availableTimeSlots.map(slot => (
              <Option key={slot.id} value={slot.slot}>
                {slot.slot}
              </Option>
            ))}
          </Select>
        </div>

        <Button
          type="primary"
          block
          size="large"
          onClick={handleBook}
          loading={loading}
          disabled={!doctorId || !selectedDate || !timeSlot || !serviceId || isWeekend(selectedDate!) || availableTimeSlots.length === 0}
          className={styles.submitButton}
        >
          Записаться
        </Button>
      </Card>
    </div>
  );
};
