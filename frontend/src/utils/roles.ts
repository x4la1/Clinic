export const ROLE_IDS = {
  GUEST: 1,
  PATIENT: 2,
  ADMIN: 3,
} as const;

export const getRoleName = (roleId: number): 'guest' | 'patient' | 'admin' => {
  switch (roleId) {
    case ROLE_IDS.PATIENT: return 'patient';
    case ROLE_IDS.ADMIN: return 'admin';
    default: return 'guest';
  }
};

export const getRoleId = (roleName: string): number => {
  switch (roleName) {
    case 'patient': return ROLE_IDS.PATIENT;
    case 'admin': return ROLE_IDS.ADMIN;
    default: return ROLE_IDS.GUEST;
  }
};