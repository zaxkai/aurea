<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public $photo;

    public bool $showEditModal = false;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
    }

    /**
     * Get streak days for the authenticated user.
     */
    public function getStreakProperty(): int
    {
        return Auth::user()->current_streak ?? 0;
    }

    /**
     * Get total journal count.
     */
    public function getJournalCountProperty(): int
    {
        return Auth::user()->journals()->count();
    }

    /**
     * Get tree growth percentage.
     */
    public function getTreeProgressProperty(): int
    {
        $tree = Auth::user()->tree;

        return $tree ? $tree->growth_percentage : 0;
    }

    /**
     * Check if user is premium.
     */
    public function getIsPremiumProperty(): bool
    {
        return Auth::user()->isPremium();
    }

    /**
     * Get the avatar URL.
     */
    public function getAvatarUrlProperty(): string
    {
        return Auth::user()->avatar_url;
    }

    /**
     * Compute achievements for this user.
     *
     * @return array<int, array{key: string, label: string, icon: string, unlocked: bool, date: ?string}>
     */
    public function getAchievementsProperty(): array
    {
        $user = Auth::user();
        $firstJournal = $user->journals()->oldest()->first();
        $hasUsedAi = $user->chatSessions()->exists();
        $tree = $user->tree;
        $treeIsGrowing = $tree && $tree->growth_percentage > 0;

        return [
            [
                'key' => 'first_journal',
                'label' => 'First Journal',
                'icon' => asset('images/profile/journal-profile.png'),
                'unlocked' => $firstJournal !== null,
                'date' => $firstJournal?->created_at?->format('d M Y'),
            ],
            [
                'key' => 'growing',
                'label' => 'Growing',
                'icon' => asset('images/profile/tree-progress-profile.png'),
                'unlocked' => $treeIsGrowing,
                'date' => $treeIsGrowing ? ($tree->updated_at?->format('d M Y')) : null,
            ],
            [
                'key' => 'streak_3',
                'label' => '3 Days',
                'icon' => asset('images/profile/streek-profile.png'),
                'unlocked' => ($user->current_streak ?? 0) >= 3,
                'date' => ($user->current_streak ?? 0) >= 3 ? now()->format('d M Y') : null,
            ],
            [
                'key' => 'open_mind',
                'label' => 'Open Mind',
                'icon' => asset('images/profile/open-mind-profile.png'),
                'unlocked' => $hasUsedAi && $firstJournal !== null,
                'date' => $hasUsedAi && $firstJournal ? $firstJournal->created_at?->format('d M Y') : null,
            ],
        ];
    }

    /**
     * Open the edit profile modal.
     */
    public function openEditModal(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->photo = null;
        $this->showEditModal = true;
    }

    /**
     * Close the edit profile modal.
     */
    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->resetValidation();
        $this->photo = null;
    }

    /**
     * Save updated profile information.
     */
    public function saveProfile(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($this->photo) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $path = $this->photo->store('avatars', 'public');
            $user->avatar = $path;
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->showEditModal = false;
        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Remove the avatar photo.
     */
    public function removePhoto(): void
    {
        $user = Auth::user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->avatar = null;
            $user->save();
        }
    }
}; ?>

<div class="max-w-[1100px] mx-auto space-y-8 pt-2">
    {{-- Page Title --}}
    <h1 class="text-3xl font-extrabold text-navy tracking-[-0.04em]">Profile</h1>

    {{-- Profile Card --}}
    <div class="rounded-[28px] bg-[#e0e8ff]/50 p-6 sm:p-8 flex flex-col sm:flex-row items-center sm:items-start gap-6">
        {{-- Avatar --}}
        <div class="relative group shrink-0">
            <div class="w-28 h-28 sm:w-36 sm:h-36 rounded-full overflow-hidden border-4 border-white shadow-lg">
                <img src="{{ $this->avatarUrl }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
            </div>
        </div>

        {{-- User Info --}}
        <div class="flex flex-col items-center sm:items-start gap-2 min-w-0 flex-1">
            <div class="flex items-center gap-3 flex-wrap justify-center sm:justify-start">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-navy tracking-tight">{{ auth()->user()->name }}</h2>
                @if ($this->isPremium)
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-[#F6BE40] text-navy text-[10px] sm:text-xs font-black tracking-wide shadow-sm">
                        PREMIUM
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </span>
                @endif
            </div>
            <span class="text-sm text-gray-500 font-medium">Student</span>
            <button wire:click="openEditModal"
                    class="mt-1 inline-flex items-center gap-2 rounded-full bg-navy px-5 py-2.5 text-sm font-semibold text-white shadow-md transition hover:bg-navy/90 active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Profile
            </button>
        </div>
    </div>

    {{-- Your Progress --}}
    <div>
        <h2 class="text-xl font-extrabold text-navy mb-4">Your Progress</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            {{-- Streak --}}
            <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-sm border border-gray-100/60">
                <img src="{{ asset('images/profile/streek-profile.png') }}" alt="Streak" class="w-12 h-12 shrink-0">
                <div>
                    <div class="text-2xl font-extrabold text-navy">{{ $this->streak }} Days</div>
                    <div class="text-xs font-semibold text-gray-500">Streak</div>
                    <div class="text-[11px] text-gray-400 mt-0.5">
                        @if ($this->streak >= 7)
                            You're doing great!
                        @elseif ($this->streak >= 3)
                            Keep the momentum!
                        @else
                            Start your journey!
                        @endif
                    </div>
                </div>
            </div>

            {{-- Journals --}}
            <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-sm border border-gray-100/60">
                <img src="{{ asset('images/profile/journal-profile.png') }}" alt="Journals" class="w-12 h-12 shrink-0">
                <div>
                    <div class="text-2xl font-extrabold text-navy">{{ $this->journalCount }}</div>
                    <div class="text-xs font-semibold text-gray-500">Journals</div>
                    <div class="text-[11px] text-gray-400 mt-0.5">Keep sharing your thoughts</div>
                </div>
            </div>

            {{-- Tree Progress --}}
            <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-sm border border-gray-100/60">
                <img src="{{ asset('images/profile/tree-progress-profile.png') }}" alt="Tree Progress" class="w-12 h-12 shrink-0">
                <div>
                    <div class="text-2xl font-extrabold text-navy">{{ $this->treeProgress }}%</div>
                    <div class="text-xs font-semibold text-gray-500">Tree Progress</div>
                    <div class="text-[11px] text-gray-400 mt-0.5">
                        @if ($this->treeProgress >= 80)
                            Almost there!
                        @elseif ($this->treeProgress >= 50)
                            Keep nurturing!
                        @else
                            Grow a little more
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Your Achievements --}}
    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-extrabold text-navy">Your Achievements</h2>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-4">
            @foreach ($this->achievements as $achievement)
                <div class="flex flex-col items-center gap-2 rounded-2xl bg-white p-5 shadow-sm border border-gray-100/60 text-center transition {{ $achievement['unlocked'] ? '' : 'opacity-50 grayscale' }}">
                    <div class="w-14 h-14 rounded-full flex items-center justify-center {{ $achievement['unlocked'] ? 'bg-white' : 'bg-gray-100' }}">
                        @if ($achievement['unlocked'])
                            <img src="{{ $achievement['icon'] }}" alt="{{ $achievement['label'] }}" class="w-12 h-12">
                        @else
                            <img src="{{ asset('images/profile/logo-kunci.png') }}" alt="Locked" class="w-12 h-12">
                        @endif
                    </div>
                    <div class="text-xs font-bold text-navy">{{ $achievement['label'] }}</div>
                    <div class="text-[10px] text-gray-400">
                        @if ($achievement['unlocked'] && $achievement['date'])
                            {{ $achievement['date'] }}
                        @elseif (!$achievement['unlocked'])
                            Keep going
                        @endif
                    </div>
                </div>
            @endforeach

            {{-- Placeholder for future achievements --}}
            <div class="flex flex-col items-center gap-2 rounded-2xl bg-white p-5 shadow-sm border border-gray-100/60 border-dashed text-center opacity-50">
                <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center">
                    <img src="{{ asset('images/profile/logo-kunci.png') }}" alt="Locked" class="w-12 h-12">
                </div>
                <div class="text-xs font-bold text-gray-400">Achieve more!</div>
                <div class="text-[10px] text-gray-400">Keep going</div>
            </div>
        </div>
    </div>

    {{-- Edit Profile Modal --}}
    @if ($showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-navy/50 p-4 backdrop-blur-sm" wire:click.self="closeEditModal">
            <section role="dialog" aria-modal="true" aria-labelledby="edit-profile-title"
                     class="my-auto w-full max-w-md rounded-2xl border border-gray-100 bg-white p-5 shadow-2xl sm:p-7">
                <div class="flex items-start justify-between gap-4 mb-6">
                    <div>
                        <h2 id="edit-profile-title" class="text-lg font-bold text-navy">Edit Profile</h2>
                        <p class="mt-1 text-xs text-gray-500">Update your name and profile photo.</p>
                    </div>
                    <button type="button" wire:click="closeEditModal" aria-label="Close"
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-gray-500 transition hover:bg-gray-100 hover:text-navy">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" stroke-linecap="round" stroke-width="2" /></svg>
                    </button>
                </div>

                <form wire:submit="saveProfile" class="space-y-5">
                    {{-- Avatar Preview & Upload --}}
                    <div class="flex flex-col items-center gap-3">
                        <div class="relative">
                            <div class="w-24 h-24 rounded-full overflow-hidden border-4 border-gray-100 shadow-sm">
                                @if ($photo)
                                    <img src="{{ $photo->temporaryUrl() }}" alt="Preview" class="w-full h-full object-cover">
                                @else
                                    <img src="{{ $this->avatarUrl }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <label for="photo-upload"
                                   class="cursor-pointer inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-4 py-2 text-xs font-semibold text-navy transition hover:bg-gray-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                Change Photo
                            </label>
                            <input id="photo-upload" type="file" wire:model="photo" accept="image/*" class="hidden">

                            @if (auth()->user()->avatar)
                                <button type="button" wire:click="removePhoto"
                                        class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-4 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-100">
                                    Remove
                                </button>
                            @endif
                        </div>

                        @error('photo')
                            <p class="text-xs text-rose-600">{{ $message }}</p>
                        @enderror

                        <div wire:loading wire:target="photo" class="text-xs text-gray-400">Uploading...</div>
                    </div>

                    {{-- Name --}}
                    <div>
                        <label for="profile-name" class="mb-1.5 block text-xs font-semibold text-navy">Name</label>
                        <input id="profile-name" type="text" wire:model="name" required
                               class="w-full rounded-xl border-gray-200 text-sm text-navy placeholder:text-gray-400 focus:border-aurea focus:ring-aurea">
                        @error('name')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="profile-email" class="mb-1.5 block text-xs font-semibold text-navy">Email</label>
                        <input id="profile-email" type="email" wire:model="email" required
                               class="w-full rounded-xl border-gray-200 text-sm text-navy placeholder:text-gray-400 focus:border-aurea focus:ring-aurea">
                        @error('email')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Actions --}}
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="closeEditModal"
                                class="min-h-10 rounded-xl px-4 text-sm font-semibold text-gray-500 transition hover:bg-gray-100">
                            Cancel
                        </button>
                        <button type="submit"
                                class="min-h-10 rounded-xl bg-navy px-5 text-sm font-semibold text-white transition hover:bg-navy/90">
                            Save Changes
                        </button>
                    </div>
                </form>
            </section>
        </div>
    @endif
</div>
