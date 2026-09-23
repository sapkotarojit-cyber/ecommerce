<?php

namespace App\Filament\Resources\ReturnRequests\Schemas;

use App\Models\ReturnRequest;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class ReturnRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | CUSTOMER INFORMATION
                |--------------------------------------------------------------------------
                */

                Section::make('Customer Information')
                    ->schema([
                        Placeholder::make('customer_name')
                            ->label('Customer Name')
                            ->content(function (?ReturnRequest $record) {
                                return $record?->user?->name ?? 'N/A';
                            }),

                        Placeholder::make('customer_email')
                            ->label('Customer Email')
                            ->content(function (?ReturnRequest $record) {
                                return $record?->user?->email ?? 'N/A';
                            }),

                        Placeholder::make('customer_phone')
                            ->label('Customer Phone')
                            ->content(function (?ReturnRequest $record) {
                                return $record?->user?->contact_number
                                    ?? $record?->user?->phone
                                    ?? 'N/A';
                            }),
                    ])
                    ->columns(3),

                /*
                |--------------------------------------------------------------------------
                | ORDER INFORMATION
                |--------------------------------------------------------------------------
                */

                Section::make('Order Information')
                    ->schema([
                        Placeholder::make('tracking_number')
                            ->label('Tracking Number')
                            ->content(function (?ReturnRequest $record) {
                                return $record?->order?->tracking_number ?? 'N/A';
                            }),

                        Placeholder::make('order_total')
                            ->label('Order Total')
                            ->content(function (?ReturnRequest $record) {
                                if (!$record?->order) {
                                    return 'N/A';
                                }

                                return 'NPR ' . number_format(
                                    $record->order->total_amount,
                                    2
                                );
                            }),

                        Placeholder::make('payment_method')
                            ->label('Payment Method')
                            ->content(function (?ReturnRequest $record) {
                                return ucfirst(
                                    $record?->order?->payment_method ?? 'N/A'
                                );
                            }),

                        Placeholder::make('payment_status')
                            ->label('Payment Status')
                            ->content(function (?ReturnRequest $record) {
                                return ucfirst(
                                    $record?->order?->payment_status ?? 'N/A'
                                );
                            }),

                        Placeholder::make('vendor')
                            ->label('Vendor')
                            ->content(function (?ReturnRequest $record) {
                                return $record?->dokan?->company_name ?? 'N/A';
                            }),

                        Placeholder::make('order_status')
                            ->label('Order Status')
                            ->content(function (?ReturnRequest $record) {
                                return ucfirst(
                                    $record?->order?->order_status ?? 'N/A'
                                );
                            }),
                    ])
                    ->columns(3),

                /*
                |--------------------------------------------------------------------------
                | SHIPPING INFORMATION
                |--------------------------------------------------------------------------
                */

                Section::make('Shipping Information')
                    ->schema([
                        Placeholder::make('shipping_address')
                            ->label('Shipping Address')
                            ->content(function (?ReturnRequest $record) {
                                $address = $record?->order?->shipping_address;

                                if (!$address) {
                                    return 'N/A';
                                }

                                $parts = array_filter([
                                    $address->name ?? null,
                                    $address->address ?? null,
                                    $address->city ?? null,
                                    $address->state ?? null,
                                    $address->phone ?? null,
                                ]);

                                return implode(', ', $parts) ?: 'N/A';
                            }),
                    ]),

                /*
                |--------------------------------------------------------------------------
                | PRODUCTS
                |--------------------------------------------------------------------------
                */

                Section::make('Products to be Returned')
                    ->schema([
                        Placeholder::make('products')
                            ->label('')
                            ->content(function (?ReturnRequest $record) {

                                if (!$record?->order) {
                                    return new HtmlString(
                                        '<p class="text-gray-500">No order found.</p>'
                                    );
                                }

                                $items = $record->order->order_items;

                                if ($items->isEmpty()) {
                                    return new HtmlString(
                                        '<p class="text-gray-500">No products found for this order.</p>'
                                    );
                                }

                                $html = '
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-sm">
                                            <thead>
                                                <tr class="border-b">
                                                    <th class="text-left p-3">Product</th>
                                                    <th class="text-left p-3">Variant</th>
                                                    <th class="text-center p-3">Quantity</th>
                                                    <th class="text-right p-3">Price</th>
                                                    <th class="text-right p-3">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                ';

                                foreach ($items as $item) {

                                    $product = $item->product;
                                    $variant = $item->varient;

                                    $productName = e(
                                        $product?->title ?? 'Unknown Product'
                                    );

                                    $variantName = e(
                                        $variant?->title ?? 'Default'
                                    );

                                    $quantity = (int) ($item->qty ?? 0);

                                    $price = (float) ($item->amount ?? 0);

                                    $total = $price * $quantity;

                                    /*
                                     * Product image
                                     */
                                    $imageUrl = null;

                                    if ($variant?->images && is_array($variant->images)) {
                                        $imagePath = $variant->images[0] ?? null;

                                        if ($imagePath) {
                                            $imageUrl = asset('storage/' . $imagePath);
                                        }
                                    }

                                    $imageHtml = $imageUrl
                                        ? '<img src="' . e($imageUrl) . '"
                                             class="w-16 h-16 rounded-lg object-cover border"
                                             alt="' . $productName . '">'
                                        : '<div class="w-16 h-16 rounded-lg border flex items-center justify-center text-gray-400">
                                               No Image
                                           </div>';

                                    $html .= '
                                        <tr class="border-b">
                                            <td class="p-3">
                                                <div class="flex items-center gap-3">
                                                    ' . $imageHtml . '

                                                    <div>
                                                        <div class="font-semibold">
                                                            ' . $productName . '
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="p-3">
                                                ' . $variantName . '
                                            </td>

                                            <td class="p-3 text-center">
                                                ' . $quantity . '
                                            </td>

                                            <td class="p-3 text-right">
                                                NPR ' . number_format($price, 2) . '
                                            </td>

                                            <td class="p-3 text-right font-semibold">
                                                NPR ' . number_format($total, 2) . '
                                            </td>
                                        </tr>
                                    ';
                                }

                                $html .= '
                                            </tbody>
                                        </table>
                                    </div>
                                ';

                                return new HtmlString($html);
                            }),
                    ]),

                /*
                |--------------------------------------------------------------------------
                | RETURN REQUEST
                |--------------------------------------------------------------------------
                */

                Section::make('Return Request')
                    ->schema([
                        Placeholder::make('return_reason')
                            ->label('Customer Return Reason')
                            ->content(function (?ReturnRequest $record) {
                                return $record?->reason ?? 'No reason provided.';
                            }),

                        Select::make('status')
                            ->label('Return Status')
                            ->options([
                                'requested' => 'Requested',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'completed' => 'Completed',
                            ])
                            ->required(),

                        Select::make('refund_status')
                            ->label('Refund Status')
                            ->options([
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'completed' => 'Completed',
                                'failed' => 'Failed',
                            ])
                            ->required(),

                        TextInput::make('refund_amount')
                            ->label('Refund Amount')
                            ->numeric()
                            ->prefix('NPR'),

                        Textarea::make('admin_note')
                            ->label('Admin Note')
                            ->rows(4)
                            ->placeholder(
                                'Add notes about this return/refund...'
                            ),
                    ])
                    ->columns(2),
            ]);
    }
}