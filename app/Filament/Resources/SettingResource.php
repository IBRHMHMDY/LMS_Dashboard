<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'System Configuration';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('key')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->helperText('e.g., site_name, platform_commission_percentage, contact_email'),

                Forms\Components\Select::make('type')
                    ->options([
                        'string' => 'String / Text',
                        'boolean' => 'Boolean (True/False)',
                        'integer' => 'Integer (Number)',
                        'json' => 'JSON Array',
                    ])
                    ->required()
                    ->default('string'),

                Forms\Components\TextInput::make('group')
                    ->required()
                    ->default('general')
                    ->maxLength(255)
                    ->helperText('Used to group settings (e.g., general, social, financials).'),

                Forms\Components\Textarea::make('value')
                    ->required()
                    ->columnSpanFull()
                    ->rows(4)
                    ->helperText('For boolean use 1 or 0. For JSON ensure it is a valid format.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('value')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('group')
                    ->badge()
                    ->color('info')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->options(fn () => Setting::pluck('group', 'group')->unique()->toArray()),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalWidth('md'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]) // يفضل إيقاف الحذف الجماعي للإعدادات لخطورتها
            ->defaultSort('group', 'asc');
    }

    public static function getPages(): array
    {
        return [
            // استخدام ManageSettings لأننا بنيناه كـ Simple Resource (Modals)
            'index' => Pages\ManageSettings::route('/'),
        ];
    }
}