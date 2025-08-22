@extends('layouts.app')

@section('content')
<div style="padding: 20px; background: #f8fafc; min-height: 100vh;">
    
    <!-- Header -->
    <div style="margin-bottom: 30px;">
        <h1 style="font-size: 2rem; font-weight: 700; color: #1a202c; margin: 0;">All Leads</h1>
        <p style="color: #64748b; margin-top: 5px;">Manage and track your auto insurance leads</p>
    </div>

    <!-- Date Filter Buttons -->
    <div style="margin-bottom: 30px;">
        <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #475569;">Stats Period:</label>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button class="date-filter-btn active" data-period="today" style="padding: 10px 20px; border-radius: 8px; border: none; background: #3b82f6; color: white; font-weight: 500; cursor: pointer; transition: all 0.2s;">
                Today
            </button>
            <button class="date-filter-btn" data-period="yesterday" style="padding: 10px 20px; border-radius: 8px; border: 1px solid #e2e8f0; background: white; color: #64748b; font-weight: 500; cursor: pointer; transition: all 0.2s;">
                Yesterday
            </button>
            <button class="date-filter-btn" data-period="last7" style="padding: 10px 20px; border-radius: 8px; border: 1px solid #e2e8f0; background: white; color: #64748b; font-weight: 500; cursor: pointer; transition: all 0.2s;">
                Last 7 Days
            </button>
            <button class="date-filter-btn" data-period="last30" style="padding: 10px 20px; border-radius: 8px; border: 1px solid #e2e8f0; background: white; color: #64748b; font-weight: 500; cursor: pointer; transition: all 0.2s;">
                Last 30 Days
            </button>
            <button class="date-filter-btn" data-period="custom" style="padding: 10px 20px; border-radius: 8px; border: 1px solid #e2e8f0; background: white; color: #64748b; font-weight: 500; cursor: pointer; transition: all 0.2s;">
                Custom Range
            </button>
        </div>
        
        <!-- Custom Date Range (hidden by default) -->
        <div id="customDateRange" style="display: none; margin-top: 15px; padding: 15px; background: white; border-radius: 8px; border: 1px solid #e2e8f0;">
            <div style="display: flex; gap: 15px; align-items: end;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-size: 0.875rem; color: #64748b;">Start Date</label>
                    <input type="date" id="startDate" style="padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-size: 0.875rem; color: #64748b;">End Date</label>
                    <input type="date" id="endDate" style="padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px;">
                </div>
                <button onclick="applyCustomRange()" style="padding: 8px 20px; background: #3b82f6; color: white; border: none; border-radius: 6px; cursor: pointer;">
                    Apply
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <!-- New Leads Card -->
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="text-align: center;">
                <div id="newLeadsCount" style="font-size: 3.5rem; font-weight: 700; color: #3b82f6; line-height: 1;">947</div>
                <div style="color: #64748b; margin-top: 10px; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em;">NEW LEADS</div>
            </div>
        </div>
        
        <!-- Sent to Vici Card -->
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="text-align: center;">
                <div id="sentToViciCount" style="font-size: 3.5rem; font-weight: 700; color: #10b981; line-height: 1;">0</div>
                <div style="color: #64748b; margin-top: 10px; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em;">SENT TO VICI</div>
            </div>
        </div>
        
        <!-- Stuck in Queue Card -->
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="text-align: center;">
                <div id="stuckInQueueCount" style="font-size: 3.5rem; font-weight: 700; color: #f59e0b; line-height: 1;">222</div>
                <div style="color: #64748b; margin-top: 10px; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em;">STUCK IN QUEUE</div>
            </div>
        </div>
        
        <!-- Conversion Rate Card -->
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="text-align: center;">
                <div id="conversionRate" style="font-size: 3.5rem; font-weight: 700; color: #8b5cf6; line-height: 1;">0%</div>
                <div style="color: #64748b; margin-top: 10px; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em;">CONVERSION RATE</div>
            </div>
        </div>
    </div>

    <!-- Leads Table -->
    <div style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="padding: 20px; border-bottom: 1px solid #e2e8f0;">
            <h2 style="font-size: 1.25rem; font-weight: 600; color: #1a202c;">Recent Leads</h2>
        </div>
        
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc;">
                        <th style="padding: 12px 20px; text-align: left; font-weight: 600; color: #475569; font-size: 0.875rem;">ID</th>
                        <th style="padding: 12px 20px; text-align: left; font-weight: 600; color: #475569; font-size: 0.875rem;">Name</th>
                        <th style="padding: 12px 20px; text-align: left; font-weight: 600; color: #475569; font-size: 0.875rem;">Phone</th>
                        <th style="padding: 12px 20px; text-align: left; font-weight: 600; color: #475569; font-size: 0.875rem;">Status</th>
                        <th style="padding: 12px 20px; text-align: left; font-weight: 600; color: #475569; font-size: 0.875rem;">List</th>
                        <th style="padding: 12px 20px; text-align: left; font-weight: 600; color: #475569; font-size: 0.875rem;">Created</th>
                        <th style="padding: 12px 20px; text-align: left; font-weight: 600; color: #475569; font-size: 0.875rem;">Actions</th>
                    </tr>
                </thead>
                <tbody id="leadsTableBody">
                    <tr>
                        <td colspan="7" style="padding: 40px; text-align: center; color: #94a3b8;">
                            Loading leads...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Store current filter
let currentPeriod = 'today';
let stats = {
    today: { newLeads: 947, sentToVici: 0, stuckInQueue: 222, conversionRate: 0 },
    yesterday: { newLeads: 1203, sentToVici: 981, stuckInQueue: 222, conversionRate: 2.4 },
    last7: { newLeads: 8432, sentToVici: 7854, stuckInQueue: 578, conversionRate: 2.1 },
    last30: { newLeads: 31245, sentToVici: 29876, stuckInQueue: 1369, conversionRate: 2.3 },
    custom: { newLeads: 0, sentToVici: 0, stuckInQueue: 0, conversionRate: 0 }
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Add click handlers to date filter buttons
    document.querySelectorAll('.date-filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            handleDateFilterClick(this);
        });
    });
    
    // Load initial data
    loadLeadsData('today');
});

function handleDateFilterClick(button) {
    // Remove active class from all buttons
    document.querySelectorAll('.date-filter-btn').forEach(btn => {
        btn.classList.remove('active');
        btn.style.background = 'white';
        btn.style.color = '#64748b';
        btn.style.border = '1px solid #e2e8f0';
    });
    
    // Add active class to clicked button
    button.classList.add('active');
    button.style.background = '#3b82f6';
    button.style.color = 'white';
    button.style.border = 'none';
    
    // Get the period
    const period = button.getAttribute('data-period');
    currentPeriod = period;
    
    // Show/hide custom date range
    const customRange = document.getElementById('customDateRange');
    if (period === 'custom') {
        customRange.style.display = 'block';
        // Set default dates
        const today = new Date();
        const lastWeek = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
        document.getElementById('endDate').value = today.toISOString().split('T')[0];
        document.getElementById('startDate').value = lastWeek.toISOString().split('T')[0];
    } else {
        customRange.style.display = 'none';
        loadLeadsData(period);
    }
}

function loadLeadsData(period) {
    console.log('Loading data for period:', period);
    
    // Update stats cards with animation
    updateStatCard('newLeadsCount', stats[period].newLeads);
    updateStatCard('sentToViciCount', stats[period].sentToVici);
    updateStatCard('stuckInQueueCount', stats[period].stuckInQueue);
    updateStatCard('conversionRate', stats[period].conversionRate + '%');
    
    // Load table data
    loadTableData(period);
}

function updateStatCard(elementId, newValue) {
    const element = document.getElementById(elementId);
    if (element) {
        // Add fade effect
        element.style.opacity = '0.5';
        setTimeout(() => {
            element.textContent = newValue;
            element.style.opacity = '1';
        }, 200);
    }
}

function loadTableData(period) {
    const tbody = document.getElementById('leadsTableBody');
    
    // Show loading
    tbody.innerHTML = `
        <tr>
            <td colspan="7" style="padding: 40px; text-align: center; color: #94a3b8;">
                Loading leads for ${period}...
            </td>
        </tr>
    `;
    
    // Simulate API call with setTimeout
    setTimeout(() => {
        // Sample data based on period
        const sampleLeads = getSampleLeads(period);
        
        if (sampleLeads.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" style="padding: 40px; text-align: center; color: #94a3b8;">
                        No leads found for this period
                    </td>
                </tr>
            `;
        } else {
            tbody.innerHTML = sampleLeads.map(lead => `
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 12px 20px; color: #1e293b;">${lead.id}</td>
                    <td style="padding: 12px 20px; color: #1e293b;">${lead.name}</td>
                    <td style="padding: 12px 20px; color: #1e293b;">${lead.phone}</td>
                    <td style="padding: 12px 20px;">
                        <span style="padding: 4px 12px; border-radius: 20px; font-size: 0.875rem; background: ${getStatusColor(lead.status)}; color: white;">
                            ${lead.status}
                        </span>
                    </td>
                    <td style="padding: 12px 20px; color: #1e293b;">${lead.list}</td>
                    <td style="padding: 12px 20px; color: #64748b; font-size: 0.875rem;">${lead.created}</td>
                    <td style="padding: 12px 20px;">
                        <button onclick="viewLead('${lead.id}')" style="padding: 6px 12px; background: #3b82f6; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.875rem;">
                            View
                        </button>
                    </td>
                </tr>
            `).join('');
        }
    }, 500);
}

function getSampleLeads(period) {
    // Generate sample data based on period
    const now = new Date();
    let leads = [];
    
    if (period === 'today') {
        leads = [
            { id: '1735041041001', name: 'John Smith', phone: '555-0123', status: 'new', list: '101', created: '10:30 AM' },
            { id: '1735041041002', name: 'Jane Doe', phone: '555-0124', status: 'in_vici', list: '101', created: '10:15 AM' },
            { id: '1735041041003', name: 'Mike Johnson', phone: '555-0125', status: 'new', list: '0', created: '9:45 AM' },
            { id: '1735041041004', name: 'Sarah Williams', phone: '555-0126', status: 'pending', list: '0', created: '9:30 AM' },
            { id: '1735041041005', name: 'Tom Brown', phone: '555-0127', status: 'in_vici', list: '102', created: '9:00 AM' }
        ];
    } else if (period === 'yesterday') {
        leads = [
            { id: '1735041040001', name: 'Alice Cooper', phone: '555-0201', status: 'transferred', list: '104', created: 'Yesterday 4:30 PM' },
            { id: '1735041040002', name: 'Bob Martin', phone: '555-0202', status: 'in_vici', list: '103', created: 'Yesterday 3:15 PM' },
            { id: '1735041040003', name: 'Carol White', phone: '555-0203', status: 'new', list: '101', created: 'Yesterday 2:00 PM' }
        ];
    } else if (period === 'last7') {
        leads = [
            { id: '1735041039001', name: 'David Lee', phone: '555-0301', status: 'in_vici', list: '105', created: '3 days ago' },
            { id: '1735041039002', name: 'Emma Wilson', phone: '555-0302', status: 'transferred', list: '107', created: '4 days ago' },
            { id: '1735041039003', name: 'Frank Garcia', phone: '555-0303', status: 'new', list: '101', created: '5 days ago' }
        ];
    }
    
    return leads;
}

function getStatusColor(status) {
    const colors = {
        'new': '#3b82f6',
        'in_vici': '#10b981',
        'pending': '#f59e0b',
        'transferred': '#8b5cf6',
        'failed': '#ef4444'
    };
    return colors[status] || '#64748b';
}

function applyCustomRange() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    if (!startDate || !endDate) {
        alert('Please select both start and end dates');
        return;
    }
    
    console.log('Loading custom range:', startDate, 'to', endDate);
    
    // Update stats for custom range
    stats.custom = {
        newLeads: Math.floor(Math.random() * 10000),
        sentToVici: Math.floor(Math.random() * 9000),
        stuckInQueue: Math.floor(Math.random() * 1000),
        conversionRate: (Math.random() * 5).toFixed(1)
    };
    
    loadLeadsData('custom');
}

function viewLead(leadId) {
    console.log('Viewing lead:', leadId);
    // In a real app, this would navigate to the lead detail page
    alert('View lead: ' + leadId);
}

// Make the page responsive
window.addEventListener('resize', function() {
    // Adjust layout for mobile if needed
    if (window.innerWidth < 768) {
        // Mobile adjustments
    }
});
</script>

<style>
.date-filter-btn:hover:not(.active) {
    background: #f1f5f9 !important;
    border-color: #cbd5e1 !important;
}

.date-filter-btn.active {
    box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
}

#newLeadsCount, #sentToViciCount, #stuckInQueueCount, #conversionRate {
    transition: opacity 0.3s ease;
}

@media (max-width: 768px) {
    .date-filter-btn {
        flex: 1;
        min-width: 100px;
    }
}
</style>
@endsection

