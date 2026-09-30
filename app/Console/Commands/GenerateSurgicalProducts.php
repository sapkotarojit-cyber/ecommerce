<?php

namespace App\Console\Commands;

use App\Models\ProductVarient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateSurgicalProducts extends Command
{
    protected $signature = 'products:generate-surgical {count=100}';

    protected $description = 'Replace surgical product placeholder images with real medical images';

    public function handle(): int
    {
        $count = max(100, (int) $this->argument('count'));

        if (!extension_loaded('gd')) {
            $this->error('PHP GD extension is required.');
            $this->line('Install it with: sudo apt install php8.4-gd');
            return self::FAILURE;
        }

        $variants = ProductVarient::query()
            ->with('product')
            ->get()
            ->filter(function ($variant) {
                return $variant->product !== null;
            })
            ->take($count);

        if ($variants->isEmpty()) {
            $this->warn('No products found.');
            return self::SUCCESS;
        }

        $total = $variants->count();

        $this->info(
            "Processing {$total} surgical product images..."
        );

        Storage::disk('public')
            ->makeDirectory('product-variants');

        $success = 0;
        $realImages = 0;
        $fallbackImages = 0;

        foreach ($variants as $index => $variant) {
            $number = $index + 1;

            $name = (string) (
                $variant->product?->title
                ?? 'Medical Product'
            );

            $this->newLine();
            $this->line(
                "[{$number}/{$total}] {$name}"
            );

            /*
            |--------------------------------------------------------------------------
            | Try several real medical image searches
            |--------------------------------------------------------------------------
            */

            $image = $this->downloadMedicalImage($name);

            /*
            |--------------------------------------------------------------------------
            | If real image cannot be downloaded, create local image
            |--------------------------------------------------------------------------
            */

            if (!$image) {
                $this->warn(
                    '  Real image unavailable. Creating local medical image...'
                );

                $image = $this->createFallbackImage(
                    $name,
                    $variant->id
                );

                $fallbackImages++;
            } else {
                $this->info(
                    '  Real medical image downloaded.'
                );

                $realImages++;
            }

            /*
            |--------------------------------------------------------------------------
            | Save image
            |--------------------------------------------------------------------------
            */

            $filename =
                'medical-' .
                Str::slug($name) .
                '-' .
                $variant->id .
                '.jpg';

            $path =
                'product-variants/' .
                $filename;

            Storage::disk('public')->put(
                $path,
                $image
            );

            /*
            |--------------------------------------------------------------------------
            | Update product variant
            |--------------------------------------------------------------------------
            */

            $variant->update([
                'images' => [$path],
            ]);

            $success++;

            $this->info(
                "  Saved: {$path}"
            );
        }

        $this->newLine();
        $this->newLine();

        $this->info(
            "Finished processing {$success}/{$total} products."
        );

        $this->info(
            "Real images: {$realImages}"
        );

        $this->info(
            "Local fallback images: {$fallbackImages}"
        );

        return self::SUCCESS;
    }

    /*
    |--------------------------------------------------------------------------
    | Download real medical image
    |--------------------------------------------------------------------------
    */

    private function downloadMedicalImage(
        string $productName
    ): ?string {
        $searches = $this->searchTerms(
            $productName
        );

        foreach ($searches as $search) {
            for ($attempt = 1; $attempt <= 2; $attempt++) {
                try {
                    $response = Http::timeout(20)
                        ->retry(
                            2,
                            1000,
                            throw: false
                        )
                        ->withHeaders([
                            'User-Agent' =>
                                'EmpireInnovationMedicalStore/1.0',
                        ])
                        ->get(
                            'https://commons.wikimedia.org/w/api.php',
                            [
                                'action' =>
                                    'query',

                                'generator' =>
                                    'search',

                                'gsrsearch' =>
                                    $search,

                                'gsrnamespace' =>
                                    6,

                                'gsrlimit' =>
                                    10,

                                'prop' =>
                                    'imageinfo',

                                'iiprop' =>
                                    'url|mime',

                                'iiurlwidth' =>
                                    1000,

                                'format' =>
                                    'json',
                            ]
                        );

                    if (!$response->successful()) {
                        continue;
                    }

                    $pages =
                        $response->json(
                            'query.pages'
                        );

                    if (!is_array($pages)) {
                        continue;
                    }

                    foreach ($pages as $page) {
                        $info =
                            $page['imageinfo'][0]
                            ?? null;

                        if (!is_array($info)) {
                            continue;
                        }

                        $url =
                            $info['thumburl']
                            ?? $info['url']
                            ?? null;

                        $mime =
                            strtolower(
                                (string) (
                                    $info['mime']
                                    ?? ''
                                )
                            );

                        if (
                            !is_string($url) ||
                            $url === ''
                        ) {
                            continue;
                        }

                        if (
                            !in_array(
                                $mime,
                                [
                                    'image/jpeg',
                                    'image/jpg',
                                    'image/png',
                                    'image/webp',
                                ],
                                true
                            )
                        ) {
                            continue;
                        }

                        $image =
                            Http::timeout(30)
                                ->retry(
                                    2,
                                    1000,
                                    throw: false
                                )
                                ->withHeaders([
                                    'User-Agent' =>
                                        'EmpireInnovationMedicalStore/1.0',
                                ])
                                ->get($url);

                        if (
                            $image->successful() &&
                            strlen(
                                $image->body()
                            ) > 5000
                        ) {
                            return $image->body();
                        }
                    }
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Search terms
    |--------------------------------------------------------------------------
    */

    private function searchTerms(
        string $name
    ): array {
        $name = strtolower(
            trim($name)
        );

        $terms = [];

        $specialTerms = [
            'scissors' =>
                'surgical scissors medical instrument',

            'forceps' =>
                'surgical forceps medical instrument',

            'needle holder' =>
                'needle holder surgical instrument',

            'scalpel' =>
                'scalpel surgical instrument',

            'retractor' =>
                'surgical retractor medical instrument',

            'curette' =>
                'surgical curette medical instrument',

            'probe' =>
                'surgical probe medical instrument',

            'suction' =>
                'surgical suction medical equipment',

            'gloves' =>
                'medical surgical gloves',

            'gown' =>
                'surgical gown medical',

            'mask' =>
                'surgical mask medical',

            'syringe' =>
                'medical syringe',

            'needle' =>
                'medical needle',

            'cannula' =>
                'IV cannula medical',

            'gauze' =>
                'medical gauze dressing',

            'bandage' =>
                'medical bandage',

            'tape' =>
                'medical surgical tape',

            'thermometer' =>
                'medical thermometer',

            'oximeter' =>
                'pulse oximeter medical',

            'blood pressure' =>
                'blood pressure monitor medical',

            'stethoscope' =>
                'medical stethoscope',

            'otoscope' =>
                'medical otoscope',

            'glucometer' =>
                'medical glucometer',

            'catheter' =>
                'medical catheter',

            'tube' =>
                'medical tube healthcare',

            'oxygen' =>
                'oxygen medical equipment',

            'nebulizer' =>
                'medical nebulizer',

            'autoclave' =>
                'medical autoclave sterilization',

            'sterilization' =>
                'medical sterilization equipment',

            'kidney tray' =>
                'medical kidney tray',

            'suture' =>
                'surgical suture medical',

            'stapler' =>
                'surgical stapler medical',

            'tourniquet' =>
                'medical tourniquet',

            'splint' =>
                'medical splint',

            'cervical collar' =>
                'medical cervical collar',

            'first aid' =>
                'medical first aid kit',
        ];

        foreach (
            $specialTerms as $keyword => $term
        ) {
            if (
                Str::contains(
                    $name,
                    $keyword
                )
            ) {
                $terms[] = $term;
                break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Generic searches
        |--------------------------------------------------------------------------
        */

        $terms[] =
            $name .
            ' medical equipment';

        $terms[] =
            $name .
            ' surgical instrument';

        $terms[] =
            $name .
            ' healthcare';

        $terms[] =
            'medical ' .
            $name;

        return array_values(
            array_unique($terms)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Create local fallback image
    |--------------------------------------------------------------------------
    */

    private function createFallbackImage(
        string $name,
        int $number
    ): string {
        $image =
            imagecreatetruecolor(
                1000,
                1000
            );

        $background =
            imagecolorallocate(
                $image,
                248,
                250,
                252
            );

        $white =
            imagecolorallocate(
                $image,
                255,
                255,
                255
            );

        $dark =
            imagecolorallocate(
                $image,
                30,
                41,
                59
            );

        $gray =
            imagecolorallocate(
                $image,
                100,
                116,
                139
            );

        $blue =
            imagecolorallocate(
                $image,
                37,
                99,
                235
            );

        $border =
            imagecolorallocate(
                $image,
                226,
                232,
                240
            );

        imagefill(
            $image,
            0,
            0,
            $background
        );

        /*
        |--------------------------------------------------------------------------
        | Product card
        |--------------------------------------------------------------------------
        */

        imagefilledrectangle(
            $image,
            70,
            70,
            930,
            930,
            $white
        );

        imagerectangle(
            $image,
            70,
            70,
            930,
            930,
            $border
        );

        /*
        |--------------------------------------------------------------------------
        | Medical product illustration
        |--------------------------------------------------------------------------
        */

        imagefilledellipse(
            $image,
            500,
            400,
            500,
            500,
            $background
        );

        /*
        | Main instrument
        */

        imagefilledrectangle(
            $image,
            455,
            190,
            545,
            580,
            $gray
        );

        /*
        | Instrument head
        */

        imagefilledellipse(
            $image,
            500,
            190,
            140,
            140,
            $gray
        );

        /*
        | Handle
        */

        imagefilledrectangle(
            $image,
            410,
            570,
            590,
            650,
            $dark
        );

        /*
        | Blue medical accent
        */

        imagefilledrectangle(
            $image,
            410,
            655,
            590,
            680,
            $blue
        );

        /*
        |--------------------------------------------------------------------------
        | Product number
        |--------------------------------------------------------------------------
        */

        imagestring(
            $image,
            5,
            425,
            735,
            'MEDICAL PRODUCT ' .
                $number,
            $dark
        );

        /*
        |--------------------------------------------------------------------------
        | Product name
        |--------------------------------------------------------------------------
        */

        $shortName =
            Str::limit(
                $name,
                36,
                ''
            );

        imagestring(
            $image,
            4,
            max(
                100,
                500 -
                    (strlen($shortName) * 4)
            ),
            790,
            $shortName,
            $dark
        );

        /*
        |--------------------------------------------------------------------------
        | Category
        |--------------------------------------------------------------------------
        */

        imagestring(
            $image,
            3,
            380,
            845,
            'SURGICAL / MEDICAL SUPPLY',
            $gray
        );

        /*
        |--------------------------------------------------------------------------
        | Convert to JPEG
        |--------------------------------------------------------------------------
        */

        ob_start();

        imagejpeg(
            $image,
            null,
            92
        );

        $output =
            ob_get_clean();

        imagedestroy(
            $image
        );

        return $output;
    }
}