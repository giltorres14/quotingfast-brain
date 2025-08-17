@extends('layouts.app')

@section('content')
<div class="container-fluid" style="max-width: 1600px; margin: 0 auto; padding: 20px;">
    <h1 style="text-align: center; margin-bottom: 30px;">📊 Vici Lead Flow Configuration</h1>
    
    <!-- Summary Stats -->
    <div style="display: flex; gap: 20px; justify-content: center; margin-bottom: 30px;">
        <div style="background: white; padding: 15px 30px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <strong>Total Call Range:</strong> <span id="totalCallRange">0-50</span>
        </div>
        <div style="background: white; padding: 15px 30px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <strong>Campaign Duration:</strong> <span id="campaignDuration">30 days</span>
        </div>
    </div>

    <!-- Compact List Table -->
    <div style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: linear-gradient(135deg, #667eea, #764ba2); color: white;">
                    <th style="padding: 12px; text-align: left;">List</th>
                    <th style="padding: 12px; text-align: center;">Name</th>
                    <th style="padding: 12px; text-align: center;">Days</th>
                    <th style="padding: 12px; text-align: center;">Resets/Day</th>
                    <th style="padding: 12px; text-align: center;">Total Calls</th>
                    <th style="padding: 12px; text-align: center;">Call Range</th>
                    <th style="padding: 12px; text-align: left;">Reset Times</th>
                    <th style="padding: 12px; text-align: left;">Agent Alert</th>
                    <th style="padding: 12px; text-align: left;">Description</th>
                </tr>
            </thead>
            <tbody>
                <!-- List 101 -->
                <tr style="background: #f0fdf4;">
                    <td style="padding: 10px; font-weight: bold;">101</td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="name" data-list="101">Brand New</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="days" data-list="101">0</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="calls_per_day" data-list="101">0</span>
                    </td>
                    <td style="padding: 10px; text-align: center;" id="total-101">0</td>
                    <td style="padding: 10px; text-align: center;" id="range-101">0</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="reset_times" data-list="101">None</span>
                    </td>
                    <td style="padding: 10px;">-</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="description" data-list="101">Fresh leads, never called</span>
                    </td>
                </tr>

                <!-- List 102 -->
                <tr style="background: #fef3c7;">
                    <td style="padding: 10px; font-weight: bold;">102</td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="name" data-list="102">First Contact</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="days" data-list="102">1</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="calls_per_day" data-list="102">1</span>
                    </td>
                    <td style="padding: 10px; text-align: center;" id="total-102">1</td>
                    <td style="padding: 10px; text-align: center;" id="range-102">1</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="reset_times" data-list="102">9:00 AM</span>
                    </td>
                    <td style="padding: 10px;">-</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="description" data-list="102">First call attempt</span>
                    </td>
                </tr>

                <!-- List 103 - VM -->
                <tr style="background: #fee2e2;">
                    <td style="padding: 10px; font-weight: bold;">103</td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="name" data-list="103">VM Follow-up</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="days" data-list="103">1</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="calls_per_day" data-list="103">1</span>
                    </td>
                    <td style="padding: 10px; text-align: center;" id="total-103">1</td>
                    <td style="padding: 10px; text-align: center;" id="range-103">2</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="reset_times" data-list="103">2:00 PM</span>
                    </td>
                    <td style="padding: 10px; color: #dc2626; font-weight: bold;">
                        <span class="editable" data-field="agent_alert" data-list="103">🔔 LEAVE VOICEMAIL</span>
                    </td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="description" data-list="103">First VM - Set LVM status</span>
                    </td>
                </tr>

                <!-- List 104 -->
                <tr style="background: #dbeafe;">
                    <td style="padding: 10px; font-weight: bold;">104</td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="name" data-list="104">Intensive</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="days" data-list="104">3</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="calls_per_day" data-list="104">4</span>
                    </td>
                    <td style="padding: 10px; text-align: center;" id="total-104">12</td>
                    <td style="padding: 10px; text-align: center;" id="range-104">3-14</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="reset_times" data-list="104">9AM, 11:30AM, 2PM, 4:30PM</span>
                    </td>
                    <td style="padding: 10px;">-</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="description" data-list="104">Aggressive calling phase</span>
                    </td>
                </tr>

                <!-- List 105 - VM -->
                <tr style="background: #fee2e2;">
                    <td style="padding: 10px; font-weight: bold;">105</td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="name" data-list="105">VM Follow-up 2</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="days" data-list="105">1</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="calls_per_day" data-list="105">1</span>
                    </td>
                    <td style="padding: 10px; text-align: center;" id="total-105">1</td>
                    <td style="padding: 10px; text-align: center;" id="range-105">15</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="reset_times" data-list="105">10:00 AM</span>
                    </td>
                    <td style="padding: 10px; color: #dc2626; font-weight: bold;">
                        <span class="editable" data-field="agent_alert" data-list="105">🔔 LEAVE VM #2</span>
                    </td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="description" data-list="105">Second VM - Set LVM status</span>
                    </td>
                </tr>

                <!-- List 106 -->
                <tr style="background: #f3e8ff;">
                    <td style="padding: 10px; font-weight: bold;">106</td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="name" data-list="106">Standard Follow-up</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="days" data-list="106">5</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="calls_per_day" data-list="106">2</span>
                    </td>
                    <td style="padding: 10px; text-align: center;" id="total-106">10</td>
                    <td style="padding: 10px; text-align: center;" id="range-106">16-25</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="reset_times" data-list="106">10:00 AM, 3:00 PM</span>
                    </td>
                    <td style="padding: 10px;">-</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="description" data-list="106">Regular follow-up</span>
                    </td>
                </tr>

                <!-- List 107 -->
                <tr style="background: #ecfdf5;">
                    <td style="padding: 10px; font-weight: bold;">107</td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="name" data-list="107">Reduced</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="days" data-list="107">7</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="calls_per_day" data-list="107">1</span>
                    </td>
                    <td style="padding: 10px; text-align: center;" id="total-107">7</td>
                    <td style="padding: 10px; text-align: center;" id="range-107">26-32</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="reset_times" data-list="107">11:00 AM</span>
                    </td>
                    <td style="padding: 10px;">-</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="description" data-list="107">Daily single attempt</span>
                    </td>
                </tr>

                <!-- List 108 -->
                <tr style="background: #fef3c7;">
                    <td style="padding: 10px; font-weight: bold;">108</td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="name" data-list="108">Final Attempts</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="days" data-list="108">14</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="calls_per_day" data-list="108">0.5</span>
                    </td>
                    <td style="padding: 10px; text-align: center;" id="total-108">7</td>
                    <td style="padding: 10px; text-align: center;" id="range-108">33-39</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="reset_times" data-list="108">Mon/Wed/Fri 10AM</span>
                    </td>
                    <td style="padding: 10px;">-</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="description" data-list="108">Every other day</span>
                    </td>
                </tr>

                <!-- List 110 -->
                <tr style="background: #f1f5f9;">
                    <td style="padding: 10px; font-weight: bold;">110</td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="name" data-list="110">Archive</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">∞</td>
                    <td style="padding: 10px; text-align: center;">0</td>
                    <td style="padding: 10px; text-align: center;" id="total-110">-</td>
                    <td style="padding: 10px; text-align: center;" id="range-110">40+</td>
                    <td style="padding: 10px;">None</td>
                    <td style="padding: 10px;">-</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="description" data-list="110">End of campaign</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Action Buttons -->
    <div style="display: flex; gap: 15px; justify-content: center; margin-top: 30px;">
        <button id="lockButton" onclick="toggleGlobalEdit()" style="padding: 12px 30px; font-size: 1.1rem; background: #dc2626; color: white; border: none; border-radius: 8px; cursor: pointer;">
            🔒 Lock Configuration
        </button>
        <button onclick="saveAllChanges()" style="padding: 12px 30px; font-size: 1.1rem; background: #3b82f6; color: white; border: none; border-radius: 8px; cursor: pointer;">
            💾 Save All Changes
        </button>
        <button onclick="recalculateRanges()" style="padding: 12px 30px; font-size: 1.1rem; background: #10b981; color: white; border: none; border-radius: 8px; cursor: pointer;">
            🔄 Recalculate
        </button>
    </div>
</div>

<style>
.editable {
    padding: 4px 8px;
    border-radius: 4px;
    transition: all 0.2s;
}

.editable[contenteditable="true"] {
    background: #fef3c7;
    border: 2px solid #f59e0b;
    cursor: text;
}

.editable[contenteditable="false"] {
    cursor: default;
}

table {
    font-size: 14px;
}

th {
    position: sticky;
    top: 0;
    z-index: 10;
}

tr:hover {
    filter: brightness(0.95);
}
</style>

<script>
let globalEditMode = true;

// Initialize on page load
window.addEventListener('DOMContentLoaded', function() {
    // Make all fields editable by default
    document.querySelectorAll('.editable').forEach(el => {
        el.contentEditable = true;
        el.addEventListener('input', recalculateRanges);
    });
    
    recalculateRanges();
});

// Toggle global edit mode
function toggleGlobalEdit() {
    globalEditMode = !globalEditMode;
    const lockButton = document.getElementById('lockButton');
    
    document.querySelectorAll('.editable').forEach(el => {
        el.contentEditable = globalEditMode;
    });
    
    if (globalEditMode) {
        lockButton.textContent = '🔒 Lock Configuration';
        lockButton.style.background = '#dc2626';
    } else {
        lockButton.textContent = '🔓 Unlock for Editing';
        lockButton.style.background = '#10b981';
        saveAllChanges();
    }
}

// Recalculate ranges
function recalculateRanges() {
    let cumulativeCalls = 0;
    let totalDays = 0;
    const lists = [101, 102, 103, 104, 105, 106, 107, 108];
    
    lists.forEach(listId => {
        const daysEl = document.querySelector(`[data-field="days"][data-list="${listId}"]`);
        const resetsEl = document.querySelector(`[data-field="calls_per_day"][data-list="${listId}"]`);
        
        const days = parseFloat(daysEl?.textContent) || 0;
        const resets = parseFloat(resetsEl?.textContent) || 0;
        const totalCalls = days * resets;
        
        totalDays += days;
        
        // Update total calls
        const totalEl = document.getElementById(`total-${listId}`);
        if (totalEl) {
            totalEl.textContent = totalCalls > 0 ? totalCalls.toFixed(1).replace('.0', '') : '0';
        }
        
        // Update range
        const rangeEl = document.getElementById(`range-${listId}`);
        if (rangeEl) {
            if (listId === 101) {
                rangeEl.textContent = '0';
            } else if (totalCalls === 0) {
                rangeEl.textContent = '-';
            } else {
                const start = cumulativeCalls + 1;
                const end = cumulativeCalls + totalCalls;
                rangeEl.textContent = totalCalls === 1 ? start.toString() : `${start}-${Math.floor(end)}`;
                cumulativeCalls += totalCalls;
            }
        }
    });
    
    // Update archive range
    const archiveRange = document.getElementById('range-110');
    if (archiveRange) {
        archiveRange.textContent = `${Math.floor(cumulativeCalls) + 1}+`;
    }
    
    // Update summary
    document.getElementById('totalCallRange').textContent = `0-${Math.floor(cumulativeCalls)}`;
    document.getElementById('campaignDuration').textContent = `${totalDays} days`;
}

// Save configuration
async function saveAllChanges() {
    const flowData = {};
    const lists = [101, 102, 103, 104, 105, 106, 107, 108, 110];
    
    lists.forEach(listId => {
        const data = {};
        document.querySelectorAll(`[data-list="${listId}"]`).forEach(el => {
            const field = el.dataset.field;
            if (field) {
                data[field] = el.textContent.trim();
            }
        });
        flowData[listId] = data;
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
            alert('✅ Configuration saved successfully!');
        }
    } catch (error) {
        console.error('Error saving:', error);
        alert('❌ Error saving configuration');
    }
}
</script>
@endsection
