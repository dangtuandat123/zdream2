<?php

namespace App\Livewire;

use App\Models\Style;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class HistoryBrowser extends Component
{
    use WithPagination;

    public function paginationView()
    {
        return 'vendor.pagination.zdream';
    }

    public function paginationSimpleView()
    {
        return 'vendor.pagination.zdream-simple';
    }

    #[Url(except: '')]
    public $status = '';

    #[Url(except: '')]
    public $style_id = '';

    // Reset pagination when filters update
    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function updatedStyleId()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();

        $query = $user->generatedImages()->with('style');

        // Filter by status
        if ($this->status && in_array($this->status, ['completed', 'processing', 'failed'])) {
            $query->where('status', $this->status);
        }

        // Filter by style
        if ($this->style_id) {
            $query->where('style_id', (int) $this->style_id);
        }

        $images = $query->latest()->paginate(12);

        // Get styles for filter dropdown
        $styles = Style::select('id', 'name')->orderBy('name')->get();

        return view('livewire.history-browser', [
            'images' => $images,
            'styles' => $styles,
        ]);
    }
}
