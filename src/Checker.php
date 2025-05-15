<?php

namespace Martinoak\StatamicLinkChecker;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\URL;
use Martinoak\StatamicLinkChecker\Model\Link;
use Statamic\Facades\Entry;
use Statamic\Facades\User;
use Statamic\Yaml\Yaml;

class Checker
{
    protected string $mail;
    protected string $manager;
    protected string $scannedDirectory;
    protected const SKIP_CODES = [200];
    protected const EXCLUDE = ['.', '..', '.git', '.gitkeep', '.gitignore', 'README.md'];
    private Yaml $yaml;
    private Client $client;

    private int $counter = 0;

    public function __construct(
        string $mail,
        string $manager,
        string $scannedDirectory,
    ) {
        $this->mail = $mail;
        $this->manager = $manager;
        $this->scannedDirectory = $scannedDirectory;
        $this->yaml = new Yaml(new \Symfony\Component\Yaml\Yaml());
        $this->client = new Client([
            'proxy' => [
                'http' => env('HTTP_PROXY'),
                'https' => env('HTTP_PROXY'),
                ]
            ]
        );
    }

    public function init(bool $absolute = true, bool $relative = true, bool $id = true): array
    {
        $merge = $this->scanDirectory($this->scannedDirectory, $absolute, $relative, $id);

        if ($this->mail === null) {
            array_walk_recursive($merge, function($url, $code) use (&$errors) { $errors[$code][] = $url; });
        }

        return $merge;
    }

    public function sendResult(array $errors, string $subject, ?string $cc = null, ?string $bcc = null): void
    {
        foreach ($errors as $recipient => $list) {
            //Mail::to($recipient)->cc($cc)->bcc($bcc)->send(new InvalidLinksMail($list, $subject));
        }
    }

    public function getResult(bool $absolute = true, bool $relative = true, bool $id = true): array
    {
        return $this->init($absolute, $relative, $id);
    }

    protected function scanDirectory(string $dir, bool $absolute = true, bool $relative = true, bool $id = true): ?array
    {
        $merge = [];
        foreach (scandir($dir) as $item) {
            if (!in_array($item, self::EXCLUDE) && !str_ends_with($item, '.yaml')) {
                $path = $dir . DIRECTORY_SEPARATOR . $item;

                if (is_dir($path)) {
                    $merge = array_merge($merge, $this->scanDirectory($path));
                } else {
                    $merge = array_merge($merge, $this->checkLinks($path, $absolute, $relative, $id));
                }

            }
        }

        return $merge;
    }

    protected function checkLinks(string $file, bool $absolute = true, bool $relative = true, bool $id = true): array
    {
        $merge = [];

        preg_match_all('#(https?://[^\'")\s]+)#', file_get_contents($file), $absMatches);
        preg_match_all('#:\s(/[^\'")\s]+)#', file_get_contents($file), $relMatches);
        preg_match_all('#((statamic://)?entry::[^\'")\s]+)#', file_get_contents($file), $idMatches);

        $merge = array_merge($merge, $absolute ? $this->processMatches($file, $absMatches, 'absolute') : []);
        $merge = array_merge($merge, $relative ? $this->processMatches($file, $relMatches, 'relative') : []);
        $merge = array_merge($merge, $id ? $this->processMatches($file, $idMatches, 'id') : []);

        return $merge;
    }

    protected function processMatches(string $file, array $matches, string $type): array
    {
        foreach ($matches[1] as $match) {
            $output[] = match ($type) {
                'absolute' => $this->checkLink($file, $match),
                'relative' => $this->checkLink($file, url()->current() . $match),
                'id' => $this->checkLink($file, $match, true),
            };
        }

        return $output ?? [];
    }

    protected function checkLink(string $file, string $link, bool $id = false): array
    {
        $data = $this->yaml->parse(file_get_contents($file));

        if ($this->mail === null || $this->mail === 'manager') {
            $email = null;
        } elseif ($this->mail === 'updated_by') {
            $email = User::find($data['updated_by']) !== null ? User::find($data['updated_by'])->email() : $this->manager;
        } else {
            $email = $this->mail;
        }

        $description = sprintf("**%s (%s):** %s", $data['title'] ?? 'Title není', basename($file), $link);

        $id && $link = URL::to('/') . Entry::find(preg_match('#(statamic://)?entry::([a-zA-Z0-9_-]+)#', $link, $matches) ? $matches[2] : $link)->url();

        try {
            $response = $this->client->get($link, ['allow_redirects' => false]);
            $status = $response->getStatusCode();

            if (in_array($status, self::SKIP_CODES)) {
                return [];
            } else {
                Link::create([
                    'app-index' => env('APP_INDEX'),
                    'code' => $status,
                    'url' => $id ? Entry::find($link)->url() : $link,
                    'source' => $file,
                    'editor' => $data['updated_by'] ?? 'No editor yet',
                ]);
                return [$email => [$status => $description]];
            }
        } catch (GuzzleException $e) {
            Link::create([
                'app-index' => env('APP_INDEX'),
                'code' => $e->getCode(),
                'url' => $link,
                'source' => $file,
                'editor' => $data['updated_by'] ?? 'No editor yet',
            ]);

            return [$email => [$e->getCode() => $description]];
        }
//
//        if (Entry::find($link) === null) {
//            return [$email => [404 => $description]];
//        }
//
//        return [];
    }

}
