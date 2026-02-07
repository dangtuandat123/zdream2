<?php

namespace App\Livewire;

use App\Models\Style;
use App\Models\Tag;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class StylesBrowser extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public function paginationView(): string
    {
        return 'vendor.pagination.zdream';
    }

    public function paginationSimpleView(): string
    {
        return 'vendor.pagination.zdream-simple';
    }

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'price')]
    public string $price = '';

    #[Url(as: 'sort')]
    public string $sort = 'popular';

    #[Url(as: 'tag')]
    public string $tag = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPrice(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function updatedTag(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->price = '';
        $this->sort = 'popular';
        $this->tag = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = Style::query()
            ->notSystem()
            ->active()
            ->with('tag')
            ->withCount('generatedImages');

        $search = trim($this->search);
        if ($search !== '') {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if (!empty($this->price)) {
            match ($this->price) {
                'free' => $query->where('price', 0),
                'low' => $query->where('price', '>', 0)->where('price', '<=', 5),
                'mid' => $query->where('price', '>', 5)->where('price', '<=', 15),
                'high' => $query->where('price', '>', 15),
                default => null,
            };
        }

        if (!empty($this->tag)) {
            $query->where('tag_id', $this->tag);
        }

        match ($this->sort) {
            'newest' => $query->reorder()->latest(),
            'price_asc' => $query->reorder()->orderBy('price', 'asc')->orderBy('name', 'asc'),
            'price_desc' => $query->reorder()->orderBy('price', 'desc')->orderBy('name', 'asc'),
            'popular' => $query->reorder()->orderByDesc('generated_images_count'),
            default => $query->ordered(),
        };

        $styles = $query->paginate(12);

        $priceRanges = [
            'free' => 'Miễn phí',
            'low' => '1-5 Xu',
            'mid' => '6-15 Xu',
            'high' => '> 15 Xu',
        ];

        $sortOptions = [
            'popular' => 'Phổ biến',
            'newest' => 'Mới nhất',
            'price_asc' => 'Giá thấp → cao',
            'price_desc' => 'Giá cao → thấp',
        ];

        $tags = Tag::query()
            ->active()
            ->ordered()
            ->get(['id', 'name']);

        return view('livewire.styles-browser', [
            'styles' => $styles,
            'priceRanges' => $priceRanges,
            'sortOptions' => $sortOptions,
            'tags' => $tags,
        ]);
    }
}
