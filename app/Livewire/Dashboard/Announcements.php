<?php

namespace App\Livewire\Dashboard;

use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Announcements extends Component
{
    public bool $showLoginAnnouncementModal = false;

    public function mount(): void
    {
        if (session()->get('announcements_login_popup_dismissed')) {
            return;
        }

        $user = Auth::user();
        if ($user) {
            $user->loadMissing(['employee', 'roles']);
        }

        if (Announcement::visibleForUser($user)->exists()) {
            $this->showLoginAnnouncementModal = true;
        }
    }

    public function updatedShowLoginAnnouncementModal(mixed $value): void
    {
        if (! $value) {
            session()->put('announcements_login_popup_dismissed', true);
        }
    }

    public function dismissLoginAnnouncementPopup(): void
    {
        session()->put('announcements_login_popup_dismissed', true);
        $this->showLoginAnnouncementModal = false;
    }

    public function render()
    {
        $user = Auth::user();
        if ($user) {
            $user->loadMissing(['employee', 'roles']);
        }

        $items = Announcement::visibleForUser($user)
            ->with(['departments', 'roles', 'groups'])
            ->get();

        return view('livewire.dashboard.announcements', [
            'items' => $items,
        ]);
    }
}
