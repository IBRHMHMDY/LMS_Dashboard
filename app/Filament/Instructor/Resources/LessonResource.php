<?php

namespace App\Filament\Instructor\Resources;

use App\Enums\LessonType;
use App\Filament\Instructor\Resources\LessonResource\Pages;
use App\Models\Lesson;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class LessonResource extends Resource
{
    protected static ?string $model = Lesson::class;

    protected static ?string $navigationIcon = 'heroicon-o-play-circle';

    // 1. إخفاء الدروس من القائمة الجانبية
    protected static bool $shouldRegisterNavigation = false;

    public static function getModelLabel(): string
    {
        return __('Lesson');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Lessons');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Lesson Details'))
                    ->schema([
                        Forms\Components\Hidden::make('section_id')
                            ->default(request()->query('section_id'))
                            ->required(),

                        Forms\Components\TextInput::make('title')
                            ->label(__('Lesson Title'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', \Illuminate\Support\Str::slug($state . '-' . uniqid()))),

                        Forms\Components\Hidden::make('slug')->required(),

                        // 1. نوع الدرس (المتحكم الرئيسي)
                        Forms\Components\Select::make('lesson_type')
                            ->label(__('Lesson Type'))
                            ->options(\App\Enums\LessonType::class)
                            ->required()
                            ->live() // تفعيل التفاعل المباشر
                            ->afterStateUpdated(function (Forms\Set $set) {
                                // تصفير الحقول عند التبديل لمنع تداخل البيانات
                                $set('video_url_link', null);
                                $set('video_upload_file', null);
                                $set('duration_in_minutes', 0);
                                $set('pdf_upload_file', null);
                            }),

                        // 2. حقل رابط الفيديو (يظهر فقط في حالة الرابط) مع زر اللصق الذكي
                        Forms\Components\TextInput::make('video_url_link')
                            ->label(__('Video URL'))
                            ->url()
                            ->visible(fn (Forms\Get $get) => $get('lesson_type') === \App\Enums\LessonType::VIDEO_URL->value)
                            ->required(fn (Forms\Get $get) => $get('lesson_type') === \App\Enums\LessonType::VIDEO_URL->value)
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('paste')
                                    ->icon('heroicon-m-clipboard-document')
                                    ->label(__('Paste'))
                                    ->color('primary')
                                    ->extraAttributes([
                                        // كود Alpine.js للصق من الحافظة مباشرة
                                        'x-on:click' => "navigator.clipboard.readText().then(text => \$wire.set('data.video_url_link', text)).catch(err => alert('".__('Allow clipboard access')."'))"
                                    ])
                            ),

                        // 3. حقل رفع الفيديو (يظهر فقط في حالة الرفع) مع قراءة المدة تلقائياً
                        Forms\Components\FileUpload::make('video_upload_file')
                            ->label(__('Upload Video'))
                            ->directory('lessons/videos')
                            ->acceptedFileTypes(['video/mp4', 'video/webm'])
                            ->visible(fn (Forms\Get $get) => $get('lesson_type') === \App\Enums\LessonType::VIDEO_UPLOAD->value)
                            ->required(fn (Forms\Get $get) => $get('lesson_type') === \App\Enums\LessonType::VIDEO_UPLOAD->value)
                            ->live() 
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                // سحر قراءة المدة التلقائي
                                if ($state) {
                                    try {
                                        $getID3 = new \getID3;
                                        $file = $getID3->analyze($state->getRealPath());
                                        if (isset($file['playtime_seconds'])) {
                                            $set('duration_in_minutes', ceil($file['playtime_seconds'] / 60));
                                        }
                                    } catch (\Exception $e) {
                                        // تجاوز في حال كان الملف غير مقروء
                                    }
                                }
                            }),

                        Forms\Components\FileUpload::make('pdf_upload_file')
                            ->label(__('Upload PDF'))
                            ->directory('lessons/pdfs') // مجلد خاص بملفات الـ PDF
                            ->acceptedFileTypes(['application/pdf']) // قبول ملفات PDF فقط
                            ->visible(fn (Forms\Get $get) => $get('lesson_type') === \App\Enums\LessonType::PDF->value)
                            ->required(fn (Forms\Get $get) => $get('lesson_type') === \App\Enums\LessonType::PDF->value)
                            ->columnSpanFull(),
                        // 4. حقل المحتوى النصي
                        Forms\Components\RichEditor::make('content')
                            ->label(__('Text Content'))
                            ->visible(fn (Forms\Get $get) => $get('lesson_type') === \App\Enums\LessonType::TEXT->value)
                            ->required(fn (Forms\Get $get) => $get('lesson_type') === \App\Enums\LessonType::TEXT->value)
                            ->columnSpanFull(),

                        // 5. حقل المدة (يظهر فقط للفيديو المرفوع أو الرابط)
                        Forms\Components\TextInput::make('duration_in_minutes')
                            ->label(__('Duration (Minutes)'))
                            ->numeric()
                            ->default(0)
                            ->visible(fn (Forms\Get $get) => in_array($get('lesson_type'), [
                                \App\Enums\LessonType::VIDEO_URL->value,
                                \App\Enums\LessonType::VIDEO_UPLOAD->value
                            ]))
                            ->required(fn (Forms\Get $get) => in_array($get('lesson_type'), [
                                \App\Enums\LessonType::VIDEO_URL->value,
                                \App\Enums\LessonType::VIDEO_UPLOAD->value
                            ])),

                        Forms\Components\Toggle::make('is_free_preview')
                            ->label(__('Free Preview'))
                            ->default(false),

                        Forms\Components\Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            
            // 2. تصفية الجدول لعرض دروس القسم المحدد فقط
            ->modifyQueryUsing(function (Builder $query) {
                $sectionId = request()->query('section_id');
                if ($sectionId) {
                    $query->where('section_id', $sectionId);
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('Lesson Title'))
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('lesson_type')
                    ->label(__('Type'))
                    ->badge()
                    ->color(fn (LessonType $state): string => match ($state) {
                        LessonType::VIDEO_URL->value => 'info',
                        LessonType::VIDEO_UPLOAD->value => 'warning',
                        LessonType::TEXT->value => 'success',
                        LessonType::PDF->value => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_free_preview')
                    ->label(__('Free'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('duration_in_minutes')
                    ->label(__('Duration'))
                    ->suffix(' ' . __('mins'))
                    ->color('gray'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(__('Edit'))
                    ->color('gray')
                    // تمرير الروابط للحفاظ على شجرة التصفح
                    ->url(fn (Lesson $record): string => LessonResource::getUrl('edit', [
                        'record' => $record,
                        'section_id' => request()->query('section_id'),
                        'course_id' => request()->query('course_id')
                    ])),
                
                Tables\Actions\DeleteAction::make()
                    ->label(__('Delete')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    // تسجيل الصفحات الجديدة
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLessons::route('/'),
            'create' => Pages\CreateLesson::route('/create'),
            'edit' => Pages\EditLesson::route('/{record}/edit'),
        ];
    }
}