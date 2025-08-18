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
                <!-- List 101 - Initial Contact -->
                <tr style="background: #f0fdf4;">
                    <td style="padding: 10px; font-weight: bold;">101</td>
                    <td style="padding: 10px; text-align: center;">Initial Contact</td>
                    <td style="padding: 10px; text-align: center;">0</td>
                    <td style="padding: 10px; text-align: center;">1</td>
                    <td style="padding: 10px; text-align: center;" id="total-101">1</td>
                    <td style="padding: 10px; text-align: center;" id="range-101">1</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="reset_times" data-list="101">Immediate</span>
                    </td>
                    <td style="padding: 10px;">-</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="description" data-list="101">Immediate call upon entry</span>
                    </td>
                </tr>

                <!-- List 102 - 20-Minute Follow-Up -->
                <tr style="background: #fef3c7;">
                    <td style="padding: 10px; font-weight: bold;">102</td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="name" data-list="102">20-Min Follow-Up</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="days" data-list="102">0</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="calls_per_day" data-list="102">1</span>
                    </td>
                    <td style="padding: 10px; text-align: center;" id="total-102">1</td>
                    <td style="padding: 10px; text-align: center;" id="range-102">2</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="reset_times" data-list="102">+20 minutes</span>
                    </td>
                    <td style="padding: 10px;">-</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="description" data-list="102">20 min after List 101</span>
                    </td>
                </tr>

                <!-- List 103 - Voicemail Phase -->
                <tr style="background: #fee2e2;">
                    <td style="padding: 10px; font-weight: bold;">103</td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="name" data-list="103">Voicemail Phase</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="days" data-list="103">0</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="calls_per_day" data-list="103">1</span>
                    </td>
                    <td style="padding: 10px; text-align: center;" id="total-103">1</td>
                    <td style="padding: 10px; text-align: center;" id="range-103">3</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="reset_times" data-list="103">After 102</span>
                    </td>
                    <td style="padding: 10px; color: #dc2626; font-weight: bold;">
                        <span class="editable" data-field="agent_alert" data-list="103">🔔 LEAVE VOICEMAIL</span>
                    </td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="description" data-list="103">Leave VM, set LVM status</span>
                    </td>
                </tr>

                <!-- List 104 - Hot Phase -->
                <tr style="background: #dbeafe;">
                    <td style="padding: 10px; font-weight: bold;">104</td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="name" data-list="104">Hot Phase</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="days" data-list="104">3</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="calls_per_day" data-list="104">4</span>
                    </td>
                    <td style="padding: 10px; text-align: center;" id="total-104">12</td>
                    <td style="padding: 10px; text-align: center;" id="range-104">4-15</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="reset_times" data-list="104">9AM, 11:30AM, 2PM, 4:30PM</span>
                    </td>
                    <td style="padding: 10px;">-</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="description" data-list="104">Aggressive 3 workdays</span>
                    </td>
                </tr>

                <!-- List 105 - Extended Follow-Up -->
                <tr style="background: #f3e8ff;">
                    <td style="padding: 10px; font-weight: bold;">105</td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="name" data-list="105">Extended Follow-Up</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="days" data-list="105">7</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="calls_per_day" data-list="105">3</span>
                    </td>
                    <td style="padding: 10px; text-align: center;" id="total-105">21</td>
                    <td style="padding: 10px; text-align: center;" id="range-105">16-36</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="reset_times" data-list="105">10AM, 1PM, 4PM</span>
                    </td>
                    <td style="padding: 10px;">-</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="description" data-list="105">7 workdays, 3x daily</span>
                    </td>
                </tr>

                <!-- List 106 - Secondary Follow-Up -->
                <tr style="background: #ecfdf5;">
                    <td style="padding: 10px; font-weight: bold;">106</td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="name" data-list="106">Secondary Follow-Up</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="days" data-list="106">5</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="calls_per_day" data-list="106">2</span>
                    </td>
                    <td style="padding: 10px; text-align: center;" id="total-106">10</td>
                    <td style="padding: 10px; text-align: center;" id="range-106">37-46</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="reset_times" data-list="106">11AM, 3:30PM</span>
                    </td>
                    <td style="padding: 10px;">-</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="description" data-list="106">5 workdays, 2x daily</span>
                    </td>
                </tr>

                <!-- List 107 - 1st Cool Down -->
                <tr style="background: #fef3c7;">
                    <td style="padding: 10px; font-weight: bold;">107</td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="name" data-list="107">1st Cool Down</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="days" data-list="107">5</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="calls_per_day" data-list="107">2</span>
                    </td>
                    <td style="padding: 10px; text-align: center;" id="total-107">10</td>
                    <td style="padding: 10px; text-align: center;" id="range-107">47-56</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="reset_times" data-list="107">10AM, 2PM</span>
                    </td>
                    <td style="padding: 10px;">-</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="description" data-list="107">5 workdays, 2x daily</span>
                    </td>
                </tr>

                <!-- List 108 - Rest Period -->
                <tr style="background: #e0e7ff;">
                    <td style="padding: 10px; font-weight: bold;">108</td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="name" data-list="108">Rest Period</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="days" data-list="108">7</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="calls_per_day" data-list="108">0</span>
                    </td>
                    <td style="padding: 10px; text-align: center;" id="total-108">0</td>
                    <td style="padding: 10px; text-align: center;" id="range-108">-</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="reset_times" data-list="108">None</span>
                    </td>
                    <td style="padding: 10px; color: #6b7280;">
                        <span>⏸️ NO CALLS</span>
                    </td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="description" data-list="108">7 workday cool down</span>
                    </td>
                </tr>

                <!-- List 109 - Final Attempt Phase -->
                <tr style="background: #fff7ed;">
                    <td style="padding: 10px; font-weight: bold;">109</td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="name" data-list="109">Final Attempt</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="days" data-list="109">5</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="calls_per_day" data-list="109">1</span>
                    </td>
                    <td style="padding: 10px; text-align: center;" id="total-109">5</td>
                    <td style="padding: 10px; text-align: center;" id="range-109">57-61</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="reset_times" data-list="109">12PM</span>
                    </td>
                    <td style="padding: 10px;">-</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="description" data-list="109">5 workdays or until TCPA expiry</span>
                    </td>
                </tr>

                <!-- List 110 - Final Archive -->
                <tr style="background: #f1f5f9;">
                    <td style="padding: 10px; font-weight: bold;">110</td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="name" data-list="110">Final Archive</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">∞</td>
                    <td style="padding: 10px; text-align: center;">0</td>
                    <td style="padding: 10px; text-align: center;" id="total-110">-</td>
                    <td style="padding: 10px; text-align: center;" id="range-110">62+</td>
                    <td style="padding: 10px;">None</td>
                    <td style="padding: 10px;">-</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="description" data-list="110">Permanent TCPA-compliant storage</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Summary Box -->
    <div style="display: flex; gap: 20px; justify-content: center; margin-top: 30px; flex-wrap: wrap;">
        <div style="background: #f0f9ff; padding: 15px 30px; border-radius: 10px; border: 2px solid #3b82f6;">
            <strong>Total Attempts:</strong> 61 calls
        </div>
        <div style="background: #f0fdf4; padding: 15px 30px; border-radius: 10px; border: 2px solid #10b981;">
            <strong>Campaign Duration:</strong> 30 workdays + 7 rest
        </div>
        <div style="background: #fef3c7; padding: 15px 30px; border-radius: 10px; border: 2px solid #f59e0b;">
            <strong>Speed to Lead:</strong> 3 calls in first hour
        </div>
    </div>
</div>

<style>
table {
    font-size: 14px;
    border-collapse: collapse;
}

th {
    position: sticky;
    top: 0;
    z-index: 10;
    background: linear-gradient(135deg, #667eea, #764ba2);
}

tr:hover {
    filter: brightness(0.95);
}

td, th {
    border: 1px solid #e5e7eb;
}
</style>
@endsection

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
                <!-- List 101 - Initial Contact -->
                <tr style="background: #f0fdf4;">
                    <td style="padding: 10px; font-weight: bold;">101</td>
                    <td style="padding: 10px; text-align: center;">Initial Contact</td>
                    <td style="padding: 10px; text-align: center;">0</td>
                    <td style="padding: 10px; text-align: center;">1</td>
                    <td style="padding: 10px; text-align: center;" id="total-101">1</td>
                    <td style="padding: 10px; text-align: center;" id="range-101">1</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="reset_times" data-list="101">Immediate</span>
                    </td>
                    <td style="padding: 10px;">-</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="description" data-list="101">Immediate call upon entry</span>
                    </td>
                </tr>

                <!-- List 102 - 20-Minute Follow-Up -->
                <tr style="background: #fef3c7;">
                    <td style="padding: 10px; font-weight: bold;">102</td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="name" data-list="102">20-Min Follow-Up</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="days" data-list="102">0</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="calls_per_day" data-list="102">1</span>
                    </td>
                    <td style="padding: 10px; text-align: center;" id="total-102">1</td>
                    <td style="padding: 10px; text-align: center;" id="range-102">2</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="reset_times" data-list="102">+20 minutes</span>
                    </td>
                    <td style="padding: 10px;">-</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="description" data-list="102">20 min after List 101</span>
                    </td>
                </tr>

                <!-- List 103 - Voicemail Phase -->
                <tr style="background: #fee2e2;">
                    <td style="padding: 10px; font-weight: bold;">103</td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="name" data-list="103">Voicemail Phase</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="days" data-list="103">0</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="calls_per_day" data-list="103">1</span>
                    </td>
                    <td style="padding: 10px; text-align: center;" id="total-103">1</td>
                    <td style="padding: 10px; text-align: center;" id="range-103">3</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="reset_times" data-list="103">After 102</span>
                    </td>
                    <td style="padding: 10px; color: #dc2626; font-weight: bold;">
                        <span class="editable" data-field="agent_alert" data-list="103">🔔 LEAVE VOICEMAIL</span>
                    </td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="description" data-list="103">Leave VM, set LVM status</span>
                    </td>
                </tr>

                <!-- List 104 - Hot Phase -->
                <tr style="background: #dbeafe;">
                    <td style="padding: 10px; font-weight: bold;">104</td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="name" data-list="104">Hot Phase</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="days" data-list="104">3</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="calls_per_day" data-list="104">4</span>
                    </td>
                    <td style="padding: 10px; text-align: center;" id="total-104">12</td>
                    <td style="padding: 10px; text-align: center;" id="range-104">4-15</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="reset_times" data-list="104">9AM, 11:30AM, 2PM, 4:30PM</span>
                    </td>
                    <td style="padding: 10px;">-</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="description" data-list="104">Aggressive 3 workdays</span>
                    </td>
                </tr>

                <!-- List 105 - Extended Follow-Up -->
                <tr style="background: #f3e8ff;">
                    <td style="padding: 10px; font-weight: bold;">105</td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="name" data-list="105">Extended Follow-Up</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="days" data-list="105">7</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="calls_per_day" data-list="105">3</span>
                    </td>
                    <td style="padding: 10px; text-align: center;" id="total-105">21</td>
                    <td style="padding: 10px; text-align: center;" id="range-105">16-36</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="reset_times" data-list="105">10AM, 1PM, 4PM</span>
                    </td>
                    <td style="padding: 10px;">-</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="description" data-list="105">7 workdays, 3x daily</span>
                    </td>
                </tr>

                <!-- List 106 - Secondary Follow-Up -->
                <tr style="background: #ecfdf5;">
                    <td style="padding: 10px; font-weight: bold;">106</td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="name" data-list="106">Secondary Follow-Up</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="days" data-list="106">5</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="calls_per_day" data-list="106">2</span>
                    </td>
                    <td style="padding: 10px; text-align: center;" id="total-106">10</td>
                    <td style="padding: 10px; text-align: center;" id="range-106">37-46</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="reset_times" data-list="106">11AM, 3:30PM</span>
                    </td>
                    <td style="padding: 10px;">-</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="description" data-list="106">5 workdays, 2x daily</span>
                    </td>
                </tr>

                <!-- List 107 - 1st Cool Down -->
                <tr style="background: #fef3c7;">
                    <td style="padding: 10px; font-weight: bold;">107</td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="name" data-list="107">1st Cool Down</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="days" data-list="107">5</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="calls_per_day" data-list="107">2</span>
                    </td>
                    <td style="padding: 10px; text-align: center;" id="total-107">10</td>
                    <td style="padding: 10px; text-align: center;" id="range-107">47-56</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="reset_times" data-list="107">10AM, 2PM</span>
                    </td>
                    <td style="padding: 10px;">-</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="description" data-list="107">5 workdays, 2x daily</span>
                    </td>
                </tr>

                <!-- List 108 - Rest Period -->
                <tr style="background: #e0e7ff;">
                    <td style="padding: 10px; font-weight: bold;">108</td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="name" data-list="108">Rest Period</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="days" data-list="108">7</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="calls_per_day" data-list="108">0</span>
                    </td>
                    <td style="padding: 10px; text-align: center;" id="total-108">0</td>
                    <td style="padding: 10px; text-align: center;" id="range-108">-</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="reset_times" data-list="108">None</span>
                    </td>
                    <td style="padding: 10px; color: #6b7280;">
                        <span>⏸️ NO CALLS</span>
                    </td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="description" data-list="108">7 workday cool down</span>
                    </td>
                </tr>

                <!-- List 109 - Final Attempt Phase -->
                <tr style="background: #fff7ed;">
                    <td style="padding: 10px; font-weight: bold;">109</td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="name" data-list="109">Final Attempt</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="days" data-list="109">5</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="calls_per_day" data-list="109">1</span>
                    </td>
                    <td style="padding: 10px; text-align: center;" id="total-109">5</td>
                    <td style="padding: 10px; text-align: center;" id="range-109">57-61</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="reset_times" data-list="109">12PM</span>
                    </td>
                    <td style="padding: 10px;">-</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="description" data-list="109">5 workdays or until TCPA expiry</span>
                    </td>
                </tr>

                <!-- List 110 - Final Archive -->
                <tr style="background: #f1f5f9;">
                    <td style="padding: 10px; font-weight: bold;">110</td>
                    <td style="padding: 10px; text-align: center;">
                        <span class="editable" data-field="name" data-list="110">Final Archive</span>
                    </td>
                    <td style="padding: 10px; text-align: center;">∞</td>
                    <td style="padding: 10px; text-align: center;">0</td>
                    <td style="padding: 10px; text-align: center;" id="total-110">-</td>
                    <td style="padding: 10px; text-align: center;" id="range-110">62+</td>
                    <td style="padding: 10px;">None</td>
                    <td style="padding: 10px;">-</td>
                    <td style="padding: 10px;">
                        <span class="editable" data-field="description" data-list="110">Permanent TCPA-compliant storage</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Summary Box -->
    <div style="display: flex; gap: 20px; justify-content: center; margin-top: 30px; flex-wrap: wrap;">
        <div style="background: #f0f9ff; padding: 15px 30px; border-radius: 10px; border: 2px solid #3b82f6;">
            <strong>Total Attempts:</strong> 61 calls
        </div>
        <div style="background: #f0fdf4; padding: 15px 30px; border-radius: 10px; border: 2px solid #10b981;">
            <strong>Campaign Duration:</strong> 30 workdays + 7 rest
        </div>
        <div style="background: #fef3c7; padding: 15px 30px; border-radius: 10px; border: 2px solid #f59e0b;">
            <strong>Speed to Lead:</strong> 3 calls in first hour
        </div>
    </div>
</div>

<style>
table {
    font-size: 14px;
    border-collapse: collapse;
}

th {
    position: sticky;
    top: 0;
    z-index: 10;
    background: linear-gradient(135deg, #667eea, #764ba2);
}

tr:hover {
    filter: brightness(0.95);
}

td, th {
    border: 1px solid #e5e7eb;
}
</style>
@endsection
