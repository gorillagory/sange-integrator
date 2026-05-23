<?php

namespace App\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class DocumentQrCodeService
{
    public function generateDataUri(?string $value, int $size = 160): string
    {
        $payload = trim((string) ($value ?? ''));

        if ($payload === '') {
            return '';
        }

        $writer = new Writer(new ImageRenderer(
            new RendererStyle(max(80, $size), 2),
            new SvgImageBackEnd()
        ));

        $svg = $writer->writeString($payload);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
