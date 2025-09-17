<?php

namespace App\Console\Commands;

use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class ImportWebmagic extends Command
{
    protected $signature = 'app:import-webmagic';
    protected $description = 'Import blog posts from webmagic.agency';

    private string $base = 'https://webmagic.agency';

    /**
     * Crawls and imports blog articles.
     * Uses the Laravel HTTP client and the Symfony DomCrawler component.
     * Implements de-duplication ($seen), a date cutoff, and chunked inserts.
     */
    public function handle()
    {
        $articles = [];
        // Cutoff window: last 4 months (1 article). For testing I used 400 months to load the full archive of relevant posts.
        $cutoff = now()->subMonthsNoOverflow(4)->startOfDay();
        $now = now();
        $page = 1;
        $seen = [];

        try {
            while (true) {
                $listUrl = $this->base . '/blog' . ($page > 1 ? '?page=' . $page : '');
                $result = $this->scrapeListPage($listUrl, $now, $cutoff, $seen);
                // Stop if no more cards are found.
                if (!$result['hasCards']) {
                    break;
                }

                if (!empty($result['rows'])) {
                    $articles = array_merge($articles, $result['rows']);
                }

                $page++;
            }

            Article::truncate();

            foreach (array_chunk($articles, 500) as $chunk) {
                Article::insert($chunk);
            }

            $this->info('Імпортовано: ' . count($articles));
        } catch (\Throwable $e) {
            $this->error('Помилка під час імпорту: ' . $e->getMessage());
        }
    }

    /**
     * Scrapes a listing page and collects article rows.
     * Implements de-duplication via $seen, allows only specific categories, and applies a date cutoff.
     *
     * @param string $url
     * @param \DateTimeInterface $now
     * @param \Carbon\Carbon $cutoff
     * @param array<string,bool> &$seen Set of processed URLs (passed by reference).
     * @return array{rows: array<int,array<string,mixed>>, hasCards: bool}
     */
    private function scrapeListPage(string $url, \DateTimeInterface $now, Carbon $cutoff, array &$seen): array
    {
        $html = $this->get($url);
        if (!$html) {
            return ['rows' => [], 'hasCards' => false];
        }

        $c = new Crawler($html);
        $rows = [];
        // Collect all article cards on the page.
        $cards = $c->filter('a.articles-row');
        $hasCards = $cards->count() > 0;

        $cards->each(function (Crawler $card) use (&$rows, &$seen, $now, $cutoff) {
            $href = $card->attr('href') ?? '';
            if ($href === '') return;
            // De-duplicate by URL.
            if (isset($seen[$href])) return;
            $seen[$href] = true;
            // Extract the title, date, and categories from the card.
            $title = trim($card->filter('.articles-ttl')->first()->text(''));
            $date = trim($card->filter('.articles-date')->first()->text(''));
            // Multiple categories are possible; collect them all.
            $category = $card->filter('.articles-categories__item')->each(function (Crawler $n) {
                return mb_strtolower(preg_replace('/\s+/u', ' ', trim($n->text(''))));
            });

            $required = ['logistics industry'];
            if (!array_intersect($required, $category)) return;
            // Validate fields and parse the date.
            if ($title === '' || $date === '' || !array_intersect($required, $category)) return;

            try {
                $published = Carbon::parse($date)->startOfDay();
            } catch (\Throwable) {
                return;
            }
            // Skip if older than the cutoff.
            if ($published->lt($cutoff)) return;

            $rows[] = [
                'url' => $href,
                'title' => $title,
                'published_at' => $published->toDateString(),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        });

        return ['rows' => $rows, 'hasCards' => $hasCards];
    }

    /**
     * HTTP GET helper for scraping.
     * Sets a 20-second timeout and browser-like headers; returns the response body or null.
     *
     * @param string $url
     * @return string|null
     */
    private function get(string $url): ?string
    {
        try {
            $res = Http::timeout(20)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en,en-US;q=0.9',
                'Cache-Control' => 'no-cache',
                'Pragma' => 'no-cache',
            ])
                ->get($url);

            return $res->successful() ? $res->body() : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
