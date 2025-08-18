@extends('layouts.app')

@section('title', 'Import Leads')

@section('content')
    <div class="card">
        <h2 class="card-title">Bulk Lead Import</h2>
        <p style="color: #6b7280; margin-bottom: 20px;">Upload CSV files to import leads in bulk.</p>
        
        <div style="border: 2px dashed #e5e7eb; border-radius: 8px; padding: 40px; text-align: center; background: #f9fafb;">
            <div style="font-size: 3rem; margin-bottom: 16px;">📁</div>
            <h3 style="font-weight: 600; margin-bottom: 8px;">Drop CSV file here</h3>
            <p style="color: #6b7280; margin-bottom: 20px;">or click to browse</p>
            <input type="file" accept=".csv" style="display: none;" id="file-input">
            <button class="btn btn-primary" onclick="document.getElementById('file-input').click()">
                Choose File
            </button>
        </div>
        
        <div style="margin-top: 30px;">
            <h3 style="font-weight: 600; margin-bottom: 12px;">Recent Imports</h3>
            <div style="background: #f3f4f6; padding: 20px; border-radius: 8px; text-align: center; color: #9ca3af;">
                No recent imports
            </div>
        </div>
    </div>
@endsection


@section('title', 'Import Leads')

@section('content')
    <div class="card">
        <h2 class="card-title">Bulk Lead Import</h2>
        <p style="color: #6b7280; margin-bottom: 20px;">Upload CSV files to import leads in bulk.</p>
        
        <div style="border: 2px dashed #e5e7eb; border-radius: 8px; padding: 40px; text-align: center; background: #f9fafb;">
            <div style="font-size: 3rem; margin-bottom: 16px;">📁</div>
            <h3 style="font-weight: 600; margin-bottom: 8px;">Drop CSV file here</h3>
            <p style="color: #6b7280; margin-bottom: 20px;">or click to browse</p>
            <input type="file" accept=".csv" style="display: none;" id="file-input">
            <button class="btn btn-primary" onclick="document.getElementById('file-input').click()">
                Choose File
            </button>
        </div>
        
        <div style="margin-top: 30px;">
            <h3 style="font-weight: 600; margin-bottom: 12px;">Recent Imports</h3>
            <div style="background: #f3f4f6; padding: 20px; border-radius: 8px; text-align: center; color: #9ca3af;">
                No recent imports
            </div>
        </div>
    </div>
@endsection

