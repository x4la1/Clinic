// src/store/index.ts
import { create } from 'zustand';
import type { User, Clinic, AuthState } from '../types';

interface AppStore {
  auth: AuthState;
  clinics: Clinic[];
  setUser: (user: User | null) => void;
  setClinics: (clinics: Clinic[]) => void;
}

const getInitialAuthState = (): AuthState => {
  if (typeof window === 'undefined') {
    return { user: null, isAuthenticated: false };
  }

  const userStr = localStorage.getItem('user');

  if (userStr) {
    try {
      const user = JSON.parse(userStr);
      if (
        user &&
        typeof user.id === 'number' &&
        typeof user.roleId === 'number'
      ) {
        return { 
          user, 
          isAuthenticated: true 
        };
      }
    } catch (e) {
      console.warn('Invalid user data in localStorage');
    }
  }

  return { user: null, isAuthenticated: false };
};

export const useAppStore = create<AppStore>((set) => ({
  auth: getInitialAuthState(),
  clinics: [],

  setUser: (user) => {
    if (user) {
      localStorage.setItem('user', JSON.stringify(user));
    } else {
      localStorage.removeItem('user');
    }
    set({
      auth: {
        user,
        isAuthenticated: !!user,
      },
    });
  },

  setClinics: (clinics) => {
    localStorage.setItem('clinics', JSON.stringify(clinics));
    set({ clinics });
  },
}));