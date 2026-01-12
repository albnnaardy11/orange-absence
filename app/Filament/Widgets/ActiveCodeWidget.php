<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\VerificationCode;
use Illuminate\Support\Facades\Auth;

use Livewire\Attributes\On;

class ActiveCodeWidget extends Widget
{
    #[On('refresh-active-codes')]
    public function refresh(): void
    {
    }
    
    protected static ?int $sort = -3;
    protected int | string | array $columnSpan = 'half';
    protected string $view = 'filament.widgets.active-code-widget';

    public static function canView(): bool
    {
        // Visible to users who have divisions (Secretary/Admin)
        return Auth::check() && Auth::user()->hasAnyRole(['super_admin', 'secretary']);
    }

    protected function getViewData(): array
    {
        $user = Auth::user();
        if (!$user) {
            return ['codes' => collect()];
        }
        
        $divisionIds = $user->divisions->pluck('id');
        
        $codes = VerificationCode::whereIn('division_id', $divisionIds)
            ->where('expired_at', '>', now())
            ->with('division')
            ->get();
            
        return [
            'codes' => $codes,
        ];
    }
}
