<?php

namespace Martinoak\StatamicLinkChecker;

readonly class Parser
{
    public function __construct(
        private string $content
    ) {
    }

    public function parseAbsolute(): array
    {
        preg_match_all('#(https?://[^\'")\s]+)#', $this->content, $matches);
        return $matches;
    }

    public function parseRelative(): array
    {
        preg_match_all('#:\s(/[^\'")\s]+)#', $this->content, $matches);
        return $matches;
    }

    public function parseInternal(): array
    {
        preg_match_all('#statamic://entry::([^\'")\s]+)#', $this->content, $matches);
        return $matches;
    }

}
