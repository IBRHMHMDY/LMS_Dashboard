@php
    // التحقق مما إذا كان هناك صورة مرفوعة، وإلا نضع صورة افتراضية
    $imageUrl = $getState() 
        ? asset('storage/' . $getState()) 
        : 'https://ui-avatars.com/api/?name=Course&background=random&size=400';
@endphp

<div style="width: 100%; height: 200px; overflow: hidden; border-top-left-radius: 0.75rem; border-top-right-radius: 0.75rem; margin: 0; padding: 0;">
    <img 
        src="{{ $imageUrl }}" 
        alt="Course Thumbnail" 
        style="width: 100%; height: 100%; object-fit: cover; display: block;" 
    />
</div>