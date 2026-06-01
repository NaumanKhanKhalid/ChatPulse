<?php
namespace App\Services;

use App\Models\LinkPreview;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class LinkPreviewService
{
    public function fetch(string $url): ?LinkPreview
    {
        // Return cached
        $existing = LinkPreview::where('url', $url)->first();
        if ($existing) return $existing;

        try {
            $client = new Client(['timeout' => 5, 'allow_redirects' => true]);
            $response = $client->get($url, [
                'headers' => ['User-Agent' => 'ChatPulse Link Preview Bot/1.0'],
                'stream' => true,
            ]);

            // Limit response size to 2MB
            $body = '';
            $stream = $response->getBody();
            while (!$stream->eof() && strlen($body) < 2097152) {
                $body .= $stream->read(8192);
            }

            $preview = $this->parseOgTags($body, $url);
            return LinkPreview::create([
                'url' => $url,
                'title' => $preview['title'],
                'description' => $preview['description'],
                'image' => $preview['image'],
                'site_name' => $preview['site_name'],
                'fetched_at' => now(),
            ]);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseOgTags(string $html, string $url): array
    {
        $data = ['title' => null, 'description' => null, 'image' => null, 'site_name' => null];

        // OG tags
        if (preg_match('/<meta[^>]+property=["\']og:title["\'][^>]+content=["\'](.*?)["\']/i', $html, $m)) $data['title'] = html_entity_decode($m[1]);
        if (preg_match('/<meta[^>]+property=["\']og:description["\'][^>]+content=["\'](.*?)["\']/i', $html, $m)) $data['description'] = html_entity_decode($m[1]);
        if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\'](.*?)["\']/i', $html, $m)) $data['image'] = $m[1];
        if (preg_match('/<meta[^>]+property=["\']og:site_name["\'][^>]+content=["\'](.*?)["\']/i', $html, $m)) $data['site_name'] = html_entity_decode($m[1]);

        // Fallback to title tag
        if (!$data['title'] && preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $m)) {
            $data['title'] = html_entity_decode(strip_tags($m[1]));
        }

        return $data;
    }
}
