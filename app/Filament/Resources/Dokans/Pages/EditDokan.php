<?php

namespace App\Filament\Resources\Dokans\Pages;

use App\Filament\Resources\Dokans\DokanResource;
use App\Mail\DokanRequestApproval;
use App\Mail\DokanRequestRejection;
use App\Models\Dokan;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EditDokan extends EditRecord
{
    protected static string $resource = DokanResource::class;

    protected function afterSave(): void
    {
        $dokan = $this->record;

        // =========================
        // APPROVED
        // =========================
        if (
            $dokan->wasChanged('status') &&
            $dokan->status === Dokan::STATUS_APPROVED
        ) {
            try {
                // Generate temporary password
                $temporaryPassword = Str::random(12);

                // Dokan model automatically hashes the password
                $dokan->password = $temporaryPassword;
                $dokan->save();

                // Send approval email
                Mail::to($dokan->email)->send(
                    new DokanRequestApproval(
                        [
                            'company_name' => $dokan->company_name,
                            'email' => $dokan->email,
                        ],
                        $temporaryPassword
                    )
                );

            } catch (\Exception $e) {
                Log::error(
                    'Failed to send vendor approval email: ' .
                    $e->getMessage()
                );
            }
        }

        // =========================
        // REJECTED
        // =========================
        if (
            $dokan->wasChanged('status') &&
            $dokan->status === Dokan::STATUS_REJECTED
        ) {
            try {
                Mail::to($dokan->email)->send(
                    new DokanRequestRejection(
                        $dokan,
                        $dokan->rejection_comment ?? 'No reason was provided.'
                    )
                );

            } catch (\Exception $e) {
                Log::error(
                    'Failed to send vendor rejection email: ' .
                    $e->getMessage()
                );
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}