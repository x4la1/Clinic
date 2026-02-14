export type UserRole = 'guest' | 'patient' | 'admin';

export interface User {
  id: number;
  login: string;
  phone: string;
  firstName: string;
  lastName: string;
  patronymic: string;
  roleId: number;
}

export interface Role {
  id: number;
  name: UserRole;
}

export interface Clinic {
  id: number;
  name: string;
  address: string;
  phone: string;
  email: string;
  imagePath: string;
}

export interface Cabinet {
  id: number;
  clinicId: number;
  number: string;
  description: string;
}

export interface AuthState {
  user: User | null;
  isAuthenticated: boolean;
}

export interface Specialization {
  id: number;
  name: string;
}

export interface Staff {
  id: number;
  firstName: string;
  lastName: string;
  patronymic: string;
  phone: string;
  experience: string;
  experienceYears: number;
  clinic: Clinic;
  cabinet: Cabinet | null;
  specializations: Specialization[];
  services: Service[];
}

export interface Service {
  id: number;
  name: string;
}

export interface AppointmentStatus {
  id: number;
  name: string;
}

export interface Appointment {
  id: number;
  date: string;
  result: string;
  status: AppointmentStatus;
  staff: {
    id: number;
    firstName: string;
    lastName: string;
    patronymic: string;
  };
  service: {
    id: number;
    name: string;
  };
}

export interface TimeSlot {
  id: number;
  staffId: number;
  slot: string;
}

export interface Review {
  id: number;
  userId: number;
  clinicId: number;
  description: string;
  status: 'new' | 'approved' | 'rejected';
}
