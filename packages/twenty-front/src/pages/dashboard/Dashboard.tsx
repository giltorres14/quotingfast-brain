import React from 'react';
import styled from '@emotion/styled';

const DashboardContainer = styled.div`
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem;
`;

const DashboardContent = styled.div`
  max-width: 1200px;
  margin: 0 auto;
  background: white;
  border-radius: 16px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  overflow: hidden;
`;

const DashboardHeader = styled.div`
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 2rem;
  text-align: center;
`;

const HeaderIcon = styled.div`
  width: 80px;
  height: 80px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1rem;
  font-size: 2rem;
`;

const DashboardTitle = styled.h1`
  font-size: 2.5rem;
  font-weight: bold;
  margin: 0;
  margin-bottom: 0.5rem;
`;

const DashboardSubtitle = styled.p`
  font-size: 1.1rem;
  opacity: 0.9;
  margin: 0;
`;

const DashboardBody = styled.div`
  padding: 2rem;
`;

const StatsGrid = styled.div`
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
`;

const StatCard = styled.div`
  background: #f8fafc;
  border-radius: 12px;
  padding: 1.5rem;
  border: 1px solid #e2e8f0;
  transition: all 0.3s ease;

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
  }
`;

const StatTitle = styled.h3`
  font-size: 0.875rem;
  font-weight: 600;
  color: #64748b;
  margin: 0 0 0.5rem 0;
  text-transform: uppercase;
  letter-spacing: 0.05em;
`;

const StatValue = styled.div`
  font-size: 2rem;
  font-weight: bold;
  color: #1e293b;
  margin-bottom: 0.5rem;
`;

const StatChange = styled.div<{ positive?: boolean }>`
  font-size: 0.875rem;
  color: ${props => props.positive ? '#10b981' : '#ef4444'};
  font-weight: 500;
`;

const ContentGrid = styled.div`
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 2rem;
  margin-top: 2rem;

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
  }
`;

const MainContent = styled.div`
  background: #f8fafc;
  border-radius: 12px;
  padding: 1.5rem;
  border: 1px solid #e2e8f0;
`;

const Sidebar = styled.div`
  background: #f8fafc;
  border-radius: 12px;
  padding: 1.5rem;
  border: 1px solid #e2e8f0;
`;

const SectionTitle = styled.h2`
  font-size: 1.25rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 1rem 0;
`;

const ActivityList = styled.div`
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
`;

const ActivityItem = styled.div`
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background: white;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
`;

const ActivityIcon = styled.div`
  width: 32px;
  height: 32px;
  background: #667eea;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 0.875rem;
`;

const ActivityContent = styled.div`
  flex: 1;
`;

const ActivityTitle = styled.div`
  font-weight: 500;
  color: #1e293b;
  font-size: 0.875rem;
`;

const ActivityTime = styled.div`
  font-size: 0.75rem;
  color: #64748b;
`;

export const Dashboard = () => {
  return (
    <DashboardContainer>
      <DashboardContent>
        <DashboardHeader>
          <HeaderIcon>🧠</HeaderIcon>
          <DashboardTitle>The Brain</DashboardTitle>
          <DashboardSubtitle>Your intelligent dashboard for insights and analytics</DashboardSubtitle>
        </DashboardHeader>
        
        <DashboardBody>
          <StatsGrid>
            <StatCard>
              <StatTitle>Total Users</StatTitle>
              <StatValue>1,234</StatValue>
              <StatChange positive>+12.5% from last month</StatChange>
            </StatCard>
            
            <StatCard>
              <StatTitle>Active Sessions</StatTitle>
              <StatValue>567</StatValue>
              <StatChange positive>+8.3% from last week</StatChange>
            </StatCard>
            
            <StatCard>
              <StatTitle>Data Processed</StatTitle>
              <StatValue>2.4TB</StatValue>
              <StatChange positive>+15.2% from yesterday</StatChange>
            </StatCard>
            
            <StatCard>
              <StatTitle>System Health</StatTitle>
              <StatValue>99.9%</StatValue>
              <StatChange positive>All systems operational</StatChange>
            </StatCard>
          </StatsGrid>

          <ContentGrid>
            <MainContent>
              <SectionTitle>Recent Activity</SectionTitle>
              <ActivityList>
                <ActivityItem>
                  <ActivityIcon>📊</ActivityIcon>
                  <ActivityContent>
                    <ActivityTitle>New analytics report generated</ActivityTitle>
                    <ActivityTime>2 minutes ago</ActivityTime>
                  </ActivityContent>
                </ActivityItem>
                
                <ActivityItem>
                  <ActivityIcon>👤</ActivityIcon>
                  <ActivityContent>
                    <ActivityTitle>User registration completed</ActivityTitle>
                    <ActivityTime>5 minutes ago</ActivityTime>
                  </ActivityContent>
                </ActivityItem>
                
                <ActivityItem>
                  <ActivityIcon>🔧</ActivityIcon>
                  <ActivityContent>
                    <ActivityTitle>System maintenance completed</ActivityTitle>
                    <ActivityTime>1 hour ago</ActivityTime>
                  </ActivityContent>
                </ActivityItem>
                
                <ActivityItem>
                  <ActivityIcon>📈</ActivityIcon>
                  <ActivityContent>
                    <ActivityTitle>Performance metrics updated</ActivityTitle>
                    <ActivityTime>2 hours ago</ActivityTime>
                  </ActivityContent>
                </ActivityItem>
                
                <ActivityItem>
                  <ActivityIcon>🔄</ActivityIcon>
                  <ActivityContent>
                    <ActivityTitle>Data sync completed</ActivityTitle>
                    <ActivityTime>3 hours ago</ActivityTime>
                  </ActivityContent>
                </ActivityItem>
              </ActivityList>
            </MainContent>
            
            <Sidebar>
              <SectionTitle>Quick Actions</SectionTitle>
              <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
                <button style={{
                  padding: '0.75rem 1rem',
                  background: '#667eea',
                  color: 'white',
                  border: 'none',
                  borderRadius: '8px',
                  cursor: 'pointer',
                  fontWeight: '500',
                  transition: 'background 0.2s'
                }}>
                  Generate Report
                </button>
                <button style={{
                  padding: '0.75rem 1rem',
                  background: '#10b981',
                  color: 'white',
                  border: 'none',
                  borderRadius: '8px',
                  cursor: 'pointer',
                  fontWeight: '500',
                  transition: 'background 0.2s'
                }}>
                  Export Data
                </button>
                <button style={{
                  padding: '0.75rem 1rem',
                  background: '#f59e0b',
                  color: 'white',
                  border: 'none',
                  borderRadius: '8px',
                  cursor: 'pointer',
                  fontWeight: '500',
                  transition: 'background 0.2s'
                }}>
                  System Settings
                </button>
              </div>
            </Sidebar>
          </ContentGrid>
        </DashboardBody>
      </DashboardContent>
    </DashboardContainer>
  );
};