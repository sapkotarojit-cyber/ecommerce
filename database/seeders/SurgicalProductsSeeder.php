<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVarient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SurgicalProductsSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->error('Sample products can only be seeded locally.');
            return;
        }

        $vendor = DB::table('dokans')
            ->where('id', 1)
            ->where('status', 'approved')
            ->first();

        if (! $vendor) {
            $this->command?->error('Approved vendor ID 1 was not found.');
            return;
        }

        $categoryNames = [
            'Surgical Instruments',
            'Medical Equipment',
            'Personal Protective Equipment',
            'Wound Care',
            'Diagnostic Supplies',
        ];

        $categories = [];

        foreach ($categoryNames as $name) {
            $slug = Str::slug($name);

            $categories[$name] = Category::firstOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'is_active' => true]
            );
        }

        $products = [
            [
                'title' => 'Stainless Steel Surgical Scissors',
                'category' => 'Surgical Instruments',
                'description' => 'Reusable stainless steel surgical scissors for clinical procedures.',
                'variant' => 'Standard 14 cm',
                'price' => 850,
                'qty' => 100,
            ],
            [
                'title' => 'Stainless Steel Surgical Forceps',
                'category' => 'Surgical Instruments',
                'description' => 'Precision stainless steel forceps for medical and surgical use.',
                'variant' => 'Standard',
                'price' => 650,
                'qty' => 100,
            ],
            [
                'title' => 'Digital Blood Pressure Monitor',
                'category' => 'Medical Equipment',
                'description' => 'Digital blood pressure monitoring device for routine measurements.',
                'variant' => 'Upper Arm',
                'price' => 2800,
                'qty' => 30,
            ],
            [
                'title' => 'Digital Thermometer',
                'category' => 'Diagnostic Supplies',
                'description' => 'Digital thermometer for routine body temperature measurement.',
                'variant' => 'Standard',
                'price' => 350,
                'qty' => 100,
            ],
            [
                'title' => 'Stethoscope',
                'category' => 'Medical Equipment',
                'description' => 'General-purpose stethoscope for clinical examination.',
                'variant' => 'Adult',
                'price' => 1200,
                'qty' => 50,
            ],
            [
                'title' => 'Disposable Surgical Gloves',
                'category' => 'Personal Protective Equipment',
                'description' => 'Disposable examination gloves for healthcare settings.',
                'variant' => 'Box of 100',
                'price' => 950,
                'qty' => 100,
            ],
            [
                'title' => 'Medical Face Masks',
                'category' => 'Personal Protective Equipment',
                'description' => 'Disposable medical face masks for routine clinical use.',
                'variant' => 'Pack of 50',
                'price' => 400,
                'qty' => 100,
            ],
            [
                'title' => 'Sterile Gauze Swabs',
                'category' => 'Wound Care',
                'description' => 'Sterile gauze swabs for dressing and wound care.',
                'variant' => 'Pack of 10',
                'price' => 180,
                'qty' => 100,
            ],
            [
                'title' => 'Adhesive Medical Bandages',
                'category' => 'Wound Care',
                'description' => 'Adhesive bandages for basic wound protection.',
                'variant' => 'Pack of 100',
                'price' => 250,
                'qty' => 100,
            ],
            [
                'title' => 'Pulse Oximeter',
                'category' => 'Diagnostic Supplies',
                'description' => 'Portable fingertip device for measuring oxygen saturation and pulse rate.',
                'variant' => 'Fingertip',
                'price' => 1600,
                'qty' => 40,
            ],
        ];

        DB::transaction(function () use ($products, $categories, $vendor): void {
            foreach ($products as $item) {
                $product = Product::firstOrCreate(
                    [
                        'dokan_id' => $vendor->id,
                        'title' => $item['title'],
                    ],
                    [
                        'category_id' => $categories[$item['category']]->id,
                        'category' => $item['category'],
                        'description' => $item['description'],
                    ]
                );

                ProductVarient::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'title' => $item['variant'],
                    ],
                    [
                        'price' => $item['price'],
                        'discount' => 0,
                        'qty' => $item['qty'],
                        'images' => [],
                    ]
                );
            }
        });

        $this->command?->info('Surgical sample products and variants created successfully.');
        $this->command?->info('Vendor: ' . $vendor->company_name);
        $this->command?->info('Products processed: ' . count($products));
    }
}
