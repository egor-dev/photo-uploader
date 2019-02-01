<?php

namespace App\Handlers;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * Downloads images from URLs.
 *
 * @package App\Handlers
 */
class LocatedByUrl
{
    /**
     * @var ClientInterface
     */
    private $client;
    /**
     * @var array
     */
    private $allowedContentTypes;
    /**
     * @var FileSaver
     */
    private $fileSaver;

    public function __construct(
        Client $client,
        FileSaver $fileSaver,
        $allowedContentTypes
    ) {
        $this->client = $client;
        $this->allowedContentTypes = $allowedContentTypes;
        $this->fileSaver = $fileSaver;
    }

    /**
     * @param array $urls
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return array
     */
    public function handle(array $urls): array
    {
        $photos = collect();
        $errors = collect();
        foreach ($urls as $url) {
            try {
                $response = $this->client->request('GET', $url);
            } catch (RequestException $exception) {
                $errors->push("Failed to download image from $url.");

                continue;
            }

            if (! $this->isContentTypeAllowed($response)) {
                $errors->push("Failed to download image from $url.");

                continue;
            }
            $contents = $response->getBody()->getContents();

            $photos->push(
                $this->fileSaver->save($contents, $this->getFileExtensionFromUrl($url))
            );
        }

        return [$photos, $errors];
    }

    /**
     * @param ResponseInterface $response
     *
     * @return bool
     */
    private function isContentTypeAllowed(ResponseInterface $response): bool
    {
        return \in_array($response->getHeader('Content-Type')[0], $this->allowedContentTypes, false);
    }

    /**
     * @param $url
     *
     * @return string
     */
    private function getFileExtensionFromUrl($url): string
    {
        return pathinfo(parse_url($url)['path'])['extension'];
    }
}
