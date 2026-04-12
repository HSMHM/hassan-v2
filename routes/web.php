<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\WorkshopController;
use Illuminate\Support\Facades\Route;

// Sitemap & robots (no locale prefix)
Route::get('/sitemap.xml', [SitemapController::class, 'index']);
Route::get('/robots.txt', [SitemapController::class, 'robots']);

// RSS feeds
Route::get('/feed', [FeedController::class, 'articles'])->name('feed.ar');
Route::get('/en/feed', [FeedController::class, 'articles'])->name('feed.en');

// Filament admin locale switcher
Route::get('/cpanel/locale/{locale}', function (string $locale) {
    if (in_array($locale, ['ar', 'en'], true)) {
        session(['filament_locale' => $locale]);
        cookie()->queue('filament_locale', $locale, 60 * 24 * 365);
    }

    return redirect('/cpanel');
})->name('filament.admin.locale');

// Legacy redirect — anyone bookmarking /admin goes to /cpanel
Route::get('/admin/{any?}', fn () => redirect('/cpanel'))->where('any', '.*');

// Arabic (default, no prefix)
Route::get('/', [HomeController::class, 'index'])->name('home.ar');
Route::get('/about', [AboutController::class, 'index'])->name('about.ar');
Route::get('/articles', [ArticleController::class, 'index'])->name('articles.ar');
Route::get('/articles/{slug}', [ArticleController::class, 'show'])->name('articles.show.ar');
Route::get('/search', [SearchController::class, 'index'])->name('search.ar');
Route::get('/portfolio', [PortfolioController::class, 'index'])->name('portfolio.ar');
Route::get('/portfolio/{slug}', [PortfolioController::class, 'show'])->name('portfolio.show.ar');
Route::get('/workshops', [WorkshopController::class, 'index'])->name('workshops.ar');
Route::get('/workshops/{slug}', [WorkshopController::class, 'show'])->name('workshops.show.ar');
Route::get('/contact', [ContactController::class, 'index'])->name('contact.ar');
Route::post('/contact', [ContactController::class, 'send'])
    ->middleware('throttle:contact')
    ->name('contact.send.ar');

Route::get('/proposals', [ProposalController::class, 'login'])->name('proposals.login');
Route::post('/proposals/verify', [ProposalController::class, 'verify'])
    ->middleware('throttle:10,60')
    ->name('proposals.verify');
Route::get('/proposals/{proposalId}', [ProposalController::class, 'show'])
    ->middleware('proposal.auth')
    ->name('proposals.show');
Route::get('/p/{slug}', [PageController::class, 'show'])->name('pages.show.ar');

// English
Route::prefix('en')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home.en');
    Route::get('/about', [AboutController::class, 'index'])->name('about.en');
    Route::get('/articles', [ArticleController::class, 'index'])->name('articles.en');
    Route::get('/articles/{slug}', [ArticleController::class, 'show'])->name('articles.show.en');
    Route::get('/search', [SearchController::class, 'index'])->name('search.en');
    Route::get('/portfolio', [PortfolioController::class, 'index'])->name('portfolio.en');
    Route::get('/portfolio/{slug}', [PortfolioController::class, 'show'])->name('portfolio.show.en');
    Route::get('/workshops', [WorkshopController::class, 'index'])->name('workshops.en');
    Route::get('/workshops/{slug}', [WorkshopController::class, 'show'])->name('workshops.show.en');
    Route::get('/contact', [ContactController::class, 'index'])->name('contact.en');
    Route::post('/contact', [ContactController::class, 'send'])
        ->middleware('throttle:contact')
        ->name('contact.send.en');

    Route::get('/proposals', [ProposalController::class, 'login'])->name('proposals.login.en');
    Route::post('/proposals/verify', [ProposalController::class, 'verify'])
        ->middleware('throttle:10,60')
        ->name('proposals.verify.en');
    Route::get('/proposals/{proposalId}', [ProposalController::class, 'show'])
        ->middleware('proposal.auth')
        ->name('proposals.show.en');
    Route::get('/p/{slug}', [PageController::class, 'show'])->name('pages.show.en');
});
