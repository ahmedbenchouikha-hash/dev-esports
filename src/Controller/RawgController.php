<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RawgController extends AbstractController
{
    public function __construct(private readonly HttpClientInterface $httpClient)
    {
    }

    #[Route('/rawg/games', name: 'rawg_games', methods: ['GET'])]
    public function games(Request $request): Response
    {
        $query = trim((string) $request->query->get('q', ''));
        $games = [];
        $error = null;

        $apiKey = (string) $this->getParameter('rawg_api_key');
        if ($apiKey === '') {
            $error = 'RAWG API key is missing. Add RAWG_API_KEY to your .env.local file.';

            return $this->render('rawg/search.html.twig', [
                'query' => $query,
                'games' => $games,
                'error' => $error,
            ]);
        }

        if ($query !== '') {
            try {
                $requestOptions = [
                    'query' => [
                        'key' => $apiKey,
                        'search' => $query,
                        'page_size' => 12,
                    ],
                ];
                $caFile = $this->resolveCaFile();
                if ($caFile !== null) {
                    $requestOptions['cafile'] = $caFile;
                }
                if ((bool) $this->getParameter('rawg_allow_insecure_ssl')) {
                    $requestOptions['verify_peer'] = false;
                    $requestOptions['verify_host'] = false;
                }

                $response = $this->httpClient->request('GET', 'https://api.rawg.io/api/games', $requestOptions);

                $data = $response->toArray(false);
                if (($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) && isset($data['results']) && is_array($data['results'])) {
                    $games = $data['results'];
                } else {
                    $error = 'RAWG request failed. Check your API key and try again.';
                }
            } catch (ExceptionInterface $e) {
                $error = str_contains($e->getMessage(), 'certificate verify failed')
                    ? 'TLS certificate validation failed. Set SSL_CERT_FILE to a valid cacert.pem path (or configure openssl.cafile/curl.cainfo in php.ini).'
                    : 'Could not reach RAWG API right now. Please try again.';
            }
        }

        return $this->render('rawg/search.html.twig', [
            'query' => $query,
            'games' => $games,
            'error' => $error,
        ]);
    }

    private function resolveCaFile(): ?string
    {
        $sslCertFile = (string) ($_SERVER['SSL_CERT_FILE'] ?? $_ENV['SSL_CERT_FILE'] ?? getenv('SSL_CERT_FILE'));
        $projectCaFile = (string) $this->getParameter('kernel.project_dir').'/var/certs/cacert.pem';

        $candidates = [
            $projectCaFile,
            $sslCertFile,
            (string) ini_get('openssl.cafile'),
            (string) ini_get('curl.cainfo'),
        ];

        if (\DIRECTORY_SEPARATOR === '\\') {
            $phpDir = \dirname(\PHP_BINARY);
            $candidates[] = $phpDir.'\\extras\\ssl\\cacert.pem';
            $candidates[] = $phpDir.'\\cacert.pem';
            $candidates[] = 'C:\\xampp\\php\\extras\\ssl\\cacert.pem';
            $candidates[] = 'C:\\php\\extras\\ssl\\cacert.pem';
        }

        foreach ($candidates as $candidate) {
            if ($candidate !== '' && is_file($candidate) && is_readable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
