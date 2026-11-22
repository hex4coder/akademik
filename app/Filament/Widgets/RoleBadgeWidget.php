<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\Widget;

class RoleBadgeWidget extends Widget
{
    protected string $view = 'filament.widgets.role-badge-widget';

    protected static ?int $sort = -3;

    protected static ?bool $isLazyLoaded = false;

    /**
     * @return array<string, string>
     */
    protected function getViewData(): array
    {
        $user = auth()->user();

        $roleLabel = match (true) {
            $user instanceof User && $user->hasRole('admin') => 'Admin',
            $user instanceof User && $user->hasRole('guru') => 'Guru',
            default => 'Pengguna',
        };

        return [
            'roleLabel' => $roleLabel,
            'roleColor' => match ($roleLabel) {
                'Admin' => 'success',
                'Guru' => 'info',
                default => 'gray',
            },
        ];
    }
}
