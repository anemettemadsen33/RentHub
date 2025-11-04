import React, { useEffect, useState } from 'react';

interface AnalyticsData {
  overview: any;
  recent_bookings: any[];
  revenue_stats: any;
  property_performance: any;
}

export const DashboardAnalytics: React.FC = () => {
  const [data, setData] = useState<AnalyticsData | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchAnalytics();
  }, []);

  const fetchAnalytics = async () => {
    try {
      const response = await fetch('/api/dashboard', {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      });
      const result = await response.json();
      setData(result);
    } catch (error) {
      console.error('Failed to fetch analytics:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return <div className="loading">Loading analytics...</div>;
  }

  if (!data) {
    return <div className="error">Failed to load analytics</div>;
  }

  return (
    <div className="dashboard-analytics">
      <h2>Dashboard Overview</h2>
      
      <div className="stats-grid">
        <div className="stat-card">
          <h3>Total Revenue</h3>
          <p className="stat-value">${data.revenue_stats?.total || 0}</p>
          <span className="stat-change positive">+12%</span>
        </div>
        
        <div className="stat-card">
          <h3>Total Bookings</h3>
          <p className="stat-value">{data.overview?.total_bookings || 0}</p>
          <span className="stat-change positive">+8%</span>
        </div>
        
        <div className="stat-card">
          <h3>Occupancy Rate</h3>
          <p className="stat-value">{data.overview?.occupancy_rate || 0}%</p>
          <span className="stat-change neutral">0%</span>
        </div>
        
        <div className="stat-card">
          <h3>Active Properties</h3>
          <p className="stat-value">{data.overview?.active_properties || 0}</p>
          <span className="stat-change positive">+2</span>
        </div>
      </div>
      
      <div className="recent-bookings">
        <h3>Recent Bookings</h3>
        <div className="bookings-list">
          {data.recent_bookings?.map((booking: any) => (
            <div key={booking.id} className="booking-item">
              <span>{booking.property_name}</span>
              <span>{booking.guest_name}</span>
              <span className={`status ${booking.status}`}>{booking.status}</span>
            </div>
          ))}
        </div>
      </div>
      
      <style jsx>{`
        .dashboard-analytics {
          padding: 2rem;
        }
        
        .stats-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
          gap: 1.5rem;
          margin: 2rem 0;
        }
        
        .stat-card {
          background: white;
          padding: 1.5rem;
          border-radius: 0.5rem;
          box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
          font-size: 0.875rem;
          color: #6b7280;
          margin-bottom: 0.5rem;
        }
        
        .stat-value {
          font-size: 2rem;
          font-weight: 600;
          color: #111827;
          margin: 0.5rem 0;
        }
        
        .stat-change {
          font-size: 0.875rem;
          font-weight: 500;
        }
        
        .stat-change.positive {
          color: #10b981;
        }
        
        .stat-change.negative {
          color: #ef4444;
        }
        
        .stat-change.neutral {
          color: #6b7280;
        }
        
        .recent-bookings {
          background: white;
          padding: 1.5rem;
          border-radius: 0.5rem;
          box-shadow: 0 1px 3px rgba(0,0,0,0.1);
          margin-top: 2rem;
        }
        
        .bookings-list {
          margin-top: 1rem;
        }
        
        .booking-item {
          display: flex;
          justify-content: space-between;
          padding: 1rem;
          border-bottom: 1px solid #e5e7eb;
        }
        
        .booking-item:last-child {
          border-bottom: none;
        }
        
        .status {
          padding: 0.25rem 0.75rem;
          border-radius: 9999px;
          font-size: 0.875rem;
          font-weight: 500;
        }
        
        .status.confirmed {
          background: #d1fae5;
          color: #065f46;
        }
        
        .status.pending {
          background: #fed7aa;
          color: #92400e;
        }
      `}</style>
    </div>
  );
};

export default DashboardAnalytics;
