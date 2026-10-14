<?php

namespace App\Http\Controllers;

use App\Support\LegalPage;

/**
 * Public legal pages (Imprint, Privacy Policy, Terms of Use). Content is
 * maintained by Super-Admins in Admin\LegalController; disabled pages 404.
 */
class LegalController extends Controller
{
    public function imprint()
    {
        return $this->render(LegalPage::IMPRINT);
    }

    public function privacy()
    {
        return $this->render(LegalPage::PRIVACY);
    }

    public function terms()
    {
        return $this->render(LegalPage::TERMS);
    }

    private function render(string $page)
    {
        abort_unless(LegalPage::enabled($page), 404);

        return view('legal.show', [
            'page'      => $page,
            'title'     => LegalPage::title($page),
            'icon'      => LegalPage::PAGES[$page]['icon'],
            'body'      => LegalPage::html($page),
            // Imprint is static contact data; only policies carry a revision date.
            'updatedAt' => $page === LegalPage::IMPRINT ? null : LegalPage::updatedAt(),
        ]);
    }
}
