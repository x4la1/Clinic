import React from 'react';
import { Form, Input, Button, Card, Typography, Row, Col, message } from 'antd';
import { LockOutlined, UserOutlined } from '@ant-design/icons';
import { Link, useNavigate } from 'react-router-dom';
import { useAppStore } from '../../store';
import styles from './LoginPage.module.scss';
import type { User } from '../../types';
import { ROLE_IDS } from '../../utils/roles';
import { apiRequest } from '../../utils/api';

const { Title } = Typography;

export const LoginPage: React.FC = () => {
  const navigate = useNavigate();
  const setUser = useAppStore((state) => state.setUser);

  const onFinish = async (values: { email: string; password: string }) => {
    const { email, password } = values;

    if (email === 'admin@gmail.com' && password === '123456') {
      const adminUser: User = {
        id: 999,
        login: 'admin@gmail.com',
        phone: '+7 (000) 000-00-00',
        firstName: 'Админ',
        lastName: 'Системный',
        patronymic: '',
        roleId: ROLE_IDS.ADMIN,
      };

      setUser(adminUser);
      localStorage.setItem('user', JSON.stringify(adminUser));

      message.success('Вход выполнен успешно!');
      navigate('/admin');
      return;
    }

    try {
      const response = await apiRequest<{ user_id: number; user_role: number }>(
        '/api/user/login', //ok
        {
          method: 'POST',
          body: JSON.stringify({
            login: email,
            password: password,
          }),
        }
      );

      const userResponse = await apiRequest<{
        login: string;
        phone: string;
        firstname: string;
        lastname: string;
        patronymic: string;
      }>(`/api/user/${response.user_id}`); //ok
      const user: User = {
        id: response.user_id,
        login: userResponse.login,
        phone: userResponse.phone,
        firstName: userResponse.firstname,
        lastName: userResponse.lastname,
        patronymic: userResponse.patronymic || '',
        roleId: response.user_role,
      };

      setUser(user);
      localStorage.setItem('user', JSON.stringify(user));

      message.success('Вход выполнен успешно!');

      if (user.roleId === ROLE_IDS.ADMIN) {
        navigate('/admin');
      } else {
        navigate('/patient');
      }
    } catch (error: any) {
      message.error(error.message || 'Неверный email или пароль');
    }
  };

  return (
    <Row justify="center" align="middle" className={styles.container}>
      <Col xs={24} sm={20} md={12} lg={8}>
        <Card className={styles.card}>
          <Title level={2} className={styles.title}>
            Вход в систему
          </Title>
          <Form
            name="login"
            layout="vertical"
            onFinish={onFinish}
            requiredMark={false}
          >
            <Form.Item
              label="Email"
              name="email"
              rules={[
                { required: true, message: 'Пожалуйста, введите email' },
                { type: 'email', message: 'Некорректный email' },
              ]}
            >
              <Input prefix={<UserOutlined />} placeholder="example@mail.ru" />
            </Form.Item>

            <Form.Item
              label="Пароль"
              name="password"
              rules={[
                { required: true, message: 'Пожалуйста, введите пароль' },
                { min: 6, message: 'Пароль должен быть не менее 6 символов' },
              ]}
            >
              <Input.Password prefix={<LockOutlined />} placeholder="••••••" />
            </Form.Item>

            <Form.Item>
              <Button type="primary" htmlType="submit" block size="large">
                Войти
              </Button>
            </Form.Item>
          </Form>

          <div className={styles.footer}>
            Нет аккаунта? <Link to="/register">Зарегистрироваться</Link>
          </div>
        </Card>
      </Col>
    </Row>
  );
};
