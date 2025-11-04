import { useEffect, useState } from 'react';
import axios from 'axios';

interface DashboardStats {
  total_revenue?: number;
  active_bookings?: number;
  [key: string]: any;
}

export default function OwnerDashboard() {
  const [stats, setStats] = useState<DashboardStats | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchStats = async () => {
      try {
        const response = await axios.get('/api/owner/dashboard/stats');
        setStats(response.data);
      } catch (error) {
        console.error('Error fetching stats:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchStats();
  }, []);

  if (loading) return <div>Loading...</div>;
  if (!stats) return <div>No data available</div>;

  return (
    <div className="dashboard-container">
      <h1>Owner Dashboard</h1>
      
      <div className="stats-grid">
        <div className="stat-card">
          <h3>Total Revenue</h3>
          <p className="stat-value">${stats.total_revenue?.toLocaleString()}</p>
        </div>
        
        <div className="stat-card">
          <h3>Active Bookings</h3>
          <p className="stat-value">{stats.active_bookings}</p>
        </div>
        
        <div className="stat-card">
          <h3>Total Properties</h3>
          <p className="stat-value">{stats.total_properties}</p>
        </div>
        
        <div className="stat-card">
          <h3>Occupancy Rate</h3>
          <p className="stat-value">{stats.occupancy_rate}%</p>
        </div>
      </div>
    </div>
  );
}
