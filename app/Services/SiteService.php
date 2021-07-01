<?php

namespace App\Services;

use App\Models\Link;
use App\Models\Site;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

class SiteService
{
    /**
     * @var Client $client
     */
    public $client;

    /**
     * @var Site $site
     */
    public $site;

    /**
     * @var string $currentUtl
     */
    public $currentUtl;

    /**
     * @param string $domain
     * @return bool
     */
    public function getLinks(string $domain): bool
    {
        try {
            $this->site = Site::where('domain', $domain)->first();
            if (!$this->site) {
                $this->site = new Site();
                $this->site->domain = $domain;
                $this->site->save();
            }
            $mainUrl = 'https://' . $domain;
            $this->client = new Client([
                'base_uri' => $mainUrl,
                'timeout'  => 10.0,
            ]);

            $sourceLink = '/';
            $baseLink = Link::where('site_id', $this->site->id)->where('url', $sourceLink)->first();
            if (!$baseLink) {
                $baseLink = Link::createLink($this->site->id, $sourceLink);
            }

            $issetNotParsed = Link::where('site_id', $this->site->id)->where('is_parsed', Link::NOT_PARSED)->first();
            if ($issetNotParsed) {
                while ($issetNotParsed) {
                    DB::table('links')
                        ->where('site_id', $this->site->id)
                        ->where('is_parsed', Link::NOT_PARSED)
                        ->orderBy('id')
                        ->chunk(100, function ($links) use ($mainUrl) {
                            foreach ($links as $link) {
                                $this->getLink($mainUrl, $link);
                            }
                        });
                    $issetNotParsed = Link::where('site_id', $this->site->id)->where('is_parsed', Link::NOT_PARSED)->first();
                }
            }
            return true;
        } catch (\Exception $exception) {
            $errorLink = Link::where('site_id', $this->site->id)->where('url', $this->currentUtl)->first();
            if ($errorLink) {
                $errorLink->is_parsed = Link::PARSED;
                $errorLink->error = $exception->getMessage();
                $errorLink->save();
            }
            return false;
        }
    }

    /**
     * @param $mainUrl
     * @param $baseLink
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getLink($mainUrl, $baseLink)
    {
        $this->currentUtl = $baseLink->url;
        $response = $this->client->get($baseLink->url);
        preg_match_all("/\<a(.*?)href=(\"|\')(.*?)(\"|\')/i", $response->getBody()->getContents(), $matches);
        $linkIds = [];
        foreach ($matches[3] as $link) {
            if ($link[0] == '/') {
                $link = $mainUrl . $link;
            } else if (strpos($link, $mainUrl) === false || $link === $mainUrl || $link === $mainUrl.'/' ) {
                continue;
            }
            $link = strtok($link, '#');
            $link = strtok($link, '?');
            $link = explode('://'.$this->site->domain, $link)[1];
            $issetLink = Link::where('site_id', $this->site->id)->where(function ($query) use ($link) {
                $query->where('url', $link)
                    ->orWhere('url', $link.'/');
            })->first();
            if (!$issetLink) {
                $issetLink = Link::createLink($this->site->id, $link);
            }
            if ($baseLink->id != $issetLink->id) {
                $linkIds[] = $issetLink->id;
            }
        }
        $baseLink = Link::where('site_id', $this->site->id)->where('url', $baseLink->url)->first();
        if ($baseLink) {
            $baseLink->sourceLinks()->syncWithoutDetaching($linkIds);
            $baseLink->is_parsed = Link::PARSED;
            $baseLink->save();
        }
        sleep(1);
    }

    /**
     * @param string $domain
     * @return array
     */
    public function getSite(string $domain): array
    {
        $targetLinks = [];
        $site = Site::where('domain', $domain)->first();
        if ($site) {
            foreach ($site->links as $link) {
                if ($link->url == '/') continue;
                $targetLinks[] = [
                    'url' => $link->url,
                    'links' => count($link->sourceLinks),
                    'linked' => count($link->targetLinks),
                ];
            }
            usort($targetLinks, function($a, $b)
            {
                return $a['linked'] - $b['linked'];
            });
        }

        return $targetLinks;
    }

}
