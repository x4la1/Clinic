import React, { useEffect, useState } from 'react';
import { Card, Table, Button, Modal, Form, Input, Select, Tag, Spin, message, Tabs, Row, DatePicker } from 'antd';
import { PlusOutlined, LogoutOutlined, EditOutlined, DeleteOutlined } from '@ant-design/icons';
import { useNavigate } from 'react-router-dom';
import { useAppStore } from '../../store';
import type { Clinic, User, Staff, Cabinet, Appointment, TimeSlot, Specialization, Service } from '../../types';
import styles from './AdminDashboardPage.module.scss';
import dayjs from 'dayjs';
import { ROLE_IDS } from '../../utils/roles';
import { apiRequest } from '../../utils/api';

const { Option } = Select;

export const AdminDashboardPage: React.FC = () => {
  const navigate = useNavigate();
  const { auth, clinics, setClinics } = useAppStore();
  const [loading, setLoading] = useState(true);

  const [isClinicModalOpen, setIsClinicModalOpen] = useState(false);
  const [clinicForm] = Form.useForm();

  const [staff, setStaff] = useState<Staff[]>([]);
  const [isStaffModalOpen, setIsStaffModalOpen] = useState(false);
  const [editingStaff, setEditingStaff] = useState<Staff | null>(null);
  const [staffForm] = Form.useForm();

  const [timeSlots, setTimeSlots] = useState<TimeSlot[]>([]);
  const [isTimeSlotModalOpen, setIsTimeSlotModalOpen] = useState(false);
  const [timeSlotForm] = Form.useForm();

  const [users, setUsers] = useState<User[]>([]);
  const [appointments, setAppointments] = useState<Appointment[]>([]);

  const [cabinets, setCabinets] = useState<Cabinet[]>([]);
  const [isCabinetModalOpen, setIsCabinetModalOpen] = useState(false);
  const [cabinetForm] = Form.useForm();

  const [filteredAppointments, setFilteredAppointments] = useState<Appointment[]>([]);
  const [filters, setFilters] = useState({
    staffId: null as number | null,
    statusId: null as number | null,
    date: null as dayjs.Dayjs | null,
  });

  const [resultForm] = Form.useForm();
  const [currentAppointment, setCurrentAppointment] = useState<Appointment | null>(null);
  const [specializations, setSpecializations] = useState<Specialization[]>([]);
  const [services, setServices] = useState<Service[]>([]);
  const [isServiceModalOpen, setIsServiceModalOpen] = useState(false);
  const [serviceForm] = Form.useForm();
  const [isSpecializationModalOpen, setIsSpecializationModalOpen] = useState(false);
  const [specializationForm] = Form.useForm();

  useEffect(() => {
    if (!auth.user || auth.user.roleId !== ROLE_IDS.ADMIN) {
      navigate('/');
      return;
    }

    loadAllData();
  }, [auth.user, navigate]);

  const loadAllData = async () => {
    try {
      setLoading(true);
      const [
        usersResponse,
        staffResponse,
        appointmentsResponse,
        clinicsResponse,
        cabinetsResponse,
        specializationsResponse,
        servicesResponse
      ] = await Promise.all([
        apiRequest<{ users: User[] }>('/api/users'), //ok
        apiRequest<Staff[]>('/api/staffs'), //ok
        apiRequest<{ appointments: Appointment[] }>('/api/appointments/all'), //ne ok
        apiRequest<{ clinics: Clinic[] }>('/api/clinics/all'), //ok
        apiRequest<{ cabinets: Cabinet[] }>('/api/cabinets/all'), //ok
        apiRequest<Specialization[]>('/api/specializations'), //ok
        apiRequest<Service[]>('/api/services') //ok
      ]);

      setUsers(usersResponse.users);
      setStaff(staffResponse);
      setAppointments(appointmentsResponse.appointments || []);
      setClinics(clinicsResponse.clinics || []);
      setCabinets(cabinetsResponse.cabinets || []);
      setSpecializations(specializationsResponse);
      setServices(servicesResponse);

    } catch (error: any) {
      message.error(error.message || 'Ошибка загрузки данных');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    let result = [...appointments];

    if (filters.staffId) {
      result = result.filter(a => a.staff?.id === filters.staffId);
    }
    if (filters.statusId) {
      result = result.filter(a => a.status?.id === filters.statusId);
    }
    if (filters.date) {
      const dateStr = filters.date.format('YYYY-MM-DD');
      result = result.filter(a => a.date.startsWith(dateStr));
    }

    setFilteredAppointments(result);
  }, [appointments, filters]);

  const handleLogout = () => {
    localStorage.removeItem('user');
    useAppStore.getState().setUser(null);
    message.success('Вы вышли из системы');
    navigate('/');
  };

  const openResultModal = (appointment: Appointment) => {
    setCurrentAppointment(appointment);
    resultForm.setFieldsValue({ result: appointment.result || '' });
  };

  const handleSaveResult = async (values: any) => {
    try {
      await apiRequest('/api/appointment/result/update', { //ок
        method: 'POST',
        body: JSON.stringify({
          id: currentAppointment!.id,
          result: values.result
        }),
      });
      const updated = appointments.map(a =>
        a.id === currentAppointment!.id
          ? { ...a, result: values.result }
          : a
      );
      setAppointments(updated);
      message.success('Результат сохранён');
      setCurrentAppointment(null);
    } catch (error: any) {
      message.error(error.message || 'Ошибка сохранения результата');
    }
  };

  const handleAddClinic = async (values: any) => {
    try {
      await apiRequest('/api/clinic/create', { //ok
        method: 'POST',
        body: JSON.stringify({
          name: values.name,
          address: values.address,
          phone: values.phone,
          email: values.email,
          image: ''
        }),
      });

      message.success('Поликлиника добавлена');
      setIsClinicModalOpen(false);
      clinicForm.resetFields();
      loadAllData();
    } catch (error: any) {
      message.error(error.message || 'Ошибка добавления поликлиники');
    }
  };

  const handleSaveStaff = async (values: any) => {
    try {
      const staffData = {
        clinic_id: values.clinicId,
        firstname: values.firstName,
        lastname: values.lastName,
        patronymic: values.patronymic || '',
        experience: dayjs().subtract(values.experience, 'year').format('YYYY-MM-DD'),
        phone: values.phone,
      };

      let staffId: number;

      if (editingStaff) {
        await apiRequest('/api/staff/update', { //ok
          method: 'POST',
          body: JSON.stringify({
            ...staffData,
            id: editingStaff.id
          }),
        });
        staffId = editingStaff.id;
      } else {
        const response = await apiRequest<{ id: number }>('/api/staff/create', { //ok
          method: 'POST',
          body: JSON.stringify(staffData),
        });
        staffId = response.id;
      }

      if (values.specializations && values.specializations.length > 0) {
        await apiRequest('/api/staff/specializations/update', { //ok
          method: 'POST',
          body: JSON.stringify({
            id: staffId,
            specializations: values.specializations.map((id: number) => ({ specialization_id: id }))
          }),
        });
      }

      if (values.services && values.services.length > 0) {
        await apiRequest('/api/staff/services/update', { //ok
          method: 'POST',
          body: JSON.stringify({
            id: staffId,
            services: values.services
          }),
        });
      }

      message.success(editingStaff ? 'Сотрудник обновлён' : 'Сотрудник добавлен');
      setIsStaffModalOpen(false);
      setEditingStaff(null);
      staffForm.resetFields();
      loadAllData();

    } catch (error: any) {
      message.error(error.message || 'Ошибка сохранения сотрудника');
    }
  };

  const handleDeleteStaff = (record: Staff) => {
    Modal.confirm({
      title: 'Удалить сотрудника?',
      content: `Вы уверены, что хотите удалить ${record.lastname} ${record.firstname}?`,
      okText: 'Да',
      okType: 'danger',
      cancelText: 'Нет',
      onOk: async () => {
        try {
          await apiRequest('/api/staff/delete', {//ok
            method: 'POST',
            body: JSON.stringify({ id: record.id }),
          });
          message.success('Сотрудник удалён');
          loadAllData();
        } catch (error: any) {
          message.error(error.message || 'Ошибка удаления сотрудника');
        }
      },
    });
  };

  const handleAddTimeSlot = async (values: any) => {
    try {
      const slots = values.slots || [];
      await apiRequest('/api/timeslots/create', {//hz
        method: 'POST',
        body: JSON.stringify({
          staff_id: values.staffId,
          slots: slots
        }),
      });
      message.success('Слоты добавлены');
      setIsTimeSlotModalOpen(false);
      timeSlotForm.resetFields();
      loadAllData();
    } catch (error: any) {
      message.error(error.message || 'Ошибка добавления слотов');
    }
  };

  const handleDeleteTimeSlot = (record: TimeSlot) => {
    Modal.confirm({
      title: 'Удалить слот?',
      content: `Вы уверены, что хотите удалить слот ${record.slot}?`,
      okText: 'Да',
      okType: 'danger',
      cancelText: 'Нет',
      onOk: async () => {
        try {
          await apiRequest('/api/timeslot/delete', { //hz
            method: 'POST',
            body: JSON.stringify({ id: record.id }),
          });
          message.success('Слот удалён');
          loadAllData();
        } catch (error: any) {
          message.error(error.message || 'Ошибка удаления слота');
        }
      },
    });
  };

  const handleAddCabinet = async (values: any) => {
    try {
      await apiRequest('/api/cabinet/create', { //ok
        method: 'POST',
        body: JSON.stringify({
          id: values.clinicId,
          number: values.number,
          description: values.description
        }),
      });
      message.success('Кабинет добавлен');
      setIsCabinetModalOpen(false);
      cabinetForm.resetFields();
      loadAllData();
    } catch (error: any) {
      message.error(error.message || 'Ошибка добавления кабинета');
    }
  };

  const handleDeleteCabinet = (record: Cabinet) => {
    Modal.confirm({
      title: 'Удалить кабинет?',
      content: `Вы уверены, что хотите удалить кабинет №${record.number}?`,
      okText: 'Да',
      okType: 'danger',
      cancelText: 'Нет',
      onOk: async () => {
        try {
          await apiRequest('/api/cabinet/delete', {//ok
            method: 'POST',
            body: JSON.stringify({ id: record.id }),
          });
          message.success('Кабинет удалён');
          loadAllData();
        } catch (error: any) {
          message.error(error.message || 'Ошибка удаления кабинета');
        }
      },
    });
  };

  const handleCancelAppointment = (record: Appointment) => {
    Modal.confirm({
      title: 'Отменить запись?',
      content: `Вы уверены, что хотите отменить запись пациента ID ${record.id}?`,
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
          loadAllData();
        } catch (error: any) {
          message.error(error.message || 'Ошибка при отмене записи');
        }
      },
    });
  };
  const handleCreateService = async (values: any) => {
    try {
      await apiRequest('/api/service/create', { //ok
        method: 'POST',
        body: JSON.stringify({ name: values.name }),
      });
      message.success('Услуга добавлена');
      setIsServiceModalOpen(false);
      serviceForm.resetFields();
      loadAllData();
    } catch (error: any) {
      message.error(error.message || 'Ошибка добавления услуги');
    }
  };

  const handleDeleteService = (service: Service) => {
    Modal.confirm({
      title: 'Удалить услугу?',
      content: `Вы уверены, что хотите удалить услугу "${service.name}"?`,
      okText: 'Да',
      okType: 'danger',
      cancelText: 'Нет',
      onOk: async () => {
        try {
          await apiRequest('/api/service/delete', { //ok
            method: 'POST',
            body: JSON.stringify({ id: service.id }),
          });
          message.success('Услуга удалена');
          loadAllData();
        } catch (error: any) {
          message.error(error.message || 'Ошибка удаления услуги');
        }
      },
    });
  };

  const handleCreateSpecialization = async (values: any) => {
    try {
      await apiRequest('/api/specialization/create', { //ok
        method: 'POST',
        body: JSON.stringify({ name: values.name }),
      });
      message.success('Специализация добавлена');
      setIsSpecializationModalOpen(false);
      specializationForm.resetFields();
      loadAllData();
    } catch (error: any) {
      message.error(error.message || 'Ошибка добавления специализации');
    }
  };

  const handleDeleteSpecialization = (spec: Specialization) => {
    Modal.confirm({
      title: 'Удалить специализацию?',
      content: `Вы уверены, что хотите удалить специализацию "${spec.name}"?`,
      okText: 'Да',
      okType: 'danger',
      cancelText: 'Нет',
      onOk: async () => {
        try {
          await apiRequest('/api/specialization/delete', { //ok
            method: 'POST',
            body: JSON.stringify({ id: spec.id }),
          });
          message.success('Специализация удалена');
          loadAllData();
        } catch (error: any) {
          message.error(error.message || 'Ошибка удаления специализации');
        }
      },
    });
  };

  const handleDeleteClinic = (record: Clinic) => {
    Modal.confirm({
      title: 'Удалить поликлинику?',
      content: `Вы уверены, что хотите удалить поликлинику "${record.name}"?`,
      okText: 'Да',
      okType: 'danger',
      cancelText: 'Нет',
      onOk: async () => {
        try {
          await apiRequest('/api/clinic/delete', { //ok
            method: 'POST',
            body: JSON.stringify({ id: record.id }),
          });
          message.success('Поликлиника удалена');
          loadAllData();
        } catch (error: any) {
          message.error(error.message || 'Ошибка удаления поликлиники');
        }
      },
    });
  };

  const clinicColumns = [
    { title: 'Название', dataIndex: 'name', key: 'name' },
    { title: 'Адрес', dataIndex: 'address', key: 'address' },
    { title: 'Телефон', dataIndex: 'phone', key: 'phone' },
    { title: 'Email', dataIndex: 'email', key: 'email' },
    {
      title: 'Действия',
      key: 'actions',
      render: (_: any, record: Clinic) => (
        <Button
          type="link"
          danger
          icon={<DeleteOutlined />}
          onClick={() => handleDeleteClinic(record)}
        >
          Удалить
        </Button>
      ),
    },
  ];

  const patientColumns = [
    { title: 'ID', dataIndex: 'id', key: 'id' },
    { title: 'Email', dataIndex: 'login', key: 'login' },
    {
      title: 'ФИО',
      render: (_: any, record: User) =>
        `${record.lastName || ''} ${record.firstName || ''} ${record.patronymic || ''}`.trim() || '—'
    },
    {
      title: 'Роль',
      render: (_: any, record: User) => {
        switch (record.roleId) {
          case ROLE_IDS.PATIENT: return 'Пациент';
          case ROLE_IDS.ADMIN: return 'Админ';
          default: return 'Гость';
        }
      }
    },
  ];

  const appointmentColumns = [
    { title: 'ID', dataIndex: 'id', key: 'id' },
    {
      title: 'Пациент',
      key: 'patient',
      render: (_: any, record: Appointment) => {
        const user = users.find(u => u.id === record.id);
        return user ? `${user.lastName} ${user.firstName}` : `ID: ${record.id}`;
      }
    },
    {
      title: 'Врач',
      key: 'staff',
      render: (_: any, record: Appointment) => {
        return `${record?.staff?.lastName || ''} ${record?.staff?.firstName || ''}`;
      }
    },
    {
      title: 'Дата',
      key: 'date',
      render: (_: any, record: Appointment) =>
        dayjs(record.date).format('DD.MM.YYYY HH:mm')
    },
    {
      title: 'Статус',
      key: 'status',
      render: (_: any, record: Appointment) => {
        let color = 'default';
        let text = record.status?.name || 'Неизвестно';
        switch (record.status?.name) {
          case 'SCHEDULED': color = 'blue'; text = 'Запланировано'; break;
          case 'COMPLETED': color = 'green'; text = 'Завершено'; break;
          case 'CANCELED': color = 'red'; text = 'Отменено'; break;
        }
        return <Tag color={color}>{text}</Tag>;
      }
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
              size="small"
              onClick={() => handleCancelAppointment(record)}
            >
              Отменить
            </Button>
          )}
          {record.status?.name === 'COMPLETED' && (
            <Button
              type="link"
              onClick={() => openResultModal(record)}
            >
              {record.result ? 'Результат' : 'Добавить результат'}
            </Button>
          )}
        </>
      ),
    },
  ];

  const staffColumns = [
    {
      title: 'ФИО',
      render: (_: any, record: Staff) =>
        `${record.lastname} ${record.firstname} ${record.patronymic || ''}`.trim(),
    },
    {
      title: 'Специализации',
      render: (_: any, record: Staff) => {
        const specs = record.specializations?.map(s => s.name).join(', ') || '—';
        return specs;
      }
    },
    {
      title: 'Услуги',
      render: (_: any, record: Staff) => {
        const servs = record.services?.map(s => s.name).join(', ') || '—';
        return servs;
      }
    },
    {
      title: 'Кабинет',
      render: (_: any, record: Staff) => {
        return record.cabinet ? `${record.cabinet.number} - ${record.cabinet.description}` : 'Без кабинета';
      }
    },
    {
      title: 'Поликлиника',
      render: (_: any, record: Staff) => {
        return record.clinic?.name || '—';
      },
    },
    {
      title: 'Опыт',
      render: (_: any, record: Staff) => `${record.experienceYears} лет`,
    },
    {
      title: 'Действия',
      key: 'actions',
      render: (_: any, record: Staff) => (
        <>
          <Button
            type="link"
            icon={<EditOutlined />}
            onClick={() => {
              setEditingStaff(record);
              staffForm.setFieldsValue({
                ...record,
                clinicId: record.clinic?.id,
                cabinetId: record.cabinet?.id || null,
                specializations: record.specializations?.map(s => s.id) || [],
                services: record.services?.map(s => s.id) || []
              });
              setIsStaffModalOpen(true);
            }}
          >
            Редактировать
          </Button>
          <Button
            type="link"
            danger
            icon={<DeleteOutlined />}
            onClick={() => handleDeleteStaff(record)}
          >
            Удалить
          </Button>
        </>
      ),
    },
  ];

  const timeSlotColumns = [
    {
      title: 'Врач',
      render: (_: any, record: TimeSlot) => {
        const doctor = staff.find(s => s.id === record.staffId);
        return doctor ? `${doctor.lastname} ${doctor.firstname}` : `ID: ${record.staffId}`;
      }
    },
    {
      title: 'Время',
      dataIndex: 'slot',
      key: 'slot',
    },
    {
      title: 'Действия',
      key: 'actions',
      render: (_: any, record: TimeSlot) => (
        <Button
          type="link"
          danger
          icon={<DeleteOutlined />}
          onClick={() => handleDeleteTimeSlot(record)}
        >
          Удалить
        </Button>
      ),
    },
  ];

  const cabinetColumns = [
    {
      title: 'Поликлиника',
      render: (_: any, record: Cabinet) => {
        const clinic = clinics.find(c => c.id === record.clinicId);
        return clinic?.name || `ID: ${record.clinicId}`;
      },
    },
    {
      title: 'Номер кабинета',
      dataIndex: 'number',
      key: 'number',
    },
    {
      title: 'Описание',
      dataIndex: 'description',
      key: 'description',
      render: (desc: string) => desc || '—',
    },
    {
      title: 'Действия',
      key: 'actions',
      render: (_: any, record: Cabinet) => (
        <Button
          type="link"
          danger
          icon={<DeleteOutlined />}
          onClick={() => handleDeleteCabinet(record)}
        >
          Удалить
        </Button>
      ),
    },
  ];

  const serviceColumns = [
    { title: 'Название', dataIndex: 'name', key: 'name' },
    {
      title: 'Действия',
      key: 'actions',
      render: (_: any, record: Service) => (
        <Button
          type="link"
          danger
          icon={<DeleteOutlined />}
          onClick={() => handleDeleteService(record)}
        >
          Удалить
        </Button>
      ),
    },
  ];

  const specializationColumns = [
    { title: 'Название', dataIndex: 'name', key: 'name' },
    {
      title: 'Действия',
      key: 'actions',
      render: (_: any, record: Specialization) => (
        <Button
          type="link"
          danger
          icon={<DeleteOutlined />}
          onClick={() => handleDeleteSpecialization(record)}
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
      <Row justify="space-between" align="middle" style={{ marginBottom: 24 }}>
        <h1 className={styles.title}>Админ-панель</h1>
        <Button icon={<LogoutOutlined />} onClick={handleLogout}>
          Выйти
        </Button>
      </Row>

      <Tabs
        items={[
          {
            key: '1',
            label: 'Поликлиники',
            children: (
              <Card
                title="Список поликлиник"
                extra={
                  <Button
                    type="primary"
                    icon={<PlusOutlined />}
                    onClick={() => setIsClinicModalOpen(true)}
                  >
                    Добавить
                  </Button>
                }
              >
                <Table
                  dataSource={clinics}
                  columns={clinicColumns}
                  rowKey="id"
                  pagination={{ pageSize: 5 }}
                />
              </Card>
            ),
          },
          {
            key: '2',
            label: 'Пациенты',
            children: (
              <Card title="Список пользователей">
                <Table
                  dataSource={users}
                  columns={patientColumns}
                  rowKey="id"
                  pagination={{ pageSize: 5 }}
                />
              </Card>
            ),
          },
          {
            key: '3',
            label: 'Все записи',
            children: (
              <Card title="Все записи на приём">
                <div style={{ marginBottom: 16, display: 'flex', gap: 16, flexWrap: 'wrap' }}>
                  <Select
                    placeholder="Фильтр по врачу"
                    value={filters.staffId}
                    onChange={(value) => setFilters(prev => ({ ...prev, staffId: value }))}
                    allowClear
                    style={{ width: 200 }}
                  >
                    {staff.map(doctor => (
                      <Option key={doctor.id} value={doctor.id}>
                        {doctor.lastname} {doctor.firstname}
                      </Option>
                    ))}
                  </Select>

                  <Select
                    placeholder="Фильтр по статусу"
                    value={filters.statusId}
                    onChange={(value) => setFilters(prev => ({ ...prev, statusId: value }))}
                    allowClear
                    style={{ width: 150 }}
                  >
                    <Option value={1}>Запланировано</Option>
                    <Option value={2}>Завершено</Option>
                    <Option value={3}>Отменено</Option>
                  </Select>

                  <DatePicker
                    placeholder="Фильтр по дате"
                    value={filters.date}
                    onChange={(date) => setFilters(prev => ({ ...prev, date }))}
                    allowClear
                    style={{ width: 180 }}
                  />

                  <Button onClick={() => setFilters({ staffId: null, statusId: null, date: null })}>
                    Сбросить фильтры
                  </Button>
                </div>

                <Table
                  dataSource={filteredAppointments}
                  columns={appointmentColumns}
                  rowKey="id"
                  pagination={{ pageSize: 10 }}
                />
              </Card>
            ),
          },
          {
            key: '4',
            label: 'Персонал',
            children: (
              <Card
                title="Список персонала"
                extra={
                  <Button
                    type="primary"
                    icon={<PlusOutlined />}
                    onClick={() => {
                      setEditingStaff(null);
                      staffForm.resetFields();
                      setIsStaffModalOpen(true);
                    }}
                  >
                    Добавить
                  </Button>
                }
              >
                <Table
                  dataSource={staff}
                  columns={staffColumns}
                  rowKey="id"
                  pagination={{ pageSize: 5 }}
                />
              </Card>
            ),
          },
          {
            key: '5',
            label: 'Временные слоты',
            children: (
              <Card
                title="Расписание врачей"
                extra={
                  <Button
                    type="primary"
                    icon={<PlusOutlined />}
                    onClick={() => {
                      timeSlotForm.resetFields();
                      setIsTimeSlotModalOpen(true);
                    }}
                  >
                    Добавить слоты
                  </Button>
                }
              >
                <Table
                  dataSource={timeSlots}
                  columns={timeSlotColumns}
                  rowKey="id"
                  pagination={{ pageSize: 10 }}
                />
              </Card>
            ),
          },
          {
            key: '6',
            label: 'Кабинеты',
            children: (
              <Card
                title="Список кабинетов"
                extra={
                  <Button
                    type="primary"
                    icon={<PlusOutlined />}
                    onClick={() => {
                      cabinetForm.resetFields();
                      setIsCabinetModalOpen(true);
                    }}
                  >
                    Добавить кабинет
                  </Button>
                }
              >
                <Table
                  dataSource={cabinets}
                  columns={cabinetColumns}
                  rowKey="id"
                  pagination={{ pageSize: 5 }}
                />
              </Card>
            ),
          },
          {
            key: '7',
            label: 'Услуги',
            children: (
              <Card
                title="Список услуг"
                extra={
                  <Button
                    type="primary"
                    icon={<PlusOutlined />}
                    onClick={() => setIsServiceModalOpen(true)}
                  >
                    Добавить
                  </Button>
                }
              >
                <Table
                  dataSource={services}
                  columns={serviceColumns}
                  rowKey="id"
                  pagination={{ pageSize: 10 }}
                />
              </Card>
            ),
          },
          {
            key: '8',
            label: 'Специализации',
            children: (
              <Card
                title="Список специализаций"
                extra={
                  <Button
                    type="primary"
                    icon={<PlusOutlined />}
                    onClick={() => setIsSpecializationModalOpen(true)}
                  >
                    Добавить
                  </Button>
                }
              >
                <Table
                  dataSource={specializations}
                  columns={specializationColumns}
                  rowKey="id"
                  pagination={{ pageSize: 10 }}
                />
              </Card>
            ),
          }
        ]}
      />
      <Modal
        title="Добавить поликлинику"
        open={isClinicModalOpen}
        onCancel={() => {
          setIsClinicModalOpen(false);
          clinicForm.resetFields();
        }}
        footer={null}
      >
        <Form form={clinicForm} layout="vertical" onFinish={handleAddClinic}>
          <Form.Item name="name" label="Название" rules={[{ required: true }]}>
            <Input placeholder="Городская поликлиника №1" />
          </Form.Item>
          <Form.Item name="address" label="Адрес" rules={[{ required: true }]}>
            <Input placeholder="ул. Ленина, 10" />
          </Form.Item>
          <Form.Item name="phone" label="Телефон" rules={[{ required: true }]}>
            <Input placeholder="+7 (495) 123-45-67" />
          </Form.Item>
          <Form.Item name="email" label="Email" rules={[{ required: true, type: 'email' }]}>
            <Input placeholder="clinic@example.com" />
          </Form.Item>
          <Form.Item>
            <Button type="primary" htmlType="submit" block>
              Добавить
            </Button>
          </Form.Item>
        </Form>
      </Modal>

      <Modal
        title={editingStaff ? 'Редактировать сотрудника' : 'Добавить сотрудника'}
        open={isStaffModalOpen}
        onCancel={() => {
          setIsStaffModalOpen(false);
          setEditingStaff(null);
          staffForm.resetFields();
        }}
        footer={null}
        width={600}
      >
        <Form form={staffForm} layout="vertical" onFinish={handleSaveStaff}>
          <Form.Item name="lastName" label="Фамилия" rules={[{ required: true }]}>
            <Input placeholder="Иванов" />
          </Form.Item>
          <Form.Item name="firstName" label="Имя" rules={[{ required: true }]}>
            <Input placeholder="Иван" />
          </Form.Item>
          <Form.Item name="patronymic" label="Отчество">
            <Input placeholder="Иванович" />
          </Form.Item>
          <Form.Item name="specializations" label="Специализации">
            <Select mode="multiple" placeholder="Выберите специализации">
              {specializations.map(spec => (
                <Option key={spec.id} value={spec.id}>{spec.name}</Option>
              ))}
            </Select>
          </Form.Item>
          <Form.Item name="services" label="Услуги">
            <Select mode="multiple" placeholder="Выберите услуги">
              {services.map(service => (
                <Option key={service.id} value={service.id}>{service.name}</Option>
              ))}
            </Select>
          </Form.Item>
          <Form.Item name="clinicId" label="Поликлиника" rules={[{ required: true }]}>
            <Select placeholder="Выберите поликлинику">
              {clinics.map(clinic => (
                <Option key={clinic.id} value={clinic.id}>
                  {clinic.name}
                </Option>
              ))}
            </Select>
          </Form.Item>
          <Form.Item name="cabinetId" label="Кабинет">
            <Select placeholder="Выберите кабинет (опционально)">
              <Option value={null}>Без кабинета</Option>
              {cabinets.map(cabinet => (
                <Option key={cabinet.id} value={cabinet.id}>
                  {cabinet.number} - {cabinet.description}
                </Option>
              ))}
            </Select>
          </Form.Item>
          <Form.Item name="experience" label="Опыт (лет)" rules={[{ required: true }]}>
            <Input type="number" min={0} placeholder="10" />
          </Form.Item>
          <Form.Item name="phone" label="Телефон" rules={[{ required: true }]}>
            <Input placeholder="+7 (999) 123-45-67" />
          </Form.Item>
          <Form.Item>
            <Button type="primary" htmlType="submit" block>
              {editingStaff ? 'Сохранить' : 'Добавить'}
            </Button>
          </Form.Item>
        </Form>
      </Modal>

      <Modal
        title="Добавить временные слоты"
        open={isTimeSlotModalOpen}
        onCancel={() => {
          setIsTimeSlotModalOpen(false);
          timeSlotForm.resetFields();
        }}
        footer={null}
      >
        <Form form={timeSlotForm} layout="vertical" onFinish={handleAddTimeSlot}>
          <Form.Item name="staffId" label="Врач" rules={[{ required: true }]}>
            <Select placeholder="Выберите врача">
              {staff.map(doctor => (
                <Option key={doctor.id} value={doctor.id}>
                  {doctor.lastname} {doctor.firstname}
                </Option>
              ))}
            </Select>
          </Form.Item>
          <Form.Item name="slots" label="Временные слоты" rules={[{ required: true }]}>
            <Select
              mode="tags"
              placeholder="Введите время (например, 09:00)"
              tokenSeparators={[',']}
            />
          </Form.Item>
          <Form.Item>
            <Button type="primary" htmlType="submit" block>
              Добавить
            </Button>
          </Form.Item>
        </Form>
      </Modal>

      <Modal
        title="Добавить кабинет"
        open={isCabinetModalOpen}
        onCancel={() => {
          setIsCabinetModalOpen(false);
          cabinetForm.resetFields();
        }}
        footer={null}
      >
        <Form form={cabinetForm} layout="vertical" onFinish={handleAddCabinet}>
          <Form.Item name="clinicId" label="Поликлиника" rules={[{ required: true }]}>
            <Select placeholder="Выберите поликлинику">
              {clinics.map(clinic => (
                <Option key={clinic.id} value={clinic.id}>
                  {clinic.name}
                </Option>
              ))}
            </Select>
          </Form.Item>
          <Form.Item name="number" label="Номер кабинета" rules={[{ required: true }]}>
            <Input placeholder="101" />
          </Form.Item>
          <Form.Item name="description" label="Описание">
            <Input.TextArea placeholder="Терапевтический кабинет" rows={3} />
          </Form.Item>
          <Form.Item>
            <Button type="primary" htmlType="submit" block>
              Добавить
            </Button>
          </Form.Item>
        </Form>
      </Modal>

      <Modal
        title="Результат приёма"
        open={!!currentAppointment}
        onCancel={() => setCurrentAppointment(null)}
        footer={null}
      >
        <Form form={resultForm} onFinish={handleSaveResult}>
          <Form.Item name="result" label="Описание">
            <Input.TextArea rows={4} />
          </Form.Item>
          <Button type="primary" htmlType="submit">Сохранить</Button>
        </Form>
      </Modal>
      <Modal
        title="Добавить услугу"
        open={isServiceModalOpen}
        onCancel={() => {
          setIsServiceModalOpen(false);
          serviceForm.resetFields();
        }}
        footer={null}
      >
        <Form form={serviceForm} layout="vertical" onFinish={handleCreateService}>
          <Form.Item name="name" label="Название" rules={[{ required: true }]}>
            <Input placeholder="Консультация терапевта" />
          </Form.Item>
          <Form.Item>
            <Button type="primary" htmlType="submit" block>
              Добавить
            </Button>
          </Form.Item>
        </Form>
      </Modal>
      <Modal
        title="Добавить специализацию"
        open={isSpecializationModalOpen}
        onCancel={() => {
          setIsSpecializationModalOpen(false);
          specializationForm.resetFields();
        }}
        footer={null}
      >
        <Form form={specializationForm} layout="vertical" onFinish={handleCreateSpecialization}>
          <Form.Item name="name" label="Название" rules={[{ required: true }]}>
            <Input placeholder="Терапевт" />
          </Form.Item>
          <Form.Item>
            <Button type="primary" htmlType="submit" block>
              Добавить
            </Button>
          </Form.Item>
        </Form>
      </Modal>
    </div>
  );
};
