<?php

namespace App\Filament\Dokan\Resources\Orders\Pages;

use App\Filament\Dokan\Resources\Orders\OrderResource;
use App\Mail\OrderStatusChangedMail;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected string $oldOrderStatus = '';

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->oldOrderStatus = $this->record->status;

        return $data;
    }

    protected function afterSave(): void
    {
        $newStatus = $this->record->fresh()->status;

        /*
         * Only send an email when the order status
         * actually changed.
         */
        if (
            $this->oldOrderStatus !== $newStatus &&
            in_array($newStatus, [
                'processing',
                'completed',
                'cancelled',
            ])
        ) {
            $order = $this->record->fresh();

            $order->load([
                'user',
                'dokan',
                'shippingAddress',
                'orderItems.product',
                'orderItems.varient',
            ]);

            if ($order->user && $order->user->email) {
                Mail::to($order->user->email)
                    ->send(
                        new OrderStatusChangedMail(
                            $order,
                            $this->oldOrderStatus,
                            $newStatus
                        )
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