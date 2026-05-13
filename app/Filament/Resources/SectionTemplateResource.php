<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SectionTemplateResource\Pages;
use App\Models\SectionTemplate;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class SectionTemplateResource extends Resource
{
    protected static ?string $model = SectionTemplate::class;
    protected static ?string $navigationIcon = 'heroicon-o-template';
    protected static ?string $navigationGroup = 'การตั้งค่า';
    protected static ?string $modelLabel = 'Template หัวข้อ';
    protected static ?string $pluralModelLabel = 'Templates หัวข้อ';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Card::make()->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('key')->label('Key')->required()
                        ->unique(ignoreRecord: true)
                        ->regex('/^[a-z0-9_]+$/')
                        ->helperText('a-z, 0-9, _ เท่านั้น เช่น rus_strict_v1'),
                    Forms\Components\TextInput::make('name_th')->label('ชื่อ (ไทย)')->required(),
                    Forms\Components\TextInput::make('name_en')->label('Name (EN)')->required(),
                    Forms\Components\Toggle::make('is_active')->label('ใช้งาน')->default(true),
                    Forms\Components\Toggle::make('is_system_default')
                        ->label('เป็น Default ของระบบ')
                        ->helperText('จะมีได้ครั้งละ 1 template เท่านั้น'),
                ]),
                Forms\Components\Textarea::make('description_th')->label('คำอธิบาย (ไทย)')->rows(2),
                Forms\Components\Textarea::make('description_en')->label('Description (EN)')->rows(2),
            ]),

            Forms\Components\Card::make('หัวข้อใน Template')->schema([
                Forms\Components\HasManyRepeater::make('items')
                    ->relationship('items')
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('order')->numeric()->required()->label('ลำดับ'),
                            Forms\Components\TextInput::make('key')->required()->label('Key')
                                ->helperText('a-z, 0-9, _'),
                            Forms\Components\Select::make('type')->options([
                                'abstract' => 'บทคัดย่อ (TH)',
                                'abstract_en' => 'Abstract (EN)',
                                'keywords' => 'คำสำคัญ',
                                'richtext' => 'เนื้อหา (Rich Text)',
                                'bibliography' => 'บรรณานุกรม',
                            ])->default('richtext')->required(),
                            Forms\Components\TextInput::make('label_th')->required()->label('ชื่อหัวข้อ (ไทย)'),
                            Forms\Components\TextInput::make('label_en')->required()->label('Label (EN)'),
                            Forms\Components\Toggle::make('numbered')->default(true)->label('ใส่เลข'),
                            Forms\Components\Toggle::make('required')->default(true)->label('บังคับกรอก'),
                            Forms\Components\Toggle::make('default_visible')->default(true)->label('แสดง default'),
                        ]),
                    ])->orderable('order')->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')->searchable(),
                Tables\Columns\TextColumn::make('name_th')->label('ชื่อ (ไทย)')->searchable(),
                Tables\Columns\TextColumn::make('items_count')->counts('items')->label('จำนวนหัวข้อ'),
                Tables\Columns\IconColumn::make('is_system_default')->boolean()->label('Default'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('ใช้งาน'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSectionTemplates::route('/'),
            'create' => Pages\CreateSectionTemplate::route('/create'),
            'edit'   => Pages\EditSectionTemplate::route('/{record}/edit'),
        ];
    }
}
