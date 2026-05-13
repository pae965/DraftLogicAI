<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArticleResource\Pages;
use App\Models\Article;
use App\Models\SectionTemplate;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

/**
 * Filament 2.x Resource for Article
 */
class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'งานวิจัย';
    protected static ?string $modelLabel = 'บทความ';
    protected static ?string $pluralModelLabel = 'บทความ';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('article_tabs')->tabs([
                Forms\Components\Tabs\Tab::make('ข้อมูลทั่วไป')->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('title_th')
                            ->label('ชื่อบทความ (ไทย)')->required()->maxLength(500),
                        Forms\Components\TextInput::make('title_en')
                            ->label('ชื่อบทความ (อังกฤษ)')->required()->maxLength(500),
                        Forms\Components\TextInput::make('subtitle_th')->label('หัวข้อรอง (ไทย)'),
                        Forms\Components\TextInput::make('subtitle_en')->label('หัวข้อรอง (อังกฤษ)'),
                        Forms\Components\Select::make('primary_language')
                            ->label('ภาษาหลัก')
                            ->options(['th' => 'ไทย', 'en' => 'English'])
                            ->default('th')->required(),
                        Forms\Components\Select::make('status')
                            ->label('สถานะ')
                            ->options([
                                'draft' => 'ร่าง',
                                'pending_review' => 'รอตรวจสอบ',
                                'scheduled' => 'กำหนดเวลาเผยแพร่',
                                'published' => 'เผยแพร่แล้ว',
                                'archived' => 'จัดเก็บ',
                            ])->default('draft')->required(),
                    ]),
                ]),

                Forms\Components\Tabs\Tab::make('Template')->schema([
                    Forms\Components\Select::make('template_id')
                        ->label('Template หัวข้อ')
                        ->options(SectionTemplate::active()->pluck('name_th', 'id'))
                        ->searchable()->preload()
                        ->helperText('เลือก template สำหรับโครงสร้างหัวข้อบทความ'),
                ]),

                Forms\Components\Tabs\Tab::make('การค้นคว้าอิสระ (IS Info)')->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\Textarea::make('independent_study_title_th')
                            ->label('ชื่อ IS (ไทย)')->rows(2),
                        Forms\Components\Textarea::make('independent_study_title_en')
                            ->label('ชื่อ IS (อังกฤษ)')->rows(2),
                        Forms\Components\TextInput::make('degree_program_th')
                            ->label('หลักสูตร (ไทย)')
                            ->default('นิติศาสตรมหาบัณฑิต'),
                        Forms\Components\TextInput::make('degree_program_en')
                            ->label('หลักสูตร (อังกฤษ)')
                            ->default('Master of Laws'),
                        Forms\Components\TextInput::make('faculty_th')
                            ->label('คณะ (ไทย)')->default('คณะนิติศาสตร์'),
                        Forms\Components\TextInput::make('faculty_en')
                            ->label('คณะ (อังกฤษ)')->default('Faculty of Law'),
                        Forms\Components\TextInput::make('institution_th')
                            ->label('สถาบัน (ไทย)')
                            ->default('มหาวิทยาลัยเทคโนโลยีราชมงคลสุวรรณภูมิ'),
                        Forms\Components\TextInput::make('institution_en')
                            ->label('สถาบัน (อังกฤษ)')
                            ->default('Rajamangala University of Technology Suvarnabhumi'),
                    ]),
                ]),

                Forms\Components\Tabs\Tab::make('ผู้เขียน')->schema([
                    Forms\Components\Select::make('primary_author_id')
                        ->label('ผู้เขียนหลัก')
                        ->relationship('primaryAuthor', 'name')
                        ->searchable()->required(),
                ]),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('title_th')->label('ชื่อ (ไทย)')->searchable()->limit(50),
                Tables\Columns\TextColumn::make('title_en')->label('Title (EN)')->searchable()->limit(40)->toggleable(),
                Tables\Columns\BadgeColumn::make('status')->label('สถานะ')->colors([
                    'secondary' => 'draft',
                    'warning' => 'pending_review',
                    'success' => 'published',
                    'danger' => 'archived',
                ]),
                Tables\Columns\TextColumn::make('primaryAuthor.name')->label('ผู้เขียน')->searchable(),
                Tables\Columns\TextColumn::make('template.name_th')->label('Template')->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')->label('แก้ไขล่าสุด')->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'draft' => 'ร่าง',
                    'pending_review' => 'รอตรวจ',
                    'published' => 'เผยแพร่',
                    'archived' => 'จัดเก็บ',
                ]),
                Tables\Filters\SelectFilter::make('template')->relationship('template', 'name_th'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $q = parent::getEloquentQuery();
        if (!$user || !$user->isEditor()) {
            $q->where('primary_author_id', $user?->id ?? 0);
        }
        return $q;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit'   => Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
