<?php

namespace App\Http\Controllers;

use App\Models\Dinosaur;
use App\Services\ScrapingService;
use Illuminate\Http\Request;

class DinosaurController extends Controller
{
    protected $scrapingService;

    public function __construct(ScrapingService $scrapingService)
    {
        $this->scrapingService = $scrapingService;
    }

    public function index()
    {
        return response()->json(Dinosaur::all());
    }

    public function startScraping()
    {
        $url = 'https://dinosaurpictures.org/Aachenosaurus-pictures';

        $nextDinoUrl = $this->scrapingService->scrapeDinosaurPage($url);

        // Adicionar o job na fila para processar o próximo dinossauro
        dispatch(function () use ($nextDinoUrl) {
            $this->scrapingService->scrapeDinosaurPage($nextDinoUrl);
        });

        return response()->json(['message' => 'Scraping started']);
    }
}
