import { BrowserRouter as Router, Routes, Route, useLocation } from 'react-router-dom';
import { Layout } from './components/Layout/Layout';
import { HomePage } from './pages/HomePage/HomePage';
import './App.scss';
import { RegisterPage } from './pages/Registration/RegisterPage';
import { LoginPage } from './pages/LoginPage/LoginPage';
import { PatientDashboardPage } from './pages/PatientDashboardPage/PatientDashboardPage';
import { BookAppointmentPage } from './pages/BookAppointmentPage/BookAppointmentPage';
import { AdminDashboardPage } from './pages/AdminDashboardPage/AdminDashboardPage';
import { PatientAppointmentsPage } from './pages/PatientAppointmentsPage/PatientAppointmentsPage';
import { ClinicDetailPage } from './pages/ClinicDetailPage/ClinicDetailPage';
import { DoctorDetailPage } from './pages/DoctorDetailPage/DoctorDetailPage';
import { DoctorsSearchPage } from './pages/DoctorsSearchPage/DoctorsSearchPage';
import { PatientNotificationsPage } from './pages/PatientNotificationsPage/PatientNotificationsPage';
import { AdminReportsPage } from './pages/AdminReportsPage/AdminReportsPage';
import { AdminReviewsPage } from './pages/AdminReviewsPage/AdminReviewsPage';
import { useEffect } from 'react';

const ScrollToTop = () => {
  const { pathname } = useLocation();

  useEffect(() => {
    window.scrollTo(0, 0);
  }, [pathname]);

  return null;
};
function App() {
  return (
    <Router>
      <Layout>
        <ScrollToTop />
        <Routes>
          <Route path="/" element={<HomePage />} />
          <Route path="/register" element={<RegisterPage />} />
          <Route path="/login" element={<LoginPage />} />
          <Route path="/patient" element={<PatientDashboardPage />} />
          <Route path="/patient/book" element={<BookAppointmentPage />} />
          <Route path="/admin" element={<AdminDashboardPage />} />
          <Route path="/patient/appointments" element={<PatientAppointmentsPage />} />
          <Route path="/clinics/:id" element={<ClinicDetailPage />} />
          <Route path="/doctors/:id" element={<DoctorDetailPage />} />
          <Route path="/doctors" element={<DoctorsSearchPage />} />
          <Route path="/patient/notifications" element={<PatientNotificationsPage />} />
          <Route path="/admin/reports" element={<AdminReportsPage />} />
          <Route path="/admin/reviews" element={<AdminReviewsPage />} />
        </Routes>
      </Layout>
    </Router>
  );
}

export default App;