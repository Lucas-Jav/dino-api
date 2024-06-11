<?php

namespace App\Services;

use App\Models\Dinosaur;
use Illuminate\Support\Facades\Http;

class ScrapingService
{
    public function scrapeDinosaurPage($url)
    {
        $response = Http::withOptions(['verify' => false])->get($url);
        $html = $response->body();

        $dom = new \DOMDocument();
        @$dom->loadHTML($html);

        $xpath = new \DOMXPath($dom);

        $titleWithoutSuffix = $this->extractTitle($xpath);
        $type = $this->extractType($xpath);
        $image_url = $this->extractImageUrl($xpath);
        $period = $this->extractPeriod($xpath);
        $habitat = $this->extractHabitat($xpath);

        $this->createDinosaur([
            'name' => $titleWithoutSuffix,
            'image_url' => $image_url,
            'period' => $period,
            'habitat' => $habitat,
            'type' => $type,
        ]);

        $nextDinoUrl = $this->extractNextDinoUrl($xpath);

        if ($nextDinoUrl) {
            dispatch(function () use ($nextDinoUrl) {
                $this->scrapeDinosaurPage("https://dinosaurpictures.org" . $nextDinoUrl);
            });
        } else {
            return null;
        }
    }

    private function extractTitle($xpath)
    {
        $titleNode = $xpath->query('//h1[@class="main-title"]')->item(0);
        $title = $titleNode ? $titleNode->nodeValue : null;

        return str_replace(" pictures and facts", "", $title);
    }

    private function extractType($xpath)
    {
        $typeNode = $xpath->query('//div[@class="intro"]/p[1]/strong')->item(0);
        return $typeNode ? $typeNode->nodeValue : null;
    }

    private function extractImageUrl($xpath)
    {
        $imageNode = $xpath->query('//div[@class="img-container dino-page-featured"]/a/img')->item(0);
        return $imageNode ? $imageNode->getAttribute('src') : null;
    }

    private function extractPeriod($xpath)
    {
        $infoNode = $xpath->query('//div[@class="intro"]/p[1]')->item(0);
        preg_match('/It lived in the (\w+) period/', $infoNode->nodeValue, $matches);
        return $matches[1] ?? null;
    }

    private function extractHabitat($xpath)
    {
        $infoNode = $xpath->query('//div[@class="intro"]/p[1]')->item(0);
        preg_match('/inhabited (.*)\./', $infoNode->nodeValue, $matches);
        return $matches[1] ?? null;
    }

    private function createDinosaur($data)
    {
        Dinosaur::create([
            'name' => $data['name'] ?? "",
            'image_url' => $data['image_url'] ?? "",
            'period' => $data['period'] ?? "",
            'habitat' => $data['habitat'] ?? "",
            'type' => $data['type'] ?? "",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function extractNextDinoUrl($xpath)
    {
        $nextDinoUrlNode = $xpath->query('//a[@id="next-dino"]')->item(0);
        return $nextDinoUrlNode ? $nextDinoUrlNode->getAttribute('href') : null;
    }
}

