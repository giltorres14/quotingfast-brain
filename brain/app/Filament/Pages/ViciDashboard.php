<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class ViciDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-phone';
    protected static string $view = 'filament.pages.vici-dashboard';
    protected static ?string $title = 'Vici Dashboard';
    protected static ?string $navigationLabel = 'Vici Dashboard';
    protected static ?string $slug = 'vici-dashboard';
    protected static ?int $navigationSort = 1;

    public function getViewData(): array
    {
        try {
            // Get total calls
            $totalCalls = DB::table('vici_call_metrics')->count();
        } catch (\Exception $e) {
            $totalCalls = 0;
        }

        try {
            // Get today's calls
            $todayCalls = DB::table('vici_call_metrics')
                ->whereDate('created_at', today())
                ->count();
        } catch (\Exception $e) {
            $todayCalls = 0;
        }

        try {
            // Get connected calls
            $connectedCalls = DB::table('vici_call_metrics')
                ->where('status', 'XFER')
                ->count();
        } catch (\Exception $e) {
            $connectedCalls = 0;
        }

        try {
            // Get orphan calls
            $orphanCalls = DB::table('orphan_call_logs')->count();
        } catch (\Exception $e) {
            $orphanCalls = 0;
        }

        try {
            // Get list distribution
            $listDistribution = DB::table('leads')
                ->select('vici_list_id', DB::raw('count(*) as count'))
                ->whereNotNull('vici_list_id')
                ->groupBy('vici_list_id')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->vici_list_id => $item->count];
                })
                ->toArray();
        } catch (\Exception $e) {
            $listDistribution = [];
        }

        try {
            // Get recent calls
            $recentCalls = DB::table('vici_call_metrics')
                ->select('lead_id', 'phone', 'status', 'duration', 'agent', 'created_at')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        } catch (\Exception $e) {
            $recentCalls = collect();
        }

        return [
            'totalCalls' => $totalCalls,
            'todayCalls' => $todayCalls,
            'connectedCalls' => $connectedCalls,
            'orphanCalls' => $orphanCalls,
            'listDistribution' => $listDistribution,
            'recentCalls' => $recentCalls,
        ];
    }
}



