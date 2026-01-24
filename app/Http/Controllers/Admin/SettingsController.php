<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * SettingsController (Admin)
 * 
 * Quản lý cấu hình hệ thống
 */
class SettingsController extends Controller
{
    /**
     * Hiển thị trang settings
     */
    public function index(): View
    {
        $apiSettings = Setting::where('group', 'api')->get();
        $generalSettings = Setting::where('group', 'general')->get();
        
        return view('admin.settings.index', compact('apiSettings', 'generalSettings'));
    }

    /**
     * Cập nhật settings
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'openrouter_api_key' => 'nullable|string|max:500',
            'openrouter_base_url' => 'nullable|url|max:500',
            'site_name' => 'nullable|string|max:255',
            'default_credits' => 'nullable|integer|min:0',
        ]);

        // Update API Key (encrypted)
        if (filled($validated['openrouter_api_key'] ?? null)) {
            Setting::set('openrouter_api_key', $validated['openrouter_api_key'], [
                'is_encrypted' => true,
                'group' => 'api',
                'label' => 'OpenRouter API Key',
            ]);
        }

        // Update Base URL
        if (isset($validated['openrouter_base_url'])) {
            Setting::set('openrouter_base_url', $validated['openrouter_base_url'], [
                'group' => 'api',
                'label' => 'OpenRouter Base URL',
            ]);
        }

        // Update General Settings
        if (isset($validated['site_name'])) {
            Setting::set('site_name', $validated['site_name'], [
                'group' => 'general',
                'label' => 'Tên website',
            ]);
        }

        if (isset($validated['default_credits'])) {
            Setting::set('default_credits', $validated['default_credits'], [
                'type' => 'integer',
                'group' => 'general',
                'label' => 'Xu mặc định',
            ]);
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Cài đặt đã được cập nhật!');
    }
}
