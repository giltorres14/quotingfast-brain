<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadResource\Pages;
use App\Models\Lead;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

/**
 * LeadResource
 *
 * Defines the Filament resource for managing leads. It specifies the
 * form schema used to create or edit a lead and the table schema used
 * to list and filter leads. Filters include search, state, source,
 * type, and date ranges for the received_at column.
 */
class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Leads';

    /**
     * Define the form used to create and edit leads.
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make([
                    Forms\Components\TextInput::make('first_name')
                        ->label('First Name'),
                    Forms\Components\TextInput::make('last_name')
                        ->label('Last Name'),
                    Forms\Components\TextInput::make('phone')
                        ->label('Phone'),
                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->email(),
                ])->columns(2),
                Forms\Components\Group::make([
                    Forms\Components\Textarea::make('address')
                        ->label('Address')
                        ->rows(2),
                    Forms\Components\TextInput::make('city')
                        ->label('City'),
                    Forms\Components\TextInput::make('state')
                        ->label('State'),
                    Forms\Components\TextInput::make('zip_code')
                        ->label('Zip Code'),
                ])->columns(2),
                Forms\Components\Select::make('source')
                    ->label('Source')
                    ->options([
                        'leadsquotingfast' => 'LeadsQuotingFast',
                        'facebook' => 'Facebook',
                        'google' => 'Google',
                        'organic' => 'Organic',
                        'referral' => 'Referral',
                        'other' => 'Other',
                    ])
                    ->searchable(),
                Forms\Components\Select::make('type')
                    ->label('Type')
                    ->options([
                        'auto' => 'Auto Insurance',
                        'home' => 'Home Insurance',
                        'life' => 'Life Insurance',
                        'health' => 'Health Insurance',
                        'commercial' => 'Commercial Insurance',
                        'internet' => 'Internet Lead',
                    ])
                    ->searchable(),
                Forms\Components\DateTimePicker::make('received_at')
                    ->label('Received At'),
                Forms\Components\DateTimePicker::make('joined_at')
                    ->label('Joined At'),
                Forms\Components\Section::make('LeadsQuotingFast Data')
                    ->description('Detailed information from the LeadsQuotingFast webhook')
                    ->schema([
                        Forms\Components\Placeholder::make('drivers_display')
                            ->label('Drivers Information')
                            ->content(function ($record) {
                                if (!$record || empty($record->drivers)) return 'No driver data available';
                                $drivers = is_string($record->drivers) ? json_decode($record->drivers, true) : $record->drivers;
                                if (!is_array($drivers)) return 'Invalid driver data format';
                                
                                $html = '<div style="max-height: 300px; overflow-y: auto; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px;">';
                                foreach ($drivers as $i => $driver) {
                                    $html .= '<div style="margin-bottom: 15px; padding: 10px; border-left: 3px solid #3b82f6; background-color: #f8fafc;">';
                                    $html .= '<strong>Driver ' . ($i + 1) . '</strong><br>';
                                    $html .= 'Name: ' . ($driver['first_name'] ?? '') . ' ' . ($driver['last_name'] ?? '') . '<br>';
                                    $html .= 'Occupation: ' . ($driver['occupation'] ?? 'N/A') . '<br>';
                                    $html .= 'Age Licensed: ' . ($driver['age_licensed'] ?? 'N/A') . '<br>';
                                    $html .= 'SR22 Required: ' . (($driver['requires_sr22'] ?? false) ? 'Yes' : 'No') . '<br>';
                                    $html .= 'Violations: ' . count($driver['major_violations'] ?? []) . ', ';
                                    $html .= 'Tickets: ' . count($driver['tickets'] ?? []) . ', ';
                                    $html .= 'Claims: ' . count($driver['claims'] ?? []);
                                    $html .= '</div>';
                                }
                                $html .= '</div>';
                                return new \Illuminate\Support\HtmlString($html);
                            }),
                            
                        Forms\Components\Placeholder::make('vehicles_display')
                            ->label('Vehicles Information')
                            ->content(function ($record) {
                                if (!$record || empty($record->vehicles)) return 'No vehicle data available';
                                $vehicles = is_string($record->vehicles) ? json_decode($record->vehicles, true) : $record->vehicles;
                                if (!is_array($vehicles)) return 'Invalid vehicle data format';
                                
                                $html = '<div style="max-height: 300px; overflow-y: auto; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px;">';
                                foreach ($vehicles as $i => $vehicle) {
                                    $html .= '<div style="margin-bottom: 15px; padding: 10px; border-left: 3px solid #10b981; background-color: #f0fdf4;">';
                                    $html .= '<strong>Vehicle ' . ($i + 1) . '</strong><br>';
                                    $html .= 'Make/Model: ' . ($vehicle['make'] ?? '') . ' ' . ($vehicle['model'] ?? '') . '<br>';
                                    $html .= 'Year: ' . ($vehicle['year'] ?? 'N/A') . '<br>';
                                    $html .= 'VIN: ' . ($vehicle['vin'] ?? 'N/A') . '<br>';
                                    $html .= 'Primary Use: ' . ($vehicle['primary_use'] ?? 'N/A') . '<br>';
                                    $html .= 'Annual Miles: ' . number_format($vehicle['annual_miles'] ?? 0) . '<br>';
                                    $html .= 'Ownership: ' . ($vehicle['ownership'] ?? 'N/A');
                                    $html .= '</div>';
                                }
                                $html .= '</div>';
                                return new \Illuminate\Support\HtmlString($html);
                            }),
                            
                        Forms\Components\Placeholder::make('policy_display')
                            ->label('Current Policy Information')
                            ->content(function ($record) {
                                if (!$record || empty($record->current_policy)) return 'No policy data available';
                                $policy = is_string($record->current_policy) ? json_decode($record->current_policy, true) : $record->current_policy;
                                if (!is_array($policy)) return 'Invalid policy data format';
                                
                                $html = '<div style="border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; border-left: 3px solid #f59e0b; background-color: #fffbeb;">';
                                foreach ($policy as $key => $value) {
                                    $html .= '<strong>' . ucfirst(str_replace('_', ' ', $key)) . ':</strong> ' . $value . '<br>';
                                }
                                $html .= '</div>';
                                return new \Illuminate\Support\HtmlString($html);
                            }),
                            
                        Forms\Components\Textarea::make('drivers')
                            ->label('Drivers (Raw JSON)')
                            ->rows(6)
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(function ($state) {
                                if (empty($state)) return 'No driver data';
                                $drivers = is_string($state) ? json_decode($state, true) : $state;
                                if (!is_array($drivers)) return 'Invalid driver data';
                                return json_encode($drivers, JSON_PRETTY_PRINT);
                            })
                            ->helperText('Raw JSON data from LeadsQuotingFast (read-only)'),
                            
                        Forms\Components\Textarea::make('vehicles')
                            ->label('Vehicles (Raw JSON)')
                            ->rows(6)
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(function ($state) {
                                if (empty($state)) return 'No vehicle data';
                                $vehicles = is_string($state) ? json_decode($state, true) : $state;
                                if (!is_array($vehicles)) return 'Invalid vehicle data';
                                return json_encode($vehicles, JSON_PRETTY_PRINT);
                            })
                            ->helperText('Raw JSON data from LeadsQuotingFast (read-only)'),
                            
                        Forms\Components\Textarea::make('current_policy')
                            ->label('Current Policy (Raw JSON)')
                            ->rows(3)
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(function ($state) {
                                if (empty($state)) return 'No policy data';
                                $policy = is_string($state) ? json_decode($state, true) : $state;
                                if (!is_array($policy)) return 'Invalid policy data';
                                return json_encode($policy, JSON_PRETTY_PRINT);
                            })
                            ->helperText('Raw JSON data from LeadsQuotingFast (read-only)'),
                            
                        Forms\Components\Textarea::make('payload')
                            ->label('Complete Raw Payload (JSON)')
                            ->rows(8)
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(function ($state) {
                                if (empty($state)) return 'No payload data';
                                $payload = is_string($state) ? json_decode($state, true) : $state;
                                if (!is_array($payload)) return 'Invalid payload data';
                                return json_encode($payload, JSON_PRETTY_PRINT);
                            })
                            ->helperText('Complete LeadsQuotingFast webhook payload (read-only)')
                    ])
                    ->collapsible()
                    ->collapsed(false),
            ]);
    }

    /**
     * Define the table used to list and filter leads.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->label('City')
                    ->sortable(),
                Tables\Columns\TextColumn::make('state')
                    ->label('State')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('source')
                    ->label('Source')
                    ->colors([
                        'primary' => 'leadsquotingfast',
                        'secondary' => 'facebook',
                        'success' => 'google',
                        'warning' => 'organic',
                    ]),
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'success' => 'auto',
                        'warning' => 'home',
                        'danger' => 'life',
                        'info' => 'internet',
                    ]),
                Tables\Columns\TextColumn::make('received_at')
                    ->label('Received At')
                    ->dateTime('m/d/Y H:i'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('state')
                    ->options(
                        fn() => Lead::query()->distinct()->pluck('state', 'state')->filter()->all()
                    ),
                Tables\Filters\SelectFilter::make('source')
                    ->options([
                        'leadsquotingfast' => 'LeadsQuotingFast',
                        'facebook' => 'Facebook',
                        'google' => 'Google',
                        'organic' => 'Organic',
                    ]),
                Tables\Filters\Filter::make('received_range')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Received From'),
                        Forms\Components\DatePicker::make('to')
                            ->label('Received To'),
                    ])
                    ->query(fn($query, $data) => $query
                        ->when($data['from'], fn($q, $date) => $q->whereDate('received_at', '>=', $date))
                        ->when($data['to'], fn($q, $date) => $q->whereDate('received_at', '<=', $date))
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Define the detailed view for individual leads
     */
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Contact Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Full Name')
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('phone')
                            ->label('Phone')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('email')
                            ->label('Email')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('address')
                            ->label('Address'),
                        Infolists\Components\TextEntry::make('city')
                            ->label('City'),
                        Infolists\Components\TextEntry::make('state')
                            ->label('State'),
                        Infolists\Components\TextEntry::make('zip_code')
                            ->label('Zip Code'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Lead Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('source')
                            ->label('Source')
                            ->badge()
                            ->color('primary'),
                        Infolists\Components\TextEntry::make('type')
                            ->label('Type')
                            ->badge()
                            ->color('success'),
                        Infolists\Components\TextEntry::make('received_at')
                            ->label('Received At')
                            ->dateTime('M d, Y H:i'),
                        Infolists\Components\TextEntry::make('joined_at')
                            ->label('Joined At')
                            ->dateTime('M d, Y H:i'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Drivers Information')
                    ->description('Detailed information about all drivers from LeadsQuotingFast')
                    ->schema([
                        Infolists\Components\TextEntry::make('drivers')
                            ->label('Driver Details')
                            ->formatStateUsing(function ($state) {
                                if (empty($state)) return 'No driver data available';
                                $drivers = is_string($state) ? json_decode($state, true) : $state;
                                if (!is_array($drivers)) return 'Invalid driver data format';
                                
                                $html = '<div style="font-family: monospace; line-height: 1.4;">';
                                foreach ($drivers as $i => $driver) {
                                    $html .= '<div style="margin-bottom: 20px; padding: 10px; border-left: 3px solid #3b82f6; background-color: #f8fafc;">';
                                    $html .= '<h4 style="margin: 0 0 10px 0; color: #1e40af;">Driver ' . ($i + 1) . '</h4>';
                                    $html .= '<p><strong>Name:</strong> ' . ($driver['first_name'] ?? '') . ' ' . ($driver['last_name'] ?? '') . '</p>';
                                    $html .= '<p><strong>Occupation:</strong> ' . ($driver['occupation'] ?? 'N/A') . '</p>';
                                    $html .= '<p><strong>Age Licensed:</strong> ' . ($driver['age_licensed'] ?? 'N/A') . '</p>';
                                    $html .= '<p><strong>License Status:</strong> ' . ($driver['license_status'] ?? 'N/A') . '</p>';
                                    $html .= '<p><strong>SR22 Required:</strong> ' . (($driver['requires_sr22'] ?? false) ? '✅ Yes' : '❌ No') . '</p>';
                                    $html .= '<p><strong>Major Violations:</strong> ' . count($driver['major_violations'] ?? []) . '</p>';
                                    $html .= '<p><strong>Tickets:</strong> ' . count($driver['tickets'] ?? []) . '</p>';
                                    $html .= '<p><strong>Claims:</strong> ' . count($driver['claims'] ?? []) . '</p>';
                                    $html .= '</div>';
                                }
                                $html .= '</div>';
                                return $html;
                            })
                            ->html()
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Vehicles Information')
                    ->description('Detailed information about all vehicles from LeadsQuotingFast')
                    ->schema([
                        Infolists\Components\TextEntry::make('vehicles')
                            ->label('Vehicle Details')
                            ->formatStateUsing(function ($state) {
                                if (empty($state)) return 'No vehicle data available';
                                $vehicles = is_string($state) ? json_decode($state, true) : $state;
                                if (!is_array($vehicles)) return 'Invalid vehicle data format';
                                
                                $html = '<div style="font-family: monospace; line-height: 1.4;">';
                                foreach ($vehicles as $i => $vehicle) {
                                    $html .= '<div style="margin-bottom: 20px; padding: 10px; border-left: 3px solid #10b981; background-color: #f0fdf4;">';
                                    $html .= '<h4 style="margin: 0 0 10px 0; color: #047857;">Vehicle ' . ($i + 1) . '</h4>';
                                    $html .= '<p><strong>Make/Model:</strong> ' . ($vehicle['make'] ?? '') . ' ' . ($vehicle['model'] ?? '') . '</p>';
                                    $html .= '<p><strong>Year:</strong> ' . ($vehicle['year'] ?? 'N/A') . '</p>';
                                    $html .= '<p><strong>VIN:</strong> ' . ($vehicle['vin'] ?? 'N/A') . '</p>';
                                    $html .= '<p><strong>Primary Use:</strong> ' . ($vehicle['primary_use'] ?? 'N/A') . '</p>';
                                    $html .= '<p><strong>Annual Miles:</strong> ' . number_format($vehicle['annual_miles'] ?? 0) . '</p>';
                                    $html .= '<p><strong>Ownership:</strong> ' . ($vehicle['ownership'] ?? 'N/A') . '</p>';
                                    $html .= '</div>';
                                }
                                $html .= '</div>';
                                return $html;
                            })
                            ->html()
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Policy Information')
                    ->description('Current insurance policy details')
                    ->schema([
                        Infolists\Components\TextEntry::make('current_policy')
                            ->label('Policy Details')
                            ->formatStateUsing(function ($state) {
                                if (empty($state)) return 'No policy data available';
                                $policy = is_string($state) ? json_decode($state, true) : $state;
                                if (!is_array($policy)) return 'Invalid policy data format';
                                
                                $html = '<div style="font-family: monospace; line-height: 1.4; padding: 10px; border-left: 3px solid #f59e0b; background-color: #fffbeb;">';
                                foreach ($policy as $key => $value) {
                                    $html .= '<p><strong>' . ucfirst(str_replace('_', ' ', $key)) . ':</strong> ' . $value . '</p>';
                                }
                                $html .= '</div>';
                                return $html;
                            })
                            ->html()
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Raw Payload')
                    ->description('Complete original data from LeadsQuotingFast webhook')
                    ->schema([
                        Infolists\Components\TextEntry::make('payload')
                            ->label('Complete LeadsQuotingFast Data (JSON)')
                            ->formatStateUsing(function ($state) {
                                if (empty($state)) return 'No payload data available';
                                $payload = is_string($state) ? json_decode($state, true) : $state;
                                return '<pre style="background-color: #1f2937; color: #f9fafb; padding: 15px; border-radius: 6px; font-size: 12px; overflow-x: auto;">' . json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</pre>';
                            })
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    /**
     * Define which attributes are searchable.
     */
    public static function getSearchableColumns(): array
    {
        return ['name', 'phone', 'email'];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Leads Management';
    }

    /**
     * Define which pages are available for this resource.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeads::route('/'),
            'create' => Pages\CreateLead::route('/create'),
            'view' => Pages\ViewLead::route('/{record}'),
            'edit' => Pages\EditLead::route('/{record}/edit'),
        ];
    }
} 