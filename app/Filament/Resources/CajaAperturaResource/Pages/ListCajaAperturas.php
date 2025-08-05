<?php

namespace App\Filament\Resources\CajaAperturaResource\Pages;

use App\Filament\Resources\CajaAperturaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListCajaAperturas extends ListRecords
{
    protected static string $resource = CajaAperturaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    protected function getTableQuery(): Builder
    {
        $currentUser = Auth::user();
        $currentUserId = $currentUser->id;
        

        $isRoot = $currentUser->email === 'root@example.com'; 
        
        
        
        if ($isRoot) {
            return parent::getTableQuery()->orderBy('created_at', 'desc');
        } else {

            return parent::getTableQuery()
                ->where('user_id', $currentUserId)
                ->orderBy('created_at', 'desc');
        }
    }
}