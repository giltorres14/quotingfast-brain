@extends('layouts.app')

@section('title', 'Lead Flow Management')

@section('content')
<style>
    .flow-container {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .flow-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 30px;
    }
    
    .flow-chart {
        display: grid;
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .list-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-left: 5px solid #4a90e2;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .list-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
    }
    
    .list-card.active {
        border-left-color: #10b981;
        background: linear-gradient(to right, #f0fdf4, white);
    }
    
    .list-card.archive {
        border-left-color: #6b7280;
        background: linear-gradient(to right, #f9fafb, white);
    }
    
    .list-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .list-number {
        background: #4a90e2;
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: bold;
        font-size: 1.2rem;
    }
    
    .list-name {
        font-size: 1.3rem;
        font-weight: 600;
        color: #1f2937;
    }
    
    .list-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin: 15px 0;
    }
    
    .stat-item {
        background: #f3f4f6;
        padding: 10px;
        border-radius: 8px;
    }
    
    .stat-label {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 5px;
    }
    
    .stat-value {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
    }
    
    .editable {
        background: #fef3c7;
        border: 1px dashed #f59e0b;
        padding: 2px 6px;
        border-radius: 4px;
        cursor: pointer;
        transition: background 0.2s;
    }
    
    .editable:hover {
        background: #fed7aa;
    }
    
    .editable:focus {
        outline: none;
        background: white;
        border-color: #3b82f6;
    }
    
    .flow-arrow {
        text-align: center;
        color: #6b7280;
        font-size: 2rem;
        margin: 10px 0;
    }
    
    .action-buttons {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }
    
    .btn-edit {
        background: #3b82f6;
        color: white;
        padding: 8px 16px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        font-weight: 500;
    }
    
    .btn-save {
        background: #10b981;
        color: white;
        padding: 8px 16px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        font-weight: 500;
    }
    
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .summary-card {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .progress-bar {
        background: #e5e7eb;
        height: 8px;
        border-radius: 4px;
        overflow: hidden;
        margin-top: 10px;
    }
    
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #3b82f6, #10b981);
        transition: width 0.3s;
    }
</style>

<div class="flow-container">
    <div class="flow-header">
        <h1 style="font-size: 2rem; margin: 0;">📊 Lead Flow Management</h1>
        <p style="margin: 10px 0 0 0; opacity: 0.9;">Configure and monitor your lead progression through campaign lists</p>
    </div>

    <!-- Summary Cards -->
    <div class="summary-grid">
        <div class="summary-card">
            <div class="stat-label">Total Leads in Campaign</div>
            <div class="stat-value" id="totalLeads">Loading...</div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: 100%;"></div>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="stat-label">Active Lists</div>
            <div class="stat-value">9</div>
            <div style="margin-top: 10px; color: #6b7280;">Lists 101-108, 110</div>
        </div>
        
        <div class="summary-card">
            <div class="stat-label">Average Calls per Lead</div>
            <div class="stat-value" id="avgCalls">0.0</div>
            <div style="margin-top: 10px; color: #6b7280;">Across all lists</div>
        </div>
        
        <div class="summary-card">
            <div class="stat-label">Daily Call Volume</div>
            <div class="stat-value" id="dailyVolume">0</div>
            <div style="margin-top: 10px; color: #6b7280;">Expected today</div>
        </div>
    </div>

    <!-- Lead Flow Chart -->
    <div class="flow-chart" id="flowChart">
        <!-- List 101 -->
        <div class="list-card active" data-list="101">
            <div class="list-header">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div class="list-number">101</div>
                    <div class="list-name">Brand New</div>
                </div>
                <button class="btn-edit" onclick="toggleEdit(101)">✏️ Edit</button>
            </div>
            
            <div class="list-stats">
                <div class="stat-item">
                    <div class="stat-label">Days in List</div>
                    <div class="stat-value editable" contenteditable="false" data-field="days" data-list="101">0</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">List Resets/Day</div>
                    <div class="stat-value editable" contenteditable="false" data-field="calls_per_day" data-list="101">0</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Total Calls</div>
                    <div class="stat-value" id="total-calls-101">0</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Call Range</div>
                    <div class="stat-value" id="call-range-101">0</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Current Leads</div>
                    <div class="stat-value" id="lead-count-101">Loading...</div>
                </div>
            </div>
            
            <div style="margin-top: 15px; padding: 10px; background: #e5f3ff; border-radius: 8px;">
                <strong>Reset Times:</strong> 
                <span class="editable" contenteditable="false" data-field="reset_times" data-list="101" style="font-family: monospace;">None</span>
            </div>
            
            <div style="margin-top: 15px; padding: 10px; background: #f9fafb; border-radius: 8px;">
                <strong>Description:</strong> 
                <span class="editable" contenteditable="false" data-field="description" data-list="101">Fresh leads that haven't been called yet</span>
            </div>
        </div>
        
        <div class="flow-arrow">⬇️</div>
        
        <!-- List 102 -->
        <div class="list-card" data-list="102">
            <div class="list-header">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div class="list-number">102</div>
                    <div class="list-name">First Contact</div>
                </div>
                <button class="btn-edit" onclick="toggleEdit(102)">✏️ Edit</button>
            </div>
            
            <div class="list-stats">
                <div class="stat-item">
                    <div class="stat-label">Days in List</div>
                    <div class="stat-value editable" contenteditable="false" data-field="days" data-list="102">1</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">List Resets/Day</div>
                    <div class="stat-value editable" contenteditable="false" data-field="calls_per_day" data-list="102">1</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Total Calls</div>
                    <div class="stat-value" id="total-calls-102">1</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Call Range</div>
                    <div class="stat-value" id="call-range-102">1</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Current Leads</div>
                    <div class="stat-value" id="lead-count-102">Loading...</div>
                </div>
            </div>
            
            
            <div style="margin-top: 15px; padding: 10px; background: #e5f3ff; border-radius: 8px;">
                <strong>Reset Times:</strong> 
                <span class="editable" contenteditable="false" data-field="reset_times" data-list="102" style="font-family: monospace;">9:00 AM</span>
            </div>
            
            <div style="margin-top: 15px; padding: 10px; background: #f9fafb; border-radius: 8px;">
                <strong>Description:</strong> 
                <span class="editable" contenteditable="false" data-field="description" data-list="102">First call attempt to establish contact</span>
            </div>
        </div>
        
        <div class="flow-arrow">⬇️</div>
        
        <!-- List 103 -->
        <div class="list-card" data-list="103">
            <div class="list-header">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div class="list-number">103</div>
                    <div class="list-name">VM Follow-up</div>
                </div>
                <button class="btn-edit" onclick="toggleEdit(103)">✏️ Edit</button>
            </div>
            
            <div class="list-stats">
                <div class="stat-item">
                    <div class="stat-label">Days in List</div>
                    <div class="stat-value editable" contenteditable="false" data-field="days" data-list="103">1</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">List Resets/Day</div>
                    <div class="stat-value editable" contenteditable="false" data-field="calls_per_day" data-list="103">1</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Total Calls</div>
                    <div class="stat-value" id="total-calls-103">1</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Call Range</div>
                    <div class="stat-value" id="call-range-103">2</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Current Leads</div>
                    <div class="stat-value" id="lead-count-103">Loading...</div>
                </div>
            </div>
            
            
            <div style="margin-top: 15px; padding: 10px; background: #e5f3ff; border-radius: 8px;">
                <strong>Reset Times:</strong> 
                <span class="editable" contenteditable="false" data-field="reset_times" data-list="103" style="font-family: monospace;">2:00 PM</span>
            </div>
            
            <div style="margin-top: 15px; padding: 10px; background: #f9fafb; border-radius: 8px;">
                <strong>Description:</strong> 
                <span class="editable" contenteditable="false" data-field="description" data-list="103">Leave voicemail message</span>
            </div>
        </div>
        
        <div class="flow-arrow">⬇️</div>
        
        <!-- List 104 -->
        <div class="list-card" data-list="104">
            <div class="list-header">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div class="list-number">104</div>
                    <div class="list-name">Intensive</div>
                </div>
                <button class="btn-edit" onclick="toggleEdit(104)">✏️ Edit</button>
            </div>
            
            <div class="list-stats">
                <div class="stat-item">
                    <div class="stat-label">Days in List</div>
                    <div class="stat-value editable" contenteditable="false" data-field="days" data-list="104">3</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">List Resets/Day</div>
                    <div class="stat-value editable" contenteditable="false" data-field="calls_per_day" data-list="104">4</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Total Calls</div>
                    <div class="stat-value" id="total-calls-104">12</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Call Range</div>
                    <div class="stat-value" id="call-range-104">3-14</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Current Leads</div>
                    <div class="stat-value" id="lead-count-104">Loading...</div>
                </div>
            </div>
            
            
            <div style="margin-top: 15px; padding: 10px; background: #e5f3ff; border-radius: 8px;">
                <strong>Reset Times:</strong> 
                <span class="editable" contenteditable="false" data-field="reset_times" data-list="104" style="font-family: monospace;">9:00 AM, 11:30 AM, 2:00 PM, 4:30 PM</span>
            </div>
            
            <div style="margin-top: 15px; padding: 10px; background: #f9fafb; border-radius: 8px;">
                <strong>Description:</strong> 
                <span class="editable" contenteditable="false" data-field="description" data-list="104">Aggressive calling phase - 4x per day for 3 days</span>
            </div>
        </div>
        
        <div class="flow-arrow">⬇️</div>
        
        <!-- List 105 -->
        <div class="list-card" data-list="105">
            <div class="list-header">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div class="list-number">105</div>
                    <div class="list-name">Standard Follow-up</div>
                </div>
                <button class="btn-edit" onclick="toggleEdit(105)">✏️ Edit</button>
            </div>
            
            <div class="list-stats">
                <div class="stat-item">
                    <div class="stat-label">Days in List</div>
                    <div class="stat-value editable" contenteditable="false" data-field="days" data-list="105">5</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">List Resets/Day</div>
                    <div class="stat-value editable" contenteditable="false" data-field="calls_per_day" data-list="105">2</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Total Calls</div>
                    <div class="stat-value" id="total-calls-105">10</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Call Range</div>
                    <div class="stat-value" id="call-range-105">15-24</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Current Leads</div>
                    <div class="stat-value" id="lead-count-105">Loading...</div>
                </div>
            </div>
            
            
            <div style="margin-top: 15px; padding: 10px; background: #e5f3ff; border-radius: 8px;">
                <strong>Reset Times:</strong> 
                <span class="editable" contenteditable="false" data-field="reset_times" data-list="105" style="font-family: monospace;">10:00 AM, 3:00 PM</span>
            </div>
            
            <div style="margin-top: 15px; padding: 10px; background: #f9fafb; border-radius: 8px;">
                <strong>Description:</strong> 
                <span class="editable" contenteditable="false" data-field="description" data-list="105">Regular follow-up attempts</span>
            </div>
        </div>
        
        <div class="flow-arrow">⬇️</div>
        
        <!-- List 106 -->
        <div class="list-card" data-list="106">
            <div class="list-header">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div class="list-number">106</div>
                    <div class="list-name">Reduced Follow-up</div>
                </div>
                <button class="btn-edit" onclick="toggleEdit(106)">✏️ Edit</button>
            </div>
            
            <div class="list-stats">
                <div class="stat-item">
                    <div class="stat-label">Days in List</div>
                    <div class="stat-value editable" contenteditable="false" data-field="days" data-list="106">7</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">List Resets/Day</div>
                    <div class="stat-value editable" contenteditable="false" data-field="calls_per_day" data-list="106">1</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Total Calls</div>
                    <div class="stat-value" id="total-calls-106">7</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Call Range</div>
                    <div class="stat-value" id="call-range-106">25-31</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Current Leads</div>
                    <div class="stat-value" id="lead-count-106">Loading...</div>
                </div>
            </div>
            
            
            <div style="margin-top: 15px; padding: 10px; background: #e5f3ff; border-radius: 8px;">
                <strong>Reset Times:</strong> 
                <span class="editable" contenteditable="false" data-field="reset_times" data-list="106" style="font-family: monospace;">11:00 AM</span>
            </div>
            
            <div style="margin-top: 15px; padding: 10px; background: #f9fafb; border-radius: 8px;">
                <strong>Description:</strong> 
                <span class="editable" contenteditable="false" data-field="description" data-list="106">Less frequent contact attempts</span>
            </div>
        </div>
        
        <div class="flow-arrow">⬇️</div>
        
        <!-- List 107 -->
        <div class="list-card" data-list="107">
            <div class="list-header">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div class="list-number">107</div>
                    <div class="list-name">Weekly Touch</div>
                </div>
                <button class="btn-edit" onclick="toggleEdit(107)">✏️ Edit</button>
            </div>
            
            <div class="list-stats">
                <div class="stat-item">
                    <div class="stat-label">Days in List</div>
                    <div class="stat-value editable" contenteditable="false" data-field="days" data-list="107">14</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">List Resets/Day</div>
                    <div class="stat-value editable" contenteditable="false" data-field="calls_per_day" data-list="107">0.5</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Total Calls</div>
                    <div class="stat-value" id="total-calls-107">7</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Call Range</div>
                    <div class="stat-value" id="call-range-107">32-38</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Current Leads</div>
                    <div class="stat-value" id="lead-count-107">Loading...</div>
                </div>
            </div>
            
            
            <div style="margin-top: 15px; padding: 10px; background: #e5f3ff; border-radius: 8px;">
                <strong>Reset Times:</strong> 
                <span class="editable" contenteditable="false" data-field="reset_times" data-list="107" style="font-family: monospace;">Mon/Wed/Fri 10:00 AM</span>
            </div>
            
            <div style="margin-top: 15px; padding: 10px; background: #f9fafb; border-radius: 8px;">
                <strong>Description:</strong> 
                <span class="editable" contenteditable="false" data-field="description" data-list="107">Every other day contact</span>
            </div>
        </div>
        
        <div class="flow-arrow">⬇️</div>
        
        <!-- List 108 -->
        <div class="list-card" data-list="108">
            <div class="list-header">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div class="list-number">108</div>
                    <div class="list-name">Final Attempts</div>
                </div>
                <button class="btn-edit" onclick="toggleEdit(108)">✏️ Edit</button>
            </div>
            
            <div class="list-stats">
                <div class="stat-item">
                    <div class="stat-label">Days in List</div>
                    <div class="stat-value editable" contenteditable="false" data-field="days" data-list="108">14</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">List Resets/Day</div>
                    <div class="stat-value editable" contenteditable="false" data-field="calls_per_day" data-list="108">0.25</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Total Calls</div>
                    <div class="stat-value" id="total-calls-108">3.5</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Call Range</div>
                    <div class="stat-value" id="call-range-108">39-42</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Current Leads</div>
                    <div class="stat-value" id="lead-count-108">Loading...</div>
                </div>
            </div>
            
            
            <div style="margin-top: 15px; padding: 10px; background: #e5f3ff; border-radius: 8px;">
                <strong>Reset Times:</strong> 
                <span class="editable" contenteditable="false" data-field="reset_times" data-list="108" style="font-family: monospace;">Tue/Thu 2:00 PM</span>
            </div>
            
            <div style="margin-top: 15px; padding: 10px; background: #f9fafb; border-radius: 8px;">
                <strong>Description:</strong> 
                <span class="editable" contenteditable="false" data-field="description" data-list="108">Final contact attempts - twice per week</span>
            </div>
        </div>
        
        <div class="flow-arrow">⬇️</div>
        
        <!-- List 110 -->
        <div class="list-card archive" data-list="110">
            <div class="list-header">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div class="list-number" style="background: #6b7280;">110</div>
                    <div class="list-name">Archive</div>
                </div>
                <button class="btn-edit" onclick="toggleEdit(110)">✏️ Edit</button>
            </div>
            
            <div class="list-stats">
                <div class="stat-item">
                    <div class="stat-label">Days in List</div>
                    <div class="stat-value">∞</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Calls/Day</div>
                    <div class="stat-value">0</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Total Calls</div>
                    <div class="stat-value">-</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Call Range</div>
                    <div class="stat-value" id="call-range-110">43+</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Current Leads</div>
                    <div class="stat-value" id="lead-count-110">Loading...</div>
                </div>
            </div>
            
            <div style="margin-top: 15px; padding: 10px; background: #f9fafb; border-radius: 8px;">
                <strong>Description:</strong> 
                <span class="editable" contenteditable="false" data-field="description" data-list="110">Maximum attempts reached - no more calls</span>
            </div>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div style="display: flex; gap: 15px; justify-content: center; margin-top: 30px;">
        <button id="lockButton" class="btn-save" onclick="toggleGlobalEdit()" style="padding: 12px 30px; font-size: 1.1rem; background: #dc2626;">
            🔒 Lock Configuration
        </button>
        <button class="btn-save" onclick="saveAllChanges()" style="padding: 12px 30px; font-size: 1.1rem;">
            💾 Save All Changes
        </button>
        <button class="btn-edit" onclick="recalculateRanges()" style="padding: 12px 30px; font-size: 1.1rem;">
            🔄 Recalculate Ranges
        </button>
        <button class="btn-edit" onclick="loadLeadCounts()" style="padding: 12px 30px; font-size: 1.1rem; background: #8b5cf6;">
            📊 Refresh Lead Counts
        </button>
    </div>
</div>

<script>
let editMode = {};
let listData = {};
let globalEditMode = true; // Start in edit mode

// Toggle global edit mode (lock/unlock all)
function toggleGlobalEdit() {
    globalEditMode = !globalEditMode;
    const lockButton = document.getElementById('lockButton');
    const lists = [101, 102, 103, 104, 105, 106, 107, 108, 110];
    
    if (globalEditMode) {
        // Unlock all for editing
        lockButton.textContent = '🔒 Lock Configuration';
        lockButton.style.background = '#dc2626';
        
        lists.forEach(listId => {
            editMode[listId] = true;
            const editables = document.querySelectorAll(`[data-list="${listId}"][contenteditable]`);
            editables.forEach(el => {
                el.contentEditable = true;
                el.style.background = '#fef3c7';
                el.style.border = '2px solid #f59e0b';
                el.style.padding = '4px 8px';
                el.style.borderRadius = '4px';
                el.style.cursor = 'text';
            });
            
            const btn = document.querySelector(`[onclick="toggleEdit(${listId})"]`);
            if (btn) {
                btn.textContent = '✅ Done';
                btn.style.background = '#10b981';
                btn.style.display = 'inline-block';
            }
        });
    } else {
        // Lock all - read-only mode
        lockButton.textContent = '🔓 Unlock for Editing';
        lockButton.style.background = '#10b981';
        
        lists.forEach(listId => {
            editMode[listId] = false;
            const editables = document.querySelectorAll(`[data-list="${listId}"][contenteditable]`);
            editables.forEach(el => {
                el.contentEditable = false;
                el.style.background = 'transparent';
                el.style.border = 'none';
                el.style.padding = '0';
                el.style.borderRadius = '0';
                el.style.cursor = 'default';
            });
            
            const btn = document.querySelector(`[onclick="toggleEdit(${listId})"]`);
            if (btn) {
                btn.style.display = 'none'; // Hide individual edit buttons when locked
            }
        });
        
        // Save configuration when locking
        saveAllChanges();
    }
}

// Initialize data
function initializeData() {
    // Load saved data or use defaults
    const lists = [101, 102, 103, 104, 105, 106, 107, 108, 110];
    
    // Start in edit mode for all lists
    lists.forEach(listId => {
        editMode[listId] = true;
        
        const days = document.querySelector(`[data-field="days"][data-list="${listId}"]`)?.textContent || '0';
        const callsPerDay = document.querySelector(`[data-field="calls_per_day"][data-list="${listId}"]`)?.textContent || '0';
        const description = document.querySelector(`[data-field="description"][data-list="${listId}"]`)?.textContent || '';
        const resetTimes = document.querySelector(`[data-field="reset_times"][data-list="${listId}"]`)?.textContent || '';
        
        listData[listId] = {
            days: parseFloat(days),
            calls_per_day: parseFloat(callsPerDay),
            description: description,
            reset_times: resetTimes
        };
        
        // Make all fields editable
        const editables = document.querySelectorAll(`[data-list="${listId}"][contenteditable]`);
        editables.forEach(el => {
            el.contentEditable = true;
            el.style.background = '#fef3c7';
            el.style.border = '2px solid #f59e0b';
            el.style.padding = '4px 8px';
            el.style.borderRadius = '4px';
            el.style.cursor = 'text';
            
            // Add input listener for real-time recalculation
            el.addEventListener('input', function() {
                const field = el.dataset.field;
                const value = el.textContent;
                if (field === 'days' || field === 'calls_per_day') {
                    listData[listId][field] = parseFloat(value) || 0;
                    recalculateRanges();
                } else {
                    listData[listId][field] = value;
                }
            });
        });
        
        // Update button to show "Done"
        const btn = document.querySelector(`[onclick="toggleEdit(${listId})"]`);
        if (btn) {
            btn.textContent = '✅ Done';
            btn.style.background = '#10b981';
        }
    });
    
    recalculateRanges();
    loadLeadCounts();
}

// Toggle edit mode for a list
function toggleEdit(listId) {
    editMode[listId] = !editMode[listId];
    
    const editables = document.querySelectorAll(`[data-list="${listId}"][contenteditable]`);
    editables.forEach(el => {
        el.contentEditable = editMode[listId];
        if (editMode[listId]) {
            el.style.background = '#fef3c7';
            el.style.border = '2px solid #f59e0b';
        } else {
            el.style.background = '';
            el.style.border = '1px dashed #f59e0b';
            
            // Update data
            const field = el.dataset.field;
            const value = el.textContent;
            if (field === 'days' || field === 'calls_per_day') {
                listData[listId][field] = parseFloat(value) || 0;
            } else {
                listData[listId][field] = value;
            }
        }
    });
    
    // Update button text
    const btn = document.querySelector(`.list-card[data-list="${listId}"] .btn-edit`);
    btn.textContent = editMode[listId] ? '✅ Done' : '✏️ Edit';
    
    if (!editMode[listId]) {
        recalculateRanges();
    }
}


// Recalculate call ranges based on days and resets per day
function recalculateRanges() {
    let cumulativeCalls = 0;
    const lists = [101, 102, 103, 104, 105, 106, 107, 108];
    
    lists.forEach(listId => {
        const days = parseFloat(document.querySelector(\`[data-field="days"][data-list="${listId}"]\`)?.textContent) || 0;
        const resetsPerDay = parseFloat(document.querySelector(\`[data-field="calls_per_day"][data-list="${listId}"]\`)?.textContent) || 0;
        const totalCalls = days * resetsPerDay;
        
        // Update total calls display
        const totalCallsEl = document.getElementById(\`total-calls-${listId}\`);
        if (totalCallsEl) {
            totalCallsEl.textContent = totalCalls > 0 ? totalCalls.toFixed(1).replace('.0', '') : '0';
        }
        
        // Update call range
        const rangeEl = document.getElementById(\`call-range-${listId}\`);
        if (rangeEl) {
            if (listId === 101) {
                rangeEl.textContent = '0';
            } else {
                const minCalls = cumulativeCalls + 1;
                const maxCalls = cumulativeCalls + totalCalls;
                
                if (totalCalls === 0) {
                    rangeEl.textContent = '-';
                } else if (totalCalls === 1) {
                    rangeEl.textContent = minCalls.toString();
                } else {
                    rangeEl.textContent = \`${minCalls}-${Math.floor(maxCalls)}\`;
                }
                
                cumulativeCalls += totalCalls;
            }
        }
    });
    
    // Update archive list range
    const archiveRangeEl = document.getElementById('call-range-110');
    if (archiveRangeEl) {
        archiveRangeEl.textContent = \`${Math.floor(cumulativeCalls) + 1}+\`;
    }
    
    // Update summary
    updateSummary();
}

// Update summary with reset schedule info
function updateSummary() {
    // Calculate total resets across all lists
    let totalDailyResets = 0;
    [102, 103, 104, 105, 106, 107, 108].forEach(listId => {
        const leads = parseInt(document.getElementById(\`lead-count-${listId}\`)?.textContent.replace(/,/g, '')) || 0;
        const resetsPerDay = parseFloat(document.querySelector(\`[data-field="calls_per_day"][data-list="${listId}"]\`)?.textContent) || 0;
        totalDailyResets += leads * resetsPerDay;
    });
    
    const dailyVolumeEl = document.getElementById('dailyVolume');
    if (dailyVolumeEl) {
        dailyVolumeEl.textContent = Math.floor(totalDailyResets).toLocaleString();
    }
}+`;
    }
}

// Load current lead counts from database
async function loadLeadCounts() {
    try {
        const response = await fetch('/api/vici/lead-counts');
        const data = await response.json();
        
        let totalLeads = 0;
        let totalCalls = 0;
        let leadCount = 0;
        
        // Update individual list counts
        Object.keys(data.lists || {}).forEach(listId => {
            const el = document.getElementById(`lead-count-${listId}`);
            if (el) {
                el.textContent = data.lists[listId].toLocaleString();
                totalLeads += data.lists[listId];
            }
        });
        
        // Update summary stats
        document.getElementById('totalLeads').textContent = totalLeads.toLocaleString();
        
        if (data.avgCalls !== undefined) {
            document.getElementById('avgCalls').textContent = data.avgCalls.toFixed(1);
        }
        
        // Calculate expected daily volume
        let dailyVolume = 0;
        [102, 103, 104, 105, 106, 107, 108].forEach(listId => {
            const leads = data.lists[listId] || 0;
            const callsPerDay = parseFloat(document.querySelector(`[data-field="calls_per_day"][data-list="${listId}"]`)?.textContent) || 0;
            dailyVolume += leads * callsPerDay;
        });
        document.getElementById('dailyVolume').textContent = Math.floor(dailyVolume).toLocaleString();
        
    } catch (error) {
        console.error('Error loading lead counts:', error);
        // Use placeholder data
        document.querySelectorAll('[id^="lead-count-"]').forEach(el => {
            el.textContent = '0';
        });
    }
}

// Save all changes
async function saveAllChanges() {
    const lists = [101, 102, 103, 104, 105, 106, 107, 108, 110];
    const flowData = {};
    
    lists.forEach(listId => {
        const days = document.querySelector(`[data-field="days"][data-list="${listId}"]`)?.textContent || '0';
        const callsPerDay = document.querySelector(`[data-field="calls_per_day"][data-list="${listId}"]`)?.textContent || '0';
        const description = document.querySelector(`[data-field="description"][data-list="${listId}"]`)?.textContent || '';
        const resetTimes = document.querySelector(`[data-field="reset_times"][data-list="${listId}"]`)?.textContent || '';
        
        flowData[listId] = {
            list_id: listId,
            days: parseFloat(days),
            calls_per_day: parseFloat(callsPerDay),
            reset_times: resetTimes,
            description: description,
            total_calls: parseFloat(days) * parseFloat(callsPerDay)
        };
    });
    
    try {
        const response = await fetch('/api/vici/save-lead-flow', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({ flow_data: flowData })
        });
        
        if (response.ok) {
            alert('✅ Lead flow configuration saved successfully!');
            recalculateRanges();
        } else {
            alert('❌ Error saving configuration. Please try again.');
        }
    } catch (error) {
        console.error('Error saving:', error);
        alert('❌ Error saving configuration. Please try again.');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeData();
    
    // Auto-refresh lead counts every 30 seconds
    setInterval(loadLeadCounts, 30000);
});
</script>
@endsection
