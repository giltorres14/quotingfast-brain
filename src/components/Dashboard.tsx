import React, { useState, useEffect } from 'react';
import { Card, Row, Col, Statistic, Table, Tag, Typography, Space, Button } from 'antd';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, PieChart, Pie, Cell } from 'recharts';
import { UserOutlined, MailOutlined, PhoneOutlined, TrophyOutlined } from '@ant-design/icons';
import axios from 'axios';

const { Title, Text } = Typography;

interface LeadData {
  name: string;
  email: string;
  phone: string;
  source: string;
  notes?: string;
  timestamp?: Date;
}

interface DashboardProps {
  leads: LeadData[];
}

const Dashboard: React.FC<DashboardProps> = ({ leads }) => {
  const [brainStatus, setBrainStatus] = useState<'checking' | 'online' | 'offline'>('checking');

  useEffect(() => {
    checkBrainStatus();
  }, []);

  const checkBrainStatus = async () => {
    try {
      const response = await axios.get('https://quotingfast-brain-v2.onrender.com/test');
      setBrainStatus('online');
    } catch (error) {
      setBrainStatus('offline');
    }
  };

  // Generate sample data for charts
  const today = new Date();
  const chartData = Array.from({ length: 7 }, (_, i) => {
    const date = new Date(today);
    date.setDate(date.getDate() - (6 - i));
    const leadsCount = Math.floor(Math.random() * 10) + leads.filter(lead => 
      lead.timestamp && new Date(lead.timestamp).toDateString() === date.toDateString()
    ).length;
    
    return {
      date: date.toLocaleDateString('en-US', { weekday: 'short' }),
      leads: leadsCount,
      sms: Math.floor(leadsCount * 0.8),
      conversions: Math.floor(leadsCount * 0.23)
    };
  });

  const sourceData = leads.reduce((acc: any, lead) => {
    const source = lead.source || 'unknown';
    acc[source] = (acc[source] || 0) + 1;
    return acc;
  }, {});

  const pieData = Object.entries(sourceData).map(([source, count], index) => ({
    name: source.charAt(0).toUpperCase() + source.slice(1),
    value: count as number,
    fill: ['#1890ff', '#52c41a', '#faad14', '#f5222d', '#722ed1', '#13c2c2'][index % 6]
  }));

  const columns = [
    {
      title: 'Name',
      dataIndex: 'name',
      key: 'name',
      render: (name: string) => (
        <Space>
          <UserOutlined />
          {name}
        </Space>
      ),
    },
    {
      title: 'Email',
      dataIndex: 'email',
      key: 'email',
      render: (email: string) => (
        <Space>
          <MailOutlined />
          {email}
        </Space>
      ),
    },
    {
      title: 'Phone',
      dataIndex: 'phone',
      key: 'phone',
      render: (phone: string) => (
        <Space>
          <PhoneOutlined />
          {phone}
        </Space>
      ),
    },
    {
      title: 'Source',
      dataIndex: 'source',
      key: 'source',
      render: (source: string) => {
        const colors: { [key: string]: string } = {
          website: 'blue',
          'social-media': 'green',
          referral: 'orange',
          advertising: 'red',
          'cold-call': 'purple',
          event: 'cyan',
          other: 'default'
        };
        return <Tag color={colors[source] || 'default'}>{source}</Tag>;
      },
    },
    {
      title: 'Time',
      dataIndex: 'timestamp',
      key: 'timestamp',
      render: (timestamp?: Date) => timestamp ? new Date(timestamp).toLocaleString() : 'N/A',
    },
  ];

  return (
    <div>
      <Row gutter={[24, 24]}>
        <Col span={24}>
          <Title level={2}>📊 Brain Lead Flow Dashboard</Title>
          <Text type="secondary">Real-time insights into your lead processing system</Text>
        </Col>
      </Row>

      {/* Status Cards */}
      <Row gutter={[16, 16]} style={{ marginBottom: 24 }}>
        <Col xs={24} sm={12} md={6}>
          <Card>
            <Statistic
              title="🧠 Brain Status"
              value={brainStatus === 'online' ? 'Online' : brainStatus === 'offline' ? 'Offline' : 'Checking'}
              valueStyle={{ 
                color: brainStatus === 'online' ? '#52c41a' : brainStatus === 'offline' ? '#f5222d' : '#faad14' 
              }}
              prefix={brainStatus === 'online' ? '✅' : brainStatus === 'offline' ? '❌' : '🔄'}
            />
          </Card>
        </Col>
        <Col xs={24} sm={12} md={6}>
          <Card>
            <Statistic
              title="📋 Total Leads"
              value={leads.length}
              prefix={<UserOutlined />}
              valueStyle={{ color: '#1890ff' }}
            />
          </Card>
        </Col>
        <Col xs={24} sm={12} md={6}>
          <Card>
            <Statistic
              title="📱 SMS Sent"
              value={Math.floor(leads.length * 0.8)}
              suffix={`/ ${leads.length}`}
              valueStyle={{ color: '#52c41a' }}
            />
          </Card>
        </Col>
        <Col xs={24} sm={12} md={6}>
          <Card>
            <Statistic
              title="🎯 Est. Conversions"
              value={Math.floor(leads.length * 0.23)}
              suffix={`(${Math.round(23)}%)`}
              prefix={<TrophyOutlined />}
              valueStyle={{ color: '#faad14' }}
            />
          </Card>
        </Col>
      </Row>

      {/* Charts */}
      <Row gutter={[16, 16]} style={{ marginBottom: 24 }}>
        <Col xs={24} lg={16}>
          <Card title="📈 Lead Flow Trends (Last 7 Days)">
            <ResponsiveContainer width="100%" height={300}>
              <LineChart data={chartData}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="date" />
                <YAxis />
                <Tooltip />
                <Line type="monotone" dataKey="leads" stroke="#1890ff" strokeWidth={2} name="Leads" />
                <Line type="monotone" dataKey="sms" stroke="#52c41a" strokeWidth={2} name="SMS Sent" />
                <Line type="monotone" dataKey="conversions" stroke="#faad14" strokeWidth={2} name="Conversions" />
              </LineChart>
            </ResponsiveContainer>
          </Card>
        </Col>
        <Col xs={24} lg={8}>
          <Card title="🎯 Lead Sources">
            {pieData.length > 0 ? (
              <ResponsiveContainer width="100%" height={300}>
                <PieChart>
                  <Pie
                    data={pieData}
                    cx="50%"
                    cy="50%"
                    outerRadius={80}
                    dataKey="value"
                    label={({ name, value }) => `${name}: ${value}`}
                  >
                    {pieData.map((entry, index) => (
                      <Cell key={`cell-${index}`} fill={entry.fill} />
                    ))}
                  </Pie>
                  <Tooltip />
                </PieChart>
              </ResponsiveContainer>
            ) : (
              <div style={{ textAlign: 'center', padding: '60px 0' }}>
                <Text type="secondary">No lead data yet. Start capturing leads!</Text>
              </div>
            )}
          </Card>
        </Col>
      </Row>

      {/* Brain API Test */}
      <Row gutter={[16, 16]} style={{ marginBottom: 24 }}>
        <Col span={24}>
          <Card title="🧠 Brain API Connection">
            <Space direction="vertical" style={{ width: '100%' }}>
              <Text>
                <strong>API Endpoint:</strong> https://quotingfast-brain-v2.onrender.com
              </Text>
              <Text>
                <strong>Status:</strong> 
                <Tag color={brainStatus === 'online' ? 'green' : 'red'} style={{ marginLeft: 8 }}>
                  {brainStatus === 'online' ? '✅ Connected' : '❌ Disconnected'}
                </Tag>
              </Text>
              <Button onClick={checkBrainStatus} loading={brainStatus === 'checking'}>
                🔄 Test Connection
              </Button>
            </Space>
          </Card>
        </Col>
      </Row>

      {/* Recent Leads Table */}
      <Row gutter={[16, 16]}>
        <Col span={24}>
          <Card title={`📋 Recent Leads (${leads.length})`}>
            {leads.length > 0 ? (
              <Table
                dataSource={leads}
                columns={columns}
                rowKey={(record, index) => `${record.email}-${index}`}
                pagination={{ pageSize: 10 }}
                scroll={{ x: 800 }}
              />
            ) : (
              <div style={{ textAlign: 'center', padding: '60px 0' }}>
                <UserOutlined style={{ fontSize: 48, color: '#d9d9d9', marginBottom: 16 }} />
                <br />
                <Text type="secondary" style={{ fontSize: 16 }}>
                  No leads captured yet. Use the lead capture form to get started!
                </Text>
              </div>
            )}
          </Card>
        </Col>
      </Row>
    </div>
  );
};

export default Dashboard;