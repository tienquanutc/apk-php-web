<?php


namespace App\twig;


use Twig\Extension\AbstractExtension;
use Twig\Extension\ExtensionInterface;
use Twig\NodeVisitor\NodeVisitorInterface;
use Twig\TokenParser\TokenParserInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

class TwigExtension extends AbstractExtension {

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return TwigFilter[]
     */
    public function getFilters() {
        return [new TwigFilter('human_size', function ($bytes) {
            if (!$bytes) return 'N/A';
            $unit = 1024;
            if ($bytes < $unit) return $bytes;

            $exp = (int)(log($bytes) / log($unit));
            $bytesPerGb = 1073741824;
            $postfix = 'KMGTPE'[$exp - 1];

            $value = round($bytes / pow($unit, $exp), 0);
            if ($bytes >= $bytesPerGb) {
                $value = round($bytes / pow($unit, $exp), 2);
            }
            return "$value$postfix";
        }), new TwigFilter('human_num_downloads', function ($number) {
            if (!$number) return 'N/A';
            $unit = 1000;
            if ($number < $unit) return $number;

            $exp = (int)(log($number) / log($unit));
            $postfix = ["N", "Tr", "T"][$exp - 1];

            $value = round($number / pow($unit, $exp), 2);
            return "$value$postfix";
        }),new TwigFilter('image', function($url, $s=90){
            if(strpos($url, 'googleusercontent') <= 0) return $url;
            return $url."=s".$s;
        })];
    }
}