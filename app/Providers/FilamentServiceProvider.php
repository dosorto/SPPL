use Filament\Facades\Filament;

public function boot(): void
{
    Filament::serving(function () {
        Filament::registerStyles([
            asset('public/css/filament/custom.css'),
        ]);
    });
}
