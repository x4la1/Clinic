export const apiRequest = async <T>(
  url: string,
  options: RequestInit = {}
): Promise<T> => {
  const userStr = localStorage.getItem('user');
  const user = userStr ? JSON.parse(userStr) : null;

  const headers = {
    'Content-Type': 'application/json',
    ...(user && {
      'X-User-ID': String(user.id),
      'X-User-Role': String(user.roleId),
    }),
    ...options.headers,
  };

  try {
    const response = await fetch(`http://localhost:7777${url}`, {
      ...options,
      headers,
    });

    if (!response.ok) {
      let errorMessage = 'Ошибка сервера';

      if (response.status === 400) {
        errorMessage = 'Некорректные данные запроса';
      } else if (response.status === 401) {
        errorMessage = 'Неавторизован';
      } else if (response.status === 403) {
        errorMessage = 'Доступ запрещён';
      }

      throw new Error(errorMessage);
    }

    return await response.json();
  } catch (error) {
    console.error('API Error:', error);
    throw error;
  }


};

export const getStatuses = async (): Promise<{ id: number; name: string }[]> => {
  return await apiRequest('/api/statuses');
};

export const createAppointment = async (appointmentData: {
  user_id: number;
  staff_id: number;
  status_id: number;
  service_id: number;
  date: string;
}) => {
  return await apiRequest('/api/appointment/create', {
    method: 'POST',
    body: JSON.stringify(appointmentData),
  });
};

export const createService = async (name: string) => {
  return await apiRequest('/api/service/create', {
    method: 'POST',
    body: JSON.stringify({ name }),
  });
};

export const deleteService = async (id: number) => {
  return await apiRequest('/api/service/delete', {
    method: 'POST',
    body: JSON.stringify({ id }),
  });
};

export const createSpecialization = async (name: string) => {
  return await apiRequest('/api/service/specialization/create', {
    method: 'POST',
    body: JSON.stringify({ name }),
  });
};

export const deleteSpecialization = async (id: number) => {
  return await apiRequest('/api/service/specialization/delete', {
    method: 'POST',
    body: JSON.stringify({ id }),
  });
};
