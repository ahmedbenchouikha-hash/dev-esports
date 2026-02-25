<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Routing\Annotation\Route;

class RecompenseGifController extends AbstractController
{
    #[Route('/api/recompense-gifs', name: 'api_recompense_gifs', methods: ['GET'])]
    public function list(HttpClientInterface $httpClient): JsonResponse
    {
        $apiKey = $_ENV['GIPHY_API_KEY'] ?? $_SERVER['GIPHY_API_KEY'] ?? null;
        if (!$apiKey) {
            return $this->json([
                'error' => 'GIPHY_API_KEY is missing.',
                'categories' => [],
            ], 500);
        }

        $categories = [
            [
                'key' => 'accessoire',
                'label' => 'Accessoire',
                'queries' => ['gaming keyboard', 'gaming mouse', 'gaming pc', 'game controller', 'ps5'],
            ],
            [
                'key' => 'argent',
                'label' => 'Argent',
                'queries' => ['cash money', 'banknotes', 'money stack'],
            ],
            [
                'key' => 'trophee',
                'label' => 'Trophee',
                'queries' => ['gold trophy', 'bronze trophy', 'trophy cup'],
            ],
            [
                'key' => 'medaille',
                'label' => 'Medaille',
                'queries' => ['gold medal', 'bronze medal', 'platinum medal'],
            ],
        ];

        $results = [];

        foreach ($categories as $category) {
            $query = implode(' OR ', $category['queries']);
            $offset = random_int(0, 25);

            try {
                $response = $httpClient->request('GET', 'https://api.giphy.com/v1/gifs/search', [
                    'query' => [
                        'api_key' => $apiKey,
                        'q' => $query,
                        'limit' => 8,
                        'offset' => $offset,
                        'rating' => 'g',
                        'lang' => 'fr',
                    ],
                ]);

                $payload = $response->toArray(false);
            } catch (\Throwable $e) {
                $payload = ['data' => []];
            }

            $items = [];
            foreach ($payload['data'] ?? [] as $gif) {
                $gifUrl = $gif['images']['downsized_medium']['url'] ?? $gif['images']['original']['url'] ?? null;
                if (!$gifUrl) {
                    continue;
                }

                $title = trim((string) ($gif['title'] ?? ''));
                if ($title === '') {
                    $title = $category['label'];
                }

                $items[] = [
                    'name' => $title,
                    'detail' => $category['label'],
                    'gifUrl' => $gifUrl,
                ];
            }

            $results[] = [
                'key' => $category['key'],
                'label' => $category['label'],
                'items' => $items,
            ];
        }

        return $this->json([
            'categories' => $results,
        ]);
    }
}
