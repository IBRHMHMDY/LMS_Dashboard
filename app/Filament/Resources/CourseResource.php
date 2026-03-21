<?php

namespace App\Filament\Resources;

use App\Enums\CourseStatus;
use App\Enums\StatusColor;
use App\Filament\Resources\CourseResource\Pages;
use App\Models\Course;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    
    public static function getNavigationGroup(): ?string
    {
        return __('Content Management');
    }

    public static function getModelLabel(): string
    {
        return __('Course');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Courses');
    }
    
    protected static ?int $navigationSort = 2;

    // منع الإدارة من إنشاء أو تعديل كورسات جديدة من هذه اللوحة
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    // الاستغناء عن form واستخدام infolist لعرض البيانات باحترافية
public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make(__('Course Overview'))
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Infolists\Components\Grid::make(3)->schema([
                            Infolists\Components\ImageEntry::make('thumbnail')
                                ->hiddenLabel()
                                ->columnSpan(1)
                                ->extraImgAttributes(['class' => 'rounded-lg shadow-sm object-cover']),

                            Infolists\Components\Group::make([
                                Infolists\Components\TextEntry::make('title')
                                    ->label(__('Course Title'))
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                    ->weight('bold'),
                                Infolists\Components\TextEntry::make('instructor.name')
                                    ->label(__('Instructor'))
                                    ->icon('heroicon-o-user')
                                    ->color('primary'),
                                Infolists\Components\TextEntry::make('category.name')
                                    ->label(__('Category'))
                                    ->icon('heroicon-o-tag')
                                    ->color('gray'),
                                Infolists\Components\TextEntry::make('status')
                                    ->label(__('Status'))
                                    ->badge(),
                                Infolists\Components\TextEntry::make('slug')
                                    ->label(__('Slug'))
                                    ->icon('heroicon-o-link')
                                    ->color('gray'),
                            ])->columnSpan(2),
                        ]),
                        
                        Infolists\Components\TextEntry::make('subtitle')
                            ->label(__('Subtitle'))
                            ->columnSpanFull()
                            ->color('gray'),
                    ]),

                Infolists\Components\Section::make(__('Pricing & Level'))
                    ->icon('heroicon-o-currency-dollar')
                    ->columns(3)
                    ->schema([
                        Infolists\Components\TextEntry::make('price')
                            ->label(__('Price (EGP)'))
                            ->money('EGP') // تم تعديلها للجنيه المصري بناء على طلباتك السابقة
                            ->weight('bold')
                            ->color('success'),
                        Infolists\Components\TextEntry::make('discount_price')
                            ->label(__('Discount Price (EGP)'))
                            ->money('EGP'),
                        Infolists\Components\TextEntry::make('level')
                            ->label(__('Level'))
                            ->badge()
                            ->color('info'),
                    ]),

                // القسم الجديد: الميديا والوصف
                Infolists\Components\Section::make(__('Detailed Content'))
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Infolists\Components\TextEntry::make('promo_video_url')
                            ->label(__('Promotional Video URL'))
                            ->url(fn ($state) => $state) // لجعل الرابط قابلاً للضغط
                            ->openUrlInNewTab()
                            ->icon('heroicon-o-video-camera')
                            ->color('primary')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('description')
                            ->label(__('Course Description'))
                            ->prose()
                            ->html() // لعرض الـ Rich Text بشكل صحيح
                            ->columnSpanFull(),
                    ]),

                // القسم الجديد: المخرجات والمتطلبات
                Infolists\Components\Section::make(__('Course Outcomes'))
                    ->icon('heroicon-o-academic-cap')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('what_you_will_learn')
                            ->label(__('What You Will Learn'))
                            ->listWithLineBreaks() // عرض المصفوفة كأسطر
                            ->bulleted() // إضافة نقاط (Bullets) بجوار كل عنصر
                            ->columnSpan(1),

                        Infolists\Components\TextEntry::make('requirements')
                            ->label(__('Requirements / Prerequisites'))
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->columnSpan(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // توجيه الضغط على السجل إلى صفحة العرض View وليس التعديل
            ->recordUrl(
                fn (Course $record): string => static::getUrl('view', ['record' => $record])
            )
            // جلب أعداد المشتركين واسم المدرب والتصنيف بكفاءة عالية
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('enrollments')->with(['instructor', 'category']))
            ->columns([
                // 1. صورة الكورس
                Tables\Columns\TextColumn::make('title')
                    ->label(__('Course Title'))
                    ->html() // السماح بعرض أكواد HTML
                    ->searchable() // البحث سيعمل على العنوان بسلاسة
                    ->sortable()
                    ->formatStateUsing(function (string $state, Course $record): string {
                        
                        // 1. معالجة رابط الصورة (الصورة الحقيقية أو صورة افتراضية)
                        $imageUrl = $record->thumbnail 
                            ? \Illuminate\Support\Facades\Storage::url($record->thumbnail) 
                            : 'https://placehold.co/100x100/f3f4f6/9ca3af?text=' . urlencode(__('Cover'));
                        
                        // 2. معالجة اسم التصنيف
                        $categoryName = $record->category?->name ?? __('Uncategorized');

                        // 3. إرجاع التصميم المدمج باستخدام Tailwind CSS
                        return "
                            <div class='flex items-center gap-3'>
                                <img src='{$imageUrl}' alt='Course Cover' 
                                     class='rounded-md border border-gray-200 dark:border-gray-700 shadow-sm' 
                                     style='width: 64px; height: 64px; min-width: 64px; min-height: 64px; object-fit: cover;'>
                                <div class='flex flex-col'>
                                    <span class='text-sm font-bold text-gray-900 dark:text-white leading-tight'>{$state}</span>
                                    <span class='text-xs text-gray-500 dark:text-gray-400 mt-1'>{$categoryName}</span>
                                </div>
                            </div>
                        ";
                    }),

                // 3. اسم المدرب
                Tables\Columns\TextColumn::make('instructor.name')
                    ->label(__('Instructor'))
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-user')
                    ->color('gray'),

                // 4. مستوى الكورس
                Tables\Columns\TextColumn::make('level')
                    ->label(__('Level'))
                    ->badge()
                    ->sortable(),

                // 5. السعر (مجاني أو بالجنيه المصري)
                Tables\Columns\TextColumn::make('price')
                    ->label(__('Price'))
                    ->badge()
                    ->color(fn ($state) => $state == 0 || is_null($state) ? StatusColor::SUCCESS->value : StatusColor::GRAY->value)
                    ->formatStateUsing(fn ($state) => $state == 0 || is_null($state) ? __('Free') : number_format($state, 2) . ' ' . __('EGP'))
                    ->sortable(),

                // 6. حالة الكورس
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge(),

                // 7. عدد الطلاب المشتركين
                Tables\Columns\TextColumn::make('enrollments_count')
                    ->label(__('Students'))
                    ->badge()
                    ->color(StatusColor::INFO->value)
                    ->icon('heroicon-m-users')
                    ->sortable(),

                // 8. تاريخ النشر (تاريخ الإنشاء)
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Published Date'))
                    ->date('d M y') // عرض التاريخ بشكل أنيق (مثال: 18 Mar 2026)
                    ->sortable()
                    ->color('gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(CourseStatus::class),
                Tables\Filters\SelectFilter::make('instructor_id')
                    ->label(__('Instructor'))
                    // استخدام دالة role الخاصة بحزمة Spatie لجلب المدربين فقط
                    ->relationship('instructor', 'name', fn (Builder $query) => $query->role('instructor'))
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                // زر العرض كما طلبت
                Tables\Actions\ViewAction::make()->label(__('View')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label(__('Delete Selected')),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'view' => Pages\ViewCourse::route('/{record}'),
        ];
    }
}