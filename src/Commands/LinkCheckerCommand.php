<?php

namespace Martinoak\StatamicLinkChecker\Commands;

use Martinoak\StatamicLinkChecker\Mail\InvalidLinksMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Martinoak\StatamicLinkChecker\Checker;
use Martinoak\StatamicLinkChecker\Model\Link;

class LinkCheckerCommand extends Command
{
    protected $signature = 'link:check {--mail= : Email, kam se má výsledek odeslat, "updated_by" email posledního editora}
                                       {--subject= : Předmět emailu}
                                       {--cc= : Kopie emailu}
                                       {--bcc= : Skrytá kopie emailu}
                                       {--exclude-500 : Vyloučit 5xx chyby}
                                       {--a|absolute : Kontrolovat absolutní odkazy}
                                       {--r|relative : Kontrolovat relativní odkazy}
                                       {--i|id : Kontrolovat odkazy přes ID}
                                       {--internal : Kontrolovat interní odkazy}
                                       {--internal-fix : Opravit interní odkazy}';

    protected $description = 'Kontrola Statamic odkazů a řešení nefunkčních odkazů na webu';

    protected const MANAGER = 'martin.dub@dek-cz.com';
    private ?string $mail = null;
    private string $subject = 'Kontrola Statamic odkazů';

    public function handle(): int
    {
        Link::truncate();

        $this->mail = $this->option('mail') ?: $this->ask('Kam se má výsledek odeslat? Případně "updated_by" email posledního editora');
        $cc = $this->option('cc') ?: null;
        $bcc = $this->option('bcc') ?: null;
        $exclude500 = $this->option('exclude-500');
        $absolute = $this->option('absolute');
        $relative = $this->option('relative');
        $id = $this->option('id');

        $checker = new Checker(
            $this->mail,
            self::MANAGER,
            base_path('content')
        );

        $errors = $checker->getResult($absolute, $relative, $id);
        $errors = $this->mergeErrors($errors);
        $exclude500 && $this->exclude(500, 511, $errors);

        if ($this->mail === null) {
            $rows = [];
            foreach (reset($errors) as $status => $list) {
                if (is_array($list)) {
                    foreach ($list as $error) {
                        $rows[] = [$status, str_replace('*', '', $error)];
                    }
                } else {
                    $rows[] = [$status, str_replace('*', '', $list)];
                }
            }
            $this->table(['Status', 'URL'], $rows);
        } elseif ($this->mail === 'manager') {
            Mail::to(self::MANAGER)->cc($cc)->bcc($bcc)->send(new InvalidLinksMail(reset($errors), $this->subject));
        } else {
            $checker->sendResult($errors, $this->subject, $cc, $bcc);
        }

        return Command::SUCCESS;
    }

    protected function mergeErrors(array $input): array
    {
        $output = [];

        foreach ($input as $item) {
            foreach ($item as $recipient => $errors) {
                if (!isset($output[$recipient])) {
                    $output[$recipient] = $errors;
                } else {
                    foreach ($errors as $statusCode => $message) {
                        if (!isset($output[$recipient][$statusCode])) {
                            $output[$recipient][$statusCode] = $message;
                        } elseif (is_array($output[$recipient][$statusCode])) {
                            $output[$recipient][$statusCode][] = $message;
                        } else {
                            $output[$recipient][$statusCode] = [$output[$recipient][$statusCode], $message];
                        }
                    }
                }
            }
        }

        return $output;
    }

    protected function exclude(int $from, int $to, array &$errors): void
    {
        $filterErrors = function ($errors) use ($from, $to) {
            foreach ($errors as $code => $list) {
                if (in_array($code, range($from, $to))) {
                    unset($errors[$code]);
                }
            }
            return $errors;
        };

        $errors = $this->mail ? $filterErrors($errors) : array_map($filterErrors, $errors);
    }
}
