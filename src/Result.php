<?php

namespace Martinoak\StatamicLinkChecker;

use Statamic\Facades\Data;

class Result
{
    public function __construct(private array $data)
    {
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getByEmail()
    {
        $result = [];
        foreach ($this->data as $link) {
            foreach ($link['contents'] as $contentId) {
                $content = Data::find($contentId);
                $email = $content->updated_by ? $content->updated_by->email : '';
                $result[$email][$link['status_code']][$contentId][] = $link;
            }
        }
        return $result;
    }

    public function getByStatusCode()
    {
        $result = [];
        foreach ($this->data as $link) {
            $result[$link['status_code']][] = $link;
        }
        return $result;
    }
}
