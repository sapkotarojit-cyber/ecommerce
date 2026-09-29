<?php

namespace App\Filament\Dokan\Resources\ReturnRequests\Pages;

use App\Filament\Dokan\Resources\ReturnRequests\ReturnRequestResource;
use App\Mail\ReturnRequestStatusChangedMail;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;

class EditReturnRequest extends EditRecord
{
    protected static string $resource = ReturnRequestResource::class;

    protected array $oldReturnRequestState = [];

    protected function beforeSave(): void
    {
        $record = $this->getRecord();

        /*
         * Save the values BEFORE Filament updates the record.
         */
        $this->oldReturnRequestState = [
            'status' => (string) ($record->status ?? ''),
            'refund_status' => (string) ($record->refund_status ?? ''),
        ];
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();

        $record->load([
            'user',
            'order',
            'dokan',
        ]);

        /*
         * -------------------------------------------------------------
         * OLD VALUES
         * -------------------------------------------------------------
         */

        $oldReturnStatus = $this->oldReturnRequestState['status'] ?? '';
        $oldRefundStatus = $this->oldReturnRequestState['refund_status'] ?? '';

        /*
         * -------------------------------------------------------------
         * NEW VALUES
         * -------------------------------------------------------------
         */

        $newReturnStatus = (string) ($record->status ?? '');
        $newRefundStatus = (string) ($record->refund_status ?? '');

        /*
         * -------------------------------------------------------------
         * CHECK WHETHER EITHER STATUS ACTUALLY CHANGED
         * -------------------------------------------------------------
         */

        $returnStatusChanged =
            $oldReturnStatus !== ''
            && $oldReturnStatus !== $newReturnStatus;

        $refundStatusChanged =
            $oldRefundStatus !== ''
            && $oldRefundStatus !== $newRefundStatus;

        /*
         * -------------------------------------------------------------
         * SEND ONE EMAIL
         *
         * Even if BOTH statuses change at the same time,
         * only ONE email is sent.
         * -------------------------------------------------------------
         */

        if (
            ($returnStatusChanged || $refundStatusChanged)
            && $record->user?->email
        ) {
            Mail::to($record->user->email)->send(
                new ReturnRequestStatusChangedMail(
                    returnRequest: $record,
                    oldReturnStatus: $oldReturnStatus,
                    newReturnStatus: $newReturnStatus,
                    oldRefundStatus: $oldRefundStatus,
                    newRefundStatus: $newRefundStatus,
                )
            );
        }
    }
}