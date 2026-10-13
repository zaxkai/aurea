<?php

namespace App\Livewire;

use App\Livewire\Actions\Logout;
use App\Models\Classroom;
use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Settings extends Component
{
    public string $name = '';

    public string $email = '';

    public bool $notifEmail = true;

    public bool $notifPush = true;

    public function mount(): void
    {
        $this->name = auth()->user()->name;
        $this->email = auth()->user()->email;
    }

    public function updateProfile(): void
    {
        $user = auth()->user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        session()->flash('saved', 'Perubahan tersimpan.');
    }

    public function deactivatePremium(): void
    {
        auth()->user()->deactivatePremium();

        session()->flash('saved', 'Akunmu kembali ke Free. Data yang sudah tersimpan tetap aman.');
    }

    public string $classCode = '';

    public ?string $classroomMessage = null;

    public ?string $classroomError = null;

    public function joinClassroom(): void
    {
        $this->classroomMessage = null;
        $this->classroomError = null;

        $code = strtoupper(trim($this->classCode));
        if (empty($code)) {
            $this->classroomError = 'Silakan masukkan kode kelas.';

            return;
        }

        $classroom = Classroom::with('teacher')->where('code', $code)->first();
        if (! $classroom) {
            $this->classroomError = 'Kode kelas tidak ditemukan. Mohon periksa kembali kode dari gurumu.';

            return;
        }

        $user = auth()->user();
        if ($user->enrolledClassrooms()->where('classroom_id', $classroom->id)->exists()) {
            $this->classroomError = 'Kamu sudah terdaftar di kelas ini.';

            return;
        }

        $user->enrolledClassrooms()->syncWithoutDetaching([$classroom->id => ['joined_at' => now()]]);

        if ($classroom->teacher) {
            $classroom->teacher->notify(new AppNotification(
                '🎓 Siswa Baru Bergabung',
                "{$user->name} baru saja bergabung ke kelas {$classroom->name}.",
                'student',
                'info',
                route('teacher.students', ['search' => $user->name])
            ));
        }

        $this->classCode = '';
        $this->classroomMessage = "Berhasil bergabung ke {$classroom->name} (Guru: {$classroom->teacher?->name}).";
    }

    public function leaveClassroom(int $classroomId): void
    {
        auth()->user()->enrolledClassrooms()->detach($classroomId);
        $this->classroomMessage = 'Kamu telah keluar dari kelas tersebut.';
        $this->classroomError = null;
    }

    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }

    public function deleteAccount(): void
    {
        $user = auth()->user();
        auth()->guard('web')->logout();
        session()->invalidate();
        session()->regenerateToken();
        $user->delete();
        $this->redirect('/', navigate: true);
    }

    public function render()
    {
        return view('livewire.settings');
    }
}
