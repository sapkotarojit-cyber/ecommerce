<?php

namespace App\Filament\Dokan\Resources\ReturnRequests\Schemas;

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

                Section::make('Customer Information')
                    ->schema([

                        Placeholder::make('customer_name')
                            ->label('Customer Name')
                            ->content(fn (?ReturnRequest $record) =>
                                $record?->user?->name ?? 'N/A'
                            ),

                        Placeholder::make('customer_email')
                            ->label('Customer Email')
                            ->content(fn (?ReturnRequest $record) =>
                                $record?->user?->email ?? 'N/A'
                            ),

                    ])
                    ->columns(2),

                Section::make('Order Information')
                    ->schema([

                        Placeholder::make('tracking_number')
                            ->label('Tracking Number')
                            ->content(fn (?ReturnRequest $record) =>
                                $record?->order?->tracking_number ?? 'N/A'
                            ),

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
                            ->content(fn (?ReturnRequest $record) =>
                                ucfirst(
                                    $record?->order?->payment_method ?? 'N/A'
                                )
                            ),

                        Placeholder::make('payment_status')
                            ->label('Payment Status')
                            ->content(fn (?ReturnRequest $record) =>
                                ucfirst(
                                    $record?->order?->payment_status ?? 'N/A'
                                )
                            ),

                    ])
                    ->columns(2),

                Section::make('Products in This Return')
                    ->schema([

                        Placeholder::make('products')
                            ->label('')
                            ->content(function (?ReturnRequest $record) {

                                if (!$record?->order) {
                                    return new HtmlString(
                                        '<p>No order found.</p>'
                                    );
                                }

                                $items = $record->order->order_items;

                                if ($items->isEmpty()) {
                                    return new HtmlString(
                                        '<p>No products found.</p>'
                                    );
                                }

                                $html = '
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-sm">
                                            <thead>
                                                <tr class="border-b">
                                                    <th class="text-left p-3">
                                                        Product
                                                    </th>
                                                    <th class="text-left p-3">
                                                        Variant
                                                    </th>
                                                    <th class="text-center p-3">
                                                        Quantity
                                                    </th>
                                                    <th class="text-right p-3">
                                                        Price
                                                    </th>
                                                    <th class="text-right p-3">
                                                        Total
                                                    </th>
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
                                     * Images are stored on ProductVarient.
                                     */
                                    $imageUrl = null;

                                    if (
                                        $variant?->images &&
                                        is_array($variant->images)
                                    ) {
                                        $imagePath =
                                            $variant->images[0] ?? null;

                                        if ($imagePath) {
                                            $imageUrl = asset(
                                                'storage/' . $imagePath
                                            );
                                        }
                                    }

                                    $imageHtml = $imageUrl

                                        ? '<img src="' .
                                            e($imageUrl) .
                                            '"
                                            class="w-16 h-16 rounded-lg
                                            object-cover border"
                                            alt="' .
                                            $productName .
                                            '">'

                                        : '<div
                                            class="w-16 h-16 rounded-lg
                                            border flex items-center
                                            justify-center text-gray-400">
                                            No Image
                                           </div>';

                                    $html .= '
                                        <tr class="border-b">

                                            <td class="p-3">
                                                <div class="flex
                                                    items-center gap-3">

                                                    ' . $imageHtml . '

                                                    <div class="font-semibold">
                                                        ' . $productName . '
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
                                                NPR ' .
                                                number_format($price, 2) .
                                                '
                                            </td>

                                            <td class="p-3 text-right
                                                font-semibold">
                                                NPR ' .
                                                number_format($total, 2) .
                                                '
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

                Section::make('Return Details')
                    ->schema([

                        Select::make('status')
                            ->label('Return Status')
                            ->options([
                                'requested' => 'Requested',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'completed' => 'Completed',
                            ])
                            ->required(),

                        Placeholder::make('refund_status')
                            ->label('Refund Status')
                            ->content(fn (?ReturnRequest $record) =>
                                ucfirst(
                                    $record?->refund_status ?? 'pending'
                                )
                            ),

                        Placeholder::make('refund_amount')
                            ->label('Refund Amount')
                            ->content(function (?ReturnRequest $record) {

                                if (!$record?->refund_amount) {
                                    return 'NPR 0.00';
                                }

                                return 'NPR ' . number_format(
                                    $record->refund_amount,
                                    2
                                );
                            }),

                        Textarea::make('admin_note')
                            ->label('Note')
                            ->rows(4)
                            ->placeholder(
                                'Add a note about this return...'
                            ),
                    ])
                    ->columns(2)
            ]);
    }
}