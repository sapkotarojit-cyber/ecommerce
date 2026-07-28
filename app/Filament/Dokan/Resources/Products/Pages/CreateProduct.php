<?php

namespace App\Filament\Dokan\Resources\Products\Pages;

use App\Filament\Dokan\Resources\Products\ProductResource;
use App\Models\Dokan;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    /**
     * This method is called BEFORE the record is saved
     * Use this to modify the data array
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Get the dokan for the current user
        $dokan = $this->getUserDokan();

        if ($dokan) {
            // ✅ Set dokan_id
            $data['dokan_id'] = $dokan->id;
        }

        return $data;
    }

    /**
     * This method handles the actual creation
     * Override this to ensure dokan_id is set
     */
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Get the dokan again (just to be safe)
        $dokan = $this->getUserDokan();

        if (!$dokan) {
            \Filament\Notifications\Notification::make()
                ->title('Vendor Account Required')
                ->body('You need to register as a vendor to create products.')
                ->danger()
                ->send();

            $this->redirect('/dokan/registration');
            return $this->getModel()::make($data);
        }

        // ✅ Set dokan_id in the data array
        $data['dokan_id'] = $dokan->id;

        // Create the product with the data
        return $this->getModel()::create($data);
    }

    protected function getUserDokan()
    {
        // Check if user is logged in as dokan
        if (Auth::guard('dokan')->check()) {
            return Auth::guard('dokan')->user();
        }

        // Check if regular user has a dokan
        if (Auth::check()) {
            return Dokan::where('user_id', Auth::id())->first();
        }

        return null;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
