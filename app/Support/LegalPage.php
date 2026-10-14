<?php

namespace App\Support;

use App\Models\Setting;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Instance legal pages: Imprint, Privacy Policy and Terms of Use.
 *
 * Content is Markdown maintained by Super-Admins at /admin/legal and stored in
 * the `settings` table. The Imprint (required in e.g. Germany/Austria, not in
 * the US) and the Terms are optional; the Privacy Policy is always published
 * and falls back to a built-in template describing what YAAMS processes.
 */
class LegalPage
{
    public const IMPRINT = 'imprint';
    public const PRIVACY = 'privacy';
    public const TERMS = 'terms';

    /**
     * Page metadata, in footer order.
     */
    public const PAGES = [
        self::IMPRINT => ['title' => 'Imprint',        'icon' => 'building'],
        self::PRIVACY => ['title' => 'Privacy Policy', 'icon' => 'shield-lock'],
        self::TERMS   => ['title' => 'Terms of Use',   'icon' => 'file-earmark-text'],
    ];

    public static function title(string $page): string
    {
        return self::PAGES[$page]['title'];
    }

    public static function url(string $page): string
    {
        return route('legal.' . $page);
    }

    /**
     * Whether the page is published. Privacy is always on; Imprint and Terms
     * need their toggle on and actual content.
     */
    public static function enabled(string $page): bool
    {
        if ($page === self::PRIVACY) {
            return true;
        }

        return Setting::get($page . '_enabled') === '1'
            && trim((string) Setting::get($page . '_content')) !== '';
    }

    /**
     * The admin-entered Markdown, or null when none is set.
     */
    public static function content(string $page): ?string
    {
        $content = trim((string) Setting::get($page . '_content'));

        return $content === '' ? null : $content;
    }

    /**
     * True while the Privacy Policy still shows the built-in template.
     */
    public static function usesDefaultPrivacy(): bool
    {
        return self::content(self::PRIVACY) === null;
    }

    /**
     * The built-in privacy template as Markdown, filled with live instance data.
     */
    public static function defaultPrivacy(): string
    {
        return view('legal.default-privacy', [
            'instanceName'  => Setting::get('app_name', config('app.name')),
            'supportEmail'  => Setting::get('support_email'),
            'retentionDays' => (int) config('activitylog.delete_records_older_than_days'),
        ])->render();
    }

    /**
     * The page body rendered to HTML. Raw HTML in the Markdown is stripped and
     * unsafe links (javascript:, data:) are dropped, so admin content cannot
     * inject scripts. Single line breaks are kept (like GitHub comments), so
     * addresses in an Imprint render line by line as typed.
     */
    public static function html(string $page): HtmlString
    {
        $markdown = self::content($page)
            ?? ($page === self::PRIVACY ? self::defaultPrivacy() : '');

        return new HtmlString(Str::markdown($markdown, [
            'html_input'         => 'strip',
            'allow_unsafe_links' => false,
            'renderer'           => ['soft_break' => "<br>\n"],
        ]));
    }

    /**
     * When Privacy or Terms content last changed, or null if never.
     */
    public static function updatedAt(): ?CarbonInterface
    {
        $value = Setting::get('legal_updated_at');

        return $value ? Carbon::parse($value) : null;
    }

    /**
     * Published pages as [page => ['title', 'icon', 'url']], in footer order.
     */
    public static function links(): array
    {
        $links = [];

        foreach (self::PAGES as $page => $meta) {
            if (self::enabled($page)) {
                $links[$page] = $meta + ['url' => self::url($page)];
            }
        }

        return $links;
    }
}
