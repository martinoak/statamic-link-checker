<?php

namespace Martinoak\StatamicLinkChecker\Livewire;

use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Martinoak\StatamicLinkChecker\Model\Link;

class Links extends Component
{
    const ASC = 'asc';
    const DESC = 'desc';
    const PER_PAGE = 15;

    public string $sortBy = 'code';
    public string $sortDirection = self::DESC;

    public array $filter = [];

    public int $page = 1;

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
        $data = Link::query();

        if (!empty($this->filter)) {
            foreach ($this->filter as $field => $value) {
                $data->where($field, $value);
            }
        }

        return $data->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(self::PER_PAGE, ['*'], 'page', $this->page);
    }

    public function render()
    {
        return view('link-checker::livewire.links', [
            'links' => $this->getLinks(),
        ]);
    }
}
