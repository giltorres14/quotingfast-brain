import React, { useState } from 'react';
import { Layout, Card, Form, Input, Button, Select, message, Typography, Space, Divider } from 'antd';
import { UserOutlined, MailOutlined, PhoneOutlined, GlobalOutlined } from '@ant-design/icons';
import axios from 'axios';
import Dashboard from './components/Dashboard';
import './App.css';

const { Header, Content } = Layout;
const { Title, Text } = Typography;
const { Option } = Select;

interface LeadData {
  name: string;
  email: string;
  phone: string;
  source: string;
  notes?: string;
  timestamp?: Date;
}

function App() {
  const [form] = Form.useForm();
  const [loading, setLoading] = useState(false);
  const [leads, setLeads] = useState<LeadData[]>([]);
  const [activeTab, setActiveTab] = useState<'capture' | 'dashboard'>('capture');

  const handleSubmit = async (values: LeadData) => {
    setLoading(true);
    try {
      // Send to Brain API
      const response = await axios.post('https://quotingfast-brain-v2.onrender.com/test-lead-data', {
        name: values.name,
        email: values.email,
        phone: values.phone,
        source: values.source,
        notes: values.notes,
        timestamp: new Date().toISOString()
      });

      console.log('Brain API Response:', response.data);
      
      // Add to local leads for dashboard
      setLeads(prev => [...prev, { ...values, timestamp: new Date() } as any]);
      
      message.success('🎯 Lead captured and sent to Brain API!');
      form.resetFields();
      
      // Auto-switch to dashboard to see results
      setTimeout(() => setActiveTab('dashboard'), 1000);
      
    } catch (error) {
      console.error('Error sending lead to Brain:', error);
      message.error('Failed to process lead. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <Layout style={{ minHeight: '100vh', background: '#f0f2f5' }}>
      <Header style={{ 
        background: '#1890ff', 
        display: 'flex', 
        alignItems: 'center', 
        justifyContent: 'space-between' 
      }}>
        <Title level={3} style={{ color: 'white', margin: 0 }}>
          🧠 Brain Lead Flow
        </Title>
        <Space>
          <Button 
            type={activeTab === 'capture' ? 'primary' : 'default'}
            ghost={activeTab !== 'capture'}
            onClick={() => setActiveTab('capture')}
          >
            📝 Capture Leads
          </Button>
          <Button 
            type={activeTab === 'dashboard' ? 'primary' : 'default'}
            ghost={activeTab !== 'dashboard'}
            onClick={() => setActiveTab('dashboard')}
          >
            📊 Dashboard
          </Button>
        </Space>
      </Header>

      <Content style={{ padding: '24px' }}>
        {activeTab === 'capture' ? (
          <div style={{ maxWidth: 600, margin: '0 auto' }}>
            <Card>
              <Title level={2} style={{ textAlign: 'center', marginBottom: 24 }}>
                🎯 Capture New Lead
              </Title>
              <Text type="secondary" style={{ display: 'block', textAlign: 'center', marginBottom: 32 }}>
                Submit leads directly to The Brain for instant processing and SMS routing
              </Text>

              <Form
                form={form}
                layout="vertical"
                onFinish={handleSubmit}
                size="large"
              >
                <Form.Item
                  name="name"
                  label="Full Name"
                  rules={[{ required: true, message: 'Please enter the lead name' }]}
                >
                  <Input 
                    prefix={<UserOutlined />} 
                    placeholder="Enter full name"
                  />
                </Form.Item>

                <Form.Item
                  name="email"
                  label="Email Address"
                  rules={[
                    { required: true, message: 'Please enter email address' },
                    { type: 'email', message: 'Please enter a valid email' }
                  ]}
                >
                  <Input 
                    prefix={<MailOutlined />} 
                    placeholder="Enter email address"
                  />
                </Form.Item>

                <Form.Item
                  name="phone"
                  label="Phone Number"
                  rules={[{ required: true, message: 'Please enter phone number' }]}
                >
                  <Input 
                    prefix={<PhoneOutlined />} 
                    placeholder="Enter phone number"
                  />
                </Form.Item>

                <Form.Item
                  name="source"
                  label="Lead Source"
                  rules={[{ required: true, message: 'Please select lead source' }]}
                >
                  <Select 
                    placeholder="Select lead source"
                    suffixIcon={<GlobalOutlined />}
                  >
                    <Option value="website">🌐 Website</Option>
                    <Option value="social-media">📱 Social Media</Option>
                    <Option value="referral">👥 Referral</Option>
                    <Option value="advertising">📺 Advertising</Option>
                    <Option value="cold-call">📞 Cold Call</Option>
                    <Option value="event">🎪 Event</Option>
                    <Option value="other">❓ Other</Option>
                  </Select>
                </Form.Item>

                <Form.Item
                  name="notes"
                  label="Notes (Optional)"
                >
                  <Input.TextArea 
                    rows={3}
                    placeholder="Any additional notes about this lead..."
                  />
                </Form.Item>

                <Divider />

                <Form.Item style={{ marginBottom: 0 }}>
                  <Button 
                    type="primary" 
                    htmlType="submit" 
                    loading={loading}
                    size="large"
                    block
                    style={{ height: 50 }}
                  >
                    {loading ? '🧠 Processing Lead...' : '🚀 Send to Brain API'}
                  </Button>
                </Form.Item>
              </Form>

              <div style={{ marginTop: 24, padding: 16, background: '#f6ffed', borderRadius: 8 }}>
                <Text type="secondary">
                  <strong>🔄 What happens next:</strong><br />
                  1. Lead sent to Brain API for processing<br />
                  2. Brain triggers SMS workflow automatically<br />
                  3. Lead data stored for reporting<br />
                  4. Results visible in dashboard
                </Text>
              </div>
            </Card>
          </div>
        ) : (
          <Dashboard leads={leads} />
        )}
      </Content>
    </Layout>
  );
}

export default App;