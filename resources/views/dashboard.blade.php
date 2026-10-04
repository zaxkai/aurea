<x-app-layout>
    @if (auth()->user()?->isTeacher())
        <livewire:teacher.teacher-dashboard />
    @else
        <livewire:dashboard />
    @endif
</x-app-layout>
