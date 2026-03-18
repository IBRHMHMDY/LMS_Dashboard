<div class="flex flex-col justify-center">
    <span class="text-xl font-bold tracking-tight text-gray-950 dark:text-white leading-none">
        EduPlatform </span>
    @if(auth()->check())
        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-1">
            {{ $panel }} Panel ({{ auth()->user()->name }})
        </span>
    @endif
</div>