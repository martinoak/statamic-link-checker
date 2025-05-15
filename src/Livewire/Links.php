<?php

namespace Martinoak\StatamicLinkChecker\Livewire;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Component;
use Martinoak\StatamicLinkChecker\Http\Controllers\LinkCheckerController;
use Martinoak\StatamicLinkChecker\Model\Link;

class Links extends Component
{
    const ASC = 'asc';
    const DESC = 'desc';
    const PER_PAGE = 15;

    public string $sortBy = 'code';
    public string $sortDirection = self::DESC;

    public array $filter = [];

    public bool $showRedirects = true;
    public bool $mineOnly = false;

    public int $page = 1;

    public array $statusCodesSelected = [];

    public array $linkTypesSelected = [];

    protected $updatesQueryString = ['statusCodesSelected', 'linkTypesSelected'];

    public function setFilter($field, mixed $value): void
    {
        $this->filter[$field] = $value;
        $this->page = 1;
    }

    public function sort(string $by): void
    {
        if ($this->sortBy === $by) {
            $this->sortDirection = $this->sortDirection === self::ASC ? self::DESC : self::ASC;
        } else {
            $this->sortBy = $by;
            $this->sortDirection = self::ASC;
        }
    }

    public function paginate(int $page): void
    {
        $this->page = $page;
    }

    public function getLinks(): LengthAwarePaginator
    {
        $query = Link::query();

        if (!empty($this->filter)) {
            foreach ($this->filter as $field => $value) {
                $query->where($field, $value);
            }
        }

        if (! $this->showRedirects) {
            $query->where('code', '<', '300')->orWhere('code', '>=', '303');
        }

        if ($this->mineOnly) {
            $query->where('editor', '=', auth()->user()->id);
        }

        $selectedCodes = array_keys(array_filter($this->statusCodesSelected));
        if (!empty($selectedCodes)) {
            $query->whereIn('code', $selectedCodes);
        }

        $selectedTypes = array_keys(array_filter($this->linkTypesSelected));
        if (!empty($selectedTypes)) {
            $query->where(function($q) use ($selectedTypes) {
                foreach ($selectedTypes as $type) {
                    switch ($type) {
                        case 'absolute':
                            $q->orWhere('url', 'like', 'http%');
                            break;
                        case 'relative':
                            $q->orWhere('url', 'like', '/%');
                            break;
                        case 'id':
                            $q->orWhere('url', 'like', '%::%');
                            break;
                    }
                }
            });
        }

        return $query->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(self::PER_PAGE, ['*'], 'page', $this->page);
    }

    public function getStatusCodes(): array
    {
        $codes = Link::all()->pluck('code')->unique()->sort()->values()->toArray();
        $result = [];

        foreach ($codes as $code) {
            if (empty($code)) continue;

            $label = $code;
            $message = LinkCheckerController::getCodeMessage($code);
            if ($message !== 'Unknown') {
                $label .= ' - ' . $message;
            }

            $result[] = ['value' => $code, 'label' => $label];
        }

        return $result;
    }

    public function getLinkTypes(): array
    {
        return [
            ['value' => 'absolute', 'label' => 'Externí (absolutní)'],
            ['value' => 'relative', 'label' => 'Relativní'],
            ['value' => 'id', 'label' => 'Interní (ID)'],
        ];
    }

    public function toggleStatusCode(string $code): void
    {
        if (in_array($code, $this->statusCodesSelected)) {
            $this->statusCodesSelected = array_diff($this->statusCodesSelected, [$code]);
        } else {
            $this->statusCodesSelected[] = $code;
        }
        $this->page = 1;
    }

    public function toggleLinkType(string $type): void
    {
        if (in_array($type, $this->linkTypesSelected)) {
            $this->linkTypesSelected = array_diff($this->linkTypesSelected, [$type]);
        } else {
            $this->linkTypesSelected[] = $type;
        }
        $this->page = 1;
    }

    public function render(): View
    {
        return view('link-checker::livewire.links', [
            'links' => $this->getLinks(),
            'statusCodes' => $this->getStatusCodes(),
            'linkTypes' => $this->getLinkTypes(),
        ]);
    }
}
