@props(['active' => ''])

<aside class="w-64 bg-white min-h-screen flex flex-col justify-between p-6">
    <div>
        <div class="flex items-center gap-2 mb-10">
            <span class="text-aurea text-2xl">💧</span>
            <span class="font-bold text-xl">aurea</span>
        </div>

        <nav class="space-y-2">
            <a href="/dashboard" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ $active === 'home' ? 'bg-navy text-white' : 'text-gray-600' }}">
                Home
            </a>
            <a href="/journal" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ $active === 'journal' ? 'bg-navy text-white' : 'text-gray-600' }}">
                Journal
            </a>
            <a href="/habit-growth-tree" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ $active === 'tree' ? 'bg-navy text-white' : 'text-gray-600' }}">
                Habit Growth Tree
            </a>
            <a href="/ai-aurea" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ $active === 'ai' ? 'bg-navy text-white' : 'text-gray-600' }}">
                AI aurea
            </a>
        </nav>
    </div>

    <a href="/settings" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ $active === 'settings' ? 'bg-navy text-white' : 'text-gray-600' }}">
        Settings
    </a>
</aside>