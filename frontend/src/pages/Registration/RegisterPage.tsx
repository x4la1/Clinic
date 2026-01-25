import React from 'react';
import { Form, Input, Button, Typography, Card, Row, Col, message } from 'antd';
import { Link, useNavigate } from 'react-router-dom';
import { UserOutlined, MailOutlined, PhoneOutlined, LockOutlined } from '@ant-design/icons';
import styles from './RegisterPage.module.scss';
import { useAppStore } from '../../store/index';
import { apiRequest } from '../../utils/api';

const { Title } = Typography;

const cyrillicValidator = (value: string, fieldName: string) => {
  if (!value) return Promise.reject(new Error(`Поле "${fieldName}" обязательно`));
  const cyrillicRegex = /^[а-яА-ЯёЁ\s\-]+$/;
  if (!cyrillicRegex.test(value)) {
    return Promise.reject(new Error(`${fieldName} должно содержать только русские буквы`));
  }
  return Promise.resolve();
};

export const RegisterPage: React.FC = () => {
  const navigate = useNavigate();
  const setUser = useAppStore((state) => state.setUser);

  const onFinish = async (values: any) => {
    const { firstName, lastName, middleName, email, phone, password } = values;

    try {
      const response = await apiRequest<{ user_id: number; user_role: number }>(
        '/api/user/register', //ok
        {
          method: 'POST',
          body: JSON.stringify({
            login: email,
            password: password,
            phone: phone,
            firstname: firstName,
            lastname: lastName,
            patronymic: middleName || '',
          }),
        }
      );
      const user = {
        id: response.user_id,
        login: email,
        phone: phone,
        firstName: firstName,
        lastName: lastName,
        patronymic: middleName || '',
        roleId: response.user_role,
      };
      setUser(user);
      localStorage.setItem('user', JSON.stringify(user));

      message.success('Регистрация прошла успешно!');
      navigate('/patient');

    } catch (error: any) {
      message.error(error.message || 'Ошибка при регистрации');
    }
  };

  return (
    <Row justify="center" align="middle" className={styles.container}>
      <Col xs={24} sm={20} md={12} lg={8}>
        <Card className={styles.card}>
          <Title level={2} className={styles.title}>
            Регистрация
          </Title>
          <Form
            name="register"
            layout="vertical"
            onFinish={onFinish}
            requiredMark={false}
          >
            <Form.Item
              label="Фамилия"
              name="lastName"
              rules={[
                { required: true, message: 'Введите фамилию' },
                { validator: (_, value) => cyrillicValidator(value, 'Фамилия') },
              ]}
            >
              <Input prefix={<UserOutlined />} placeholder="Иванов" />
            </Form.Item>

            <Form.Item
              label="Имя"
              name="firstName"
              rules={[
                { required: true, message: 'Введите имя' },
                { validator: (_, value) => cyrillicValidator(value, 'Имя') },
              ]}
            >
              <Input prefix={<UserOutlined />} placeholder="Иван" />
            </Form.Item>

            <Form.Item
              label="Отчество (если есть)"
              name="middleName"
              rules={[
                {
                  validator: (_, value) =>
                    value ? cyrillicValidator(value, 'Отчество') : Promise.resolve(),
                },
              ]}
            >
              <Input placeholder="Иванович" />
            </Form.Item>

            <Form.Item
              label="Email"
              name="email"
              rules={[
                { required: true, message: 'Введите email' },
                { type: 'email', message: 'Некорректный email' },
              ]}
            >
              <Input prefix={<MailOutlined />} placeholder="ivanov@example.com" />
            </Form.Item>

            <Form.Item
              label="Телефон"
              name="phone"
              rules={[
                { required: true, message: 'Введите номер телефона' },
                {
                  pattern: /^[\+]?7\s?\(?\d{3}\)?[\s\-]?\d{3}[\s\-]?\d{2}[\s\-]?\d{2}$/,
                  message: 'Формат: +7 (999) 123-45-67',
                },
              ]}
            >
              <Input prefix={<PhoneOutlined />} placeholder="+7 (999) 123-45-67" />
            </Form.Item>

            <Form.Item
              label="Пароль"
              name="password"
              rules={[
                { required: true, message: 'Введите пароль' },
                { min: 6, message: 'Пароль должен быть не менее 6 символов' },
              ]}
            >
              <Input.Password prefix={<LockOutlined />} placeholder="••••••" />
            </Form.Item>

            <Form.Item>
              <Button type="primary" htmlType="submit" block size="large">
                Зарегистрироваться
              </Button>
            </Form.Item>
          </Form>

          <div className={styles.footer}>
            Уже есть аккаунт? <Link to="/login">Войти</Link>
          </div>
        </Card>
      </Col>
    </Row>
  );
};
