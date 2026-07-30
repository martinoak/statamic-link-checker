<?php

namespace Martinoak\LinkChecker;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Martinoak\LinkChecker\Data\FoundLink;
use Psr\Http\Message\ResponseInterface;

/**
 * Verifies link availability. HTTP(S) links are checked concurrently via Guzzle Pool
 * (HEAD with GET fallback), other types are classified without network requests.
 */
class LinkChecker
{
    /** Status codes where HEAD may fail and GET should be tried instead. */
    private const RETRY_WITH_GET = [400, 403, 405, 406, 501];

    public function __construct(private array $config = [], private ?Client $client = null) {}

    /**
     * @param  array<int, FoundLink>  $links  unique links
     * @param  callable|null  $onChecked  callback after each completed link (for progress)
     * @return array<int, FoundLink>
     */
    public function check(array $links, ?callable $onChecked = null): array
    {
        $checkable = [];
        $anchorLinks = [];

        foreach ($links as $link) {
            if ($this->preClassify($link)) {
                $onChecked && $onChecked($link);
            } elseif ($this->hasCheckableFragment($link)) {
                // Links with #fragment are checked with GET so we can verify the anchor.
                $anchorLinks[] = $link;
            } else {
                $checkable[] = $link;
            }
        }

        // 1st pass: HEAD
        $needsGet = [];
        $this->runPool($checkable, 'HEAD', function (FoundLink $link, ?ResponseInterface $res) use (&$needsGet, $onChecked) {
            if ($res !== null && ! in_array($res->getStatusCode(), self::RETRY_WITH_GET, true)) {
                $this->applyResponse($link, $res);
                $onChecked && $onChecked($link);

                return;
            }

            $needsGet[] = $link;
        });

        // 2nd pass: GET (fallback for servers rejecting HEAD or connection errors)
        $this->runPool($needsGet, 'GET', function (FoundLink $link, ?ResponseInterface $res, $reason) use ($onChecked) {
            $res !== null ? $this->applyResponse($link, $res) : $this->applyError($link, $reason);
            $onChecked && $onChecked($link);
        });

        // 3rd pass: GET with anchor #fragment verification in response body
        $this->runPool($anchorLinks, 'GET', function (FoundLink $link, ?ResponseInterface $res, $reason) use ($onChecked) {
            if ($res === null) {
                $this->applyError($link, $reason);
            } else {
                $this->applyResponse($link, $res);
                $this->verifyAnchor($link, $res);
            }
            $onChecked && $onChecked($link);
        });

        return $links;
    }

    private function hasCheckableFragment(FoundLink $link): bool
    {
        return ($this->config['check_anchors'] ?? true)
            && preg_match('#^https?://#i', (string) $link->url)
            && (string) parse_url($link->url, PHP_URL_FRAGMENT) !== '';
    }

    private function verifyAnchor(FoundLink $link, ResponseInterface $res): void
    {
        // Only verify anchor if the page itself responded successfully.
        if (! in_array($link->state, [FoundLink::STATE_OK, FoundLink::STATE_REDIRECT], true)) {
            return;
        }

        $contentType = strtolower($res->getHeaderLine('Content-Type'));
        if ($contentType !== '' && ! str_contains($contentType, 'html')) {
            return;
        }

        $fragment = urldecode((string) parse_url($link->url, PHP_URL_FRAGMENT));
        $body = (string) $res->getBody();

        $pattern = '/\s(?:id|name)\s*=\s*["\']'.preg_quote($fragment, '/').'["\']/';

        if ($fragment !== '' && ! preg_match($pattern, $body)) {
            $link->state = FoundLink::STATE_ERROR;
            $link->error = __('statamic-link-checker::messages.anchor_missing', ['anchor' => $fragment]);
        }
    }

    /**
     * @param  array<int, FoundLink>  $links
     */
    private function runPool(array $links, string $method, callable $onEach): void
    {
        if (empty($links)) {
            return;
        }

        $client = $this->client();

        $requests = function () use ($links, $method) {
            foreach ($links as $i => $link) {
                yield $i => new Request($method, $link->url);
            }
        };

        (new Pool($client, $requests(), [
            'concurrency' => (int) ($this->config['concurrency'] ?? 10),
            'fulfilled' => fn (ResponseInterface $res, $i) => $onEach($links[$i], $res, null),
            'rejected' => fn ($reason, $i) => $onEach($links[$i], null, $reason),
        ]))->promise()->wait();
    }

    private function client(): Client
    {
        return $this->client ??= new Client([
            'timeout' => (float) ($this->config['timeout'] ?? 10),
            'connect_timeout' => (float) ($this->config['timeout'] ?? 10),
            'verify' => (bool) ($this->config['verify_ssl'] ?? true),
            'http_errors' => false,
            'allow_redirects' => ($this->config['follow_redirects'] ?? true)
                ? ['max' => (int) ($this->config['max_redirects'] ?? 5), 'track_redirects' => true]
                : false,
            'headers' => [
                'User-Agent' => $this->config['user_agent'] ?? 'Statamic-LinkChecker/1.0',
                'Accept' => '*/*',
            ],
        ]);
    }

    private function applyResponse(FoundLink $link, ResponseInterface $res): void
    {
        $status = $res->getStatusCode();
        $link->status = $status;
        $link->finalUrl = $this->finalUrl($res);

        $digit = intdiv($status, 100);

        if (in_array($digit, $this->errorDigits(), true)) {
            $link->state = FoundLink::STATE_ERROR;
            $link->error = 'HTTP '.$status;
        } elseif ($digit === 3) {
            $link->state = FoundLink::STATE_REDIRECT;
        } else {
            $link->state = FoundLink::STATE_OK;
        }
    }

    private function applyError(FoundLink $link, mixed $reason): void
    {
        $link->state = FoundLink::STATE_ERROR;
        $link->status = null;

        if ($reason instanceof RequestException && $reason->hasResponse()) {
            $status = $reason->getResponse()->getStatusCode();
            $link->status = $status;
            $link->error = 'HTTP '.$status;

            return;
        }

        $message = $reason instanceof \Throwable ? $reason->getMessage() : (string) $reason;
        $link->error = $this->shortError($message);
    }

    private function finalUrl(ResponseInterface $res): ?string
    {
        $history = $res->getHeader('X-Guzzle-Redirect-History');

        return empty($history) ? null : (string) end($history);
    }

    private function shortError(string $message): string
    {
        $message = trim(preg_replace('/\s+/', ' ', $message));

        return mb_strlen($message) > 160 ? mb_substr($message, 0, 157).'…' : $message;
    }

    /** @return array<int, int> */
    private function errorDigits(): array
    {
        return array_map('intval', (array) ($this->config['error_statuses'] ?? [4, 5]));
    }

    private function preClassify(FoundLink $link): bool
    {
        if ($link->url === null) {
            $link->state = $link->type === 'unresolved'
                ? FoundLink::STATE_UNRESOLVED
                : FoundLink::STATE_SKIPPED;

            return true;
        }

        if ($link->type === 'unresolved') {
            $link->state = FoundLink::STATE_UNRESOLVED;

            return true;
        }

        if (in_array($link->type, ['mailto', 'tel', 'anchor', 'skipped'], true)) {
            $link->state = FoundLink::STATE_SKIPPED;

            return true;
        }

        if ($link->type === 'external' && ! ($this->config['check_external'] ?? true)) {
            $link->state = FoundLink::STATE_SKIPPED;

            return true;
        }

        if (in_array($link->type, ['internal', 'entry', 'asset'], true) && ! ($this->config['check_internal'] ?? true)) {
            $link->state = FoundLink::STATE_SKIPPED;

            return true;
        }

        if (! preg_match('#^https?://#i', $link->url) || $this->isExcluded($link->url)) {
            $link->state = FoundLink::STATE_SKIPPED;

            return true;
        }

        return false;
    }

    private function isExcluded(string $url): bool
    {
        foreach ((array) ($this->config['exclude_patterns'] ?? []) as $pattern) {
            if ($pattern !== '' && @preg_match('#'.$pattern.'#i', $url) === 1) {
                return true;
            }
        }

        return false;
    }
}
