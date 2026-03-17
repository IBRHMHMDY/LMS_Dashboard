<?php

namespace App\Filament\Instructor\Resources;

use App\Enums\LessonType;
use App\Filament\Instructor\Resources\LessonResource\Pages;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Section;
use Filament\Forms;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LessonResource extends Resource
{
    protected static ?string $model = Lesson::class;

    protected static ?string $navigationIcon = 'heroicon-o-play-circle';
    protected static ?string $navigationLabel = 'Course Lessons';
    protected static ?string $modelLabel = 'Lesson';
    protected static ?int $navigationSort = 3; // يظهر تحت الوحدات

    // قصر عرض الدروس على كورسات المدرب الحالي فقط
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereHas('section.course', function (Builder $query) {
            $query->where('instructor_id', Auth::id());
        });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // 1. اختيار الكورس (حقل غير مرتبط بقاعدة البيانات، يستخدم فقط لتصفية الوحدات)
                Select::make('course_id')
                    ->label('Select Course First')
                    ->options(Course::where('instructor_id', Auth::id())->pluck('title', 'id'))
                    ->live() // تفعيل التحديث المباشر
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('section_id', null)) // تفريغ الوحدة عند تغيير الكورس
                    ->dehydrated(false) // لمنع حفظ هذا الحقل في جدول الدروس
                    ->searchable()
                    ->preload()
                    ->required(),

                // 2. اختيار الوحدة (يعتمد على الكورس المختار)
                Select::make('section_id')
                    ->label('Section')
                    ->options(fn (Get $get) => Section::where('course_id', $get('course_id'))->pluck('title', 'id'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->disabled(fn (Get $get) => ! $get('course_id')), // تعطيل الحقل إذا لم يتم اختيار كورس

                // 3. بيانات الدرس الأساسية
                TextInput::make('title')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $set('slug', Str::slug($state) . '-' . uniqid()))
                    ->maxLength(255),

                TextInput::make('slug')
                    ->required()
                    ->readOnly()
                    ->unique(ignoreRecord: true),

                // 4. نوع الدرس (القلب النابض للاستمارة الذكية)
                Select::make('lesson_type')
                    ->options(LessonType::class)
                    ->default(LessonType::VIDEO_URL)
                    ->live() // ضروري جداً لتغيير الحقول التالية بناءً على الاختيار
                    ->required()
                    ->columnSpanFull(),

                // === الحقول التفاعلية (تظهر وتختفي حسب نوع الدرس) ===
                
                // أ. رابط يوتيوب/فيميو
                TextInput::make('video_url')
                    ->label('Video URL')
                    ->url()
                    ->visible(fn (Get $get) => $get('lesson_type') === LessonType::VIDEO_URL->value)
                    ->required(fn (Get $get) => $get('lesson_type') === LessonType::VIDEO_URL->value)
                    ->columnSpanFull(),

                // ب. رفع فيديو (Spatie Media Library)
                SpatieMediaLibraryFileUpload::make('video_upload')
                    ->collection('videos')
                    ->label('Upload Video File')
                    ->acceptedFileTypes(['video/mp4', 'video/webm'])
                    ->maxSize(102400) // 100MB الحد الأقصى
                    ->visible(fn (Get $get) => $get('lesson_type') === LessonType::VIDEO_UPLOAD->value)
                    ->required(fn (Get $get) => $get('lesson_type') === LessonType::VIDEO_UPLOAD->value)
                    ->columnSpanFull(),

                // ج. رفع ملف PDF
                SpatieMediaLibraryFileUpload::make('pdf_file')
                    ->collection('attachments')
                    ->label('Upload PDF Document')
                    ->acceptedFileTypes(['application/pdf'])
                    ->visible(fn (Get $get) => $get('lesson_type') === LessonType::PDF->value)
                    ->required(fn (Get $get) => $get('lesson_type') === LessonType::PDF->value)
                    ->columnSpanFull(),

                // د. محرر النصوص للمقالات
                RichEditor::make('content')
                    ->label('Lesson Content')
                    ->visible(fn (Get $get) => $get('lesson_type') === LessonType::TEXT->value)
                    ->required(fn (Get $get) => $get('lesson_type') === LessonType::TEXT->value)
                    ->columnSpanFull(),

                // 5. الإعدادات الإضافية
                TextInput::make('duration_in_minutes')
                    ->numeric()
                    ->label('Duration (Minutes)')
                    ->helperText('Estimated time to complete this lesson.'),

                TextInput::make('order')
                    ->numeric()
                    ->default(0),

                Toggle::make('is_free_preview')
                    ->label('Free Preview')
                    ->helperText('Can unregistered users watch this?')
                    ->default(false),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('section.course.title')
                    ->label('Course')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('section.title')
                    ->label('Section')
                    ->sortable()
                    ->searchable()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->description(fn (Lesson $record): string => Str::limit($record->slug, 30)),

                Tables\Columns\TextColumn::make('lesson_type')
                    ->badge(),

                Tables\Columns\IconColumn::make('is_free_preview')
                    ->boolean()
                    ->label('Preview'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Status'),

                Tables\Columns\TextColumn::make('order')
                    ->sortable()
                    ->badge()
                    ->color('gray'),
            ])
            ->filters([
                // فلتر ذكي للبحث بالكورس
                Tables\Filters\SelectFilter::make('course_id')
                    ->label('Filter by Course')
                    ->options(Course::where('instructor_id', Auth::id())->pluck('title', 'id'))
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('section', fn($q) => $q->where('course_id', $data['value']));
                        }
                    }),
                
                // فلتر للبحث بالوحدة
                Tables\Filters\SelectFilter::make('section_id')
                    ->label('Filter by Section')
                    ->relationship(
                        name: 'section',
                        titleAttribute: 'title',
                        modifyQueryUsing: fn (Builder $query) => $query->whereHas('course', fn($q) => $q->where('instructor_id', Auth::id()))
                    )
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalWidth('2xl') // عرض أوسع ليناسب محرر النصوص والفيديو
                    ->mutateRecordDataUsing(function (array $data): array {
                        // عند التعديل، نحتاج لتعبئة حقل الكورس الوهمي لكي تعمل قائمة الوحدات المنسدلة بشكل صحيح
                        $section = Section::find($data['section_id']);
                        $data['course_id'] = $section ? $section->course_id : null;
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('order') // تفعيل السحب والإفلات لترتيب الدروس
            ->defaultSort('section_id', 'asc')
            ->defaultSort('order', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageLessons::route('/'),
        ];
    }
}