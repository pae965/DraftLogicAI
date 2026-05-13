<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'การตั้งค่า';
    protected static ?string $modelLabel = 'ผู้ใช้';
    protected static ?string $pluralModelLabel = 'ผู้ใช้';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('user_tabs')->tabs([
                Forms\Components\Tabs\Tab::make('ข้อมูลพื้นฐาน')->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('name')->label('ชื่อแสดง (Login)')->required(),
                        Forms\Components\TextInput::make('email')->label('Email')->email()->required()->unique(ignoreRecord: true),
                        Forms\Components\Select::make('role')->options([
                            'super_admin' => 'Super Admin',
                            'admin' => 'Admin',
                            'editor' => 'Editor',
                            'author' => 'Author',
                        ])->default('author')->required(),
                        Forms\Components\Select::make('preferred_language')->options(['th' => 'ไทย', 'en' => 'EN'])->default('th'),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn ($s) => filled($s) ? Hash::make($s) : null)
                            ->dehydrated(fn ($s) => filled($s))
                            ->required(fn (string $context) => $context === 'create'),
                    ]),
                ]),
                Forms\Components\Tabs\Tab::make('ชื่อ-นามสกุล (TH/EN)')->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('title_th')->label('คำนำหน้า (TH)'),
                        Forms\Components\TextInput::make('title_en')->label('Title (EN)'),
                        Forms\Components\TextInput::make('name_th')->label('ชื่อ (ไทย)'),
                        Forms\Components\TextInput::make('name_en')->label('Name (EN)'),
                    ]),
                ]),
                Forms\Components\Tabs\Tab::make('Affiliation Default')->schema([
                    Forms\Components\Textarea::make('default_affiliation_th')->label('สังกัด (TH)'),
                    Forms\Components\Textarea::make('default_affiliation_en')->label('Affiliation (EN)'),
                    Forms\Components\Textarea::make('default_address_th')->label('ที่อยู่ (TH)'),
                    Forms\Components\Textarea::make('default_address_en')->label('Address (EN)'),
                    Forms\Components\TextInput::make('orcid_id')->label('ORCID'),
                    Forms\Components\TextInput::make('profile_url')->label('Profile URL'),
                ]),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\BadgeColumn::make('role')->colors([
                    'danger' => 'super_admin',
                    'warning' => 'admin',
                    'success' => 'editor',
                    'secondary' => 'author',
                ]),
                Tables\Columns\TextColumn::make('created_at')->date()->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')->options([
                    'super_admin' => 'Super Admin', 'admin' => 'Admin',
                    'editor' => 'Editor', 'author' => 'Author',
                ]),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
