<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactSubmissionController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\SiteController;
use App\Services\SiteSeo;
use Illuminate\Support\Facades\Route;

Route::get('/robots.txt', [SeoController::class, 'robots'])->name('seo.robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');
Route::get('/site-pages-sitemap.xml', [SeoController::class, 'sitemap'])->defaults('section', 'pages')->name('seo.pages');
Route::get('/pages-sitemap.xml', [SeoController::class, 'sitemap'])->defaults('section', 'pages');
foreach (['' => 'es', 'en_' => 'en'] as $prefix => $language) {
    Route::get('/'.$prefix.'blog-posts-sitemap.xml', [SeoController::class, 'sitemap'])->defaults('section', 'posts')->defaults('locale', $language);
    Route::get('/'.$prefix.'blog-categories-sitemap.xml', [SeoController::class, 'sitemap'])->defaults('section', 'categories')->defaults('locale', $language);
}

Route::get('/', [SiteController::class, 'home'])->name('home');
Route::get('/performance-connections', [SiteController::class, 'performance'])->name('performance');
Route::get('/brand-consulting', [SiteController::class, 'brand'])->name('brand');
Route::get('/social-media-strategies', [SiteController::class, 'social'])->name('social');
Route::get('/experience-tech', [SiteController::class, 'experience'])->name('experience');
Route::get('/makitha', [SiteController::class, 'makitha'])->name('makitha');
Route::get('/casos', [SiteController::class, 'cases'])->name('cases');
Route::get('/nosotros', [SiteController::class, 'about'])->name('about');
Route::get('/contacto', [SiteController::class, 'contact'])->name('contact');
foreach (SiteSeo::GEO_PAGES as $geoPage) {
    Route::get('/'.$geoPage, [SiteController::class, 'geo'])->name($geoPage);
}
Route::post('/contacto', [ContactSubmissionController::class, 'store'])->middleware('throttle:6,1')->name('contact.store');

foreach (['' => 'blog.', '/en' => 'blog.en.'] as $prefix => $name) {
    Route::get($prefix.'/blog', [BlogController::class, 'index'])->name($name.'index');
    Route::get($prefix.'/blog/page/{page}', [BlogController::class, 'index'])->whereNumber('page')->name($name.'page');
    Route::get($prefix.'/blog/categories/{slug}', [BlogController::class, 'category'])->name($name.'category');
    Route::get($prefix.'/blog/categories/{slug}/page/{page}', [BlogController::class, 'category'])->whereNumber('page')->name($name.'category.page');
    Route::get($prefix.'/blog/search/{term}', [BlogController::class, 'search'])->name($name.'search');
    Route::get($prefix.'/blog/search/{term}/page/{page}', [BlogController::class, 'search'])->whereNumber('page')->name($name.'search.page');
    foreach (['tags', 'hashtags'] as $type) {
        Route::get($prefix.'/blog/'.$type.'/{slug}', [BlogController::class, 'tag'])->name($name.$type);
        Route::get($prefix.'/blog/'.$type.'/{slug}/page/{page}', [BlogController::class, 'tag'])->whereNumber('page')->name($name.$type.'.page');
    }
    Route::get($prefix.'/post/{slug}', [BlogController::class, 'post'])->name($name.'post');
}
Route::redirect('/nuestrosservicios', '/#servicios', 301);
Route::redirect('/solicitarservicios', '/contacto', 301);
Route::redirect('/solicitarservicios1', '/contacto', 301);
