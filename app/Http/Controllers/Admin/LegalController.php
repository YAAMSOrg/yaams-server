<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\ActivityLevel;
use App\Support\LegalPage;
use Illuminate\Http\Request;

class LegalController extends Controller
{
    /**
     * Legal pages editor (Imprint, Privacy Policy, Terms of Use). Access is
     * restricted to Super-Admins via the `role:Super-Admin` middleware on the
     * route group (see routes/web.php).
     */
    public function edit()
    {
        $settings = [
            'imprint_enabled' => Setting::get('imprint_enabled'),
            'imprint_content' => Setting::get('imprint_content'),
            'privacy_content' => Setting::get('privacy_content'),
            'terms_enabled'   => Setting::get('terms_enabled'),
            'terms_content'   => Setting::get('terms_content'),
        ];

        return view('admin.legal', [
            'settings'        => $settings,
            'usesDefault'     => LegalPage::usesDefaultPrivacy(),
            'defaultPrivacy'  => LegalPage::defaultPrivacy(),
            'updatedAt'       => LegalPage::updatedAt(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'imprint_enabled' => 'required|boolean',
            'imprint_content' => 'nullable|string|max:65535|required_if_accepted:imprint_enabled',
            'privacy_content' => 'nullable|string|max:65535',
            'terms_enabled'   => 'required|boolean',
            'terms_content'   => 'nullable|string|max:65535|required_if_accepted:terms_enabled',
        ], [
            'imprint_content.required_if_accepted' => 'Enter the imprint content before publishing it.',
            'terms_content.required_if_accepted'   => 'Enter the terms of use before publishing them.',
        ]);

        $content = fn (string $key) => trim((string) ($validated[$key] ?? '')) ?: null;

        // Users agree to the privacy policy and terms at registration, so a
        // change to either is dated ("Last updated" on the public pages).
        $changed = collect(['imprint_enabled', 'imprint_content', 'privacy_content', 'terms_enabled', 'terms_content'])
            ->filter(fn ($key) => (str_ends_with($key, '_enabled')
                ? ($request->boolean($key) ? '1' : '0')
                : $content($key)) !== Setting::get($key))
            ->values();

        Setting::set('imprint_enabled', $request->boolean('imprint_enabled') ? '1' : '0');
        Setting::set('imprint_content', $content('imprint_content'));
        Setting::set('privacy_content', $content('privacy_content'));
        Setting::set('terms_enabled', $request->boolean('terms_enabled') ? '1' : '0');
        Setting::set('terms_content', $content('terms_content'));

        if ($changed->intersect(['privacy_content', 'terms_enabled', 'terms_content'])->isNotEmpty()) {
            Setting::set('legal_updated_at', now()->toIso8601String());
        }

        if ($changed->isNotEmpty()) {
            activity()
                ->causedBy(auth()->user())
                ->withProperties(['level' => ActivityLevel::INFO, 'changed' => $changed->all()])
                ->event('legal_pages_updated')
                ->log('Updated legal pages');
        }

        return redirect()
            ->route('admin.legal.edit')
            ->with('status', 'Legal pages saved.');
    }
}
