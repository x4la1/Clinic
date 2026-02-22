export const ROLE_IDS = {
  PATIENT: 1,
  ADMIN: 2,
  GUEST: 3,
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
