<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/sitemap-pages.xml', [SitemapController::class, 'pages'])->name('sitemap.pages');
Route::get('/sitemap-posts.xml', [SitemapController::class, 'posts'])->name('sitemap.posts');
Route::match(['get', 'post'], '/search', [HomeController::class, 'search'])
    ->middleware('throttle:8,1')
    ->name('viewer.search');
Route::post('/search-preview', [HomeController::class, 'searchPreview'])
    ->middleware('throttle:8,1')
    ->name('viewer.search-preview');
Route::get('/download', [HomeController::class, 'download'])->name('viewer.download');
Route::get('/media', [HomeController::class, 'media'])->name('viewer.media');

// Blog Routes
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

// Static Content pages
Route::get('/about', function () {
    return view('pages.about');
})->name('about');

Route::get('/contact', function () {
    return view('pages.contact');
})->name('contact');
Route::get('/faqs', function () {
    return view('pages.faqs', ['questions' => \App\Models\Faq::orderBy('order_index')->orderBy('id')->get()]);
})->name('faqs');
Route::get('/tools', function () {
    return view('pages.tools');
})->name('tools');
Route::post('/contact', [HomeController::class, 'contactSubmit'])->name('contact.submit');
Route::get('/reload-captcha', [HomeController::class, 'reloadCaptcha']);

Route::get('/privacy-policy', function () {
    return view('pages.privacy');
})->name('privacy');

Route::get('/terms-of-use', function () {
    return view('pages.terms');
})->name('terms');

// Public Auth Routes
Route::get('/signin', [AdminController::class, 'signinForm'])->name('signin');
Route::post('/signin', [AdminController::class, 'signin'])->middleware('throttle:5,1')->name('signin.post');
Route::get('/signup', [AdminController::class, 'signupForm'])->name('signup');
Route::post('/signup', [AdminController::class, 'signup'])->middleware('throttle:5,1')->name('signup.post');

Route::post('/logout', [AdminController::class, 'logout'])->middleware('auth')->name('logout');

// Named login route for fallback authentication redirections
Route::get('/login', fn () => redirect()->route('signin'))->name('login');

Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
    // Auth
    Route::get('/login', [AdminController::class, 'loginForm'])->name('login');
    Route::post('/login', [AdminController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');

    // Dashboard & Settings (Protected)
    Route::group(['middleware' => ['auth', \App\Http\Middleware\EnsureAdmin::class]], function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');

        // Contacts Manager
        Route::get('/contacts', [AdminController::class, 'contactsIndex'])->name('contacts.index');
        Route::delete('/contacts', [AdminController::class, 'contactsBulkDestroy'])->name('contacts.bulk-destroy');
        Route::delete('/contacts/{id}', [AdminController::class, 'contactsDestroy'])->name('contacts.destroy');

        // Dynamic FAQs Manager
        Route::get('/faqs', [AdminController::class, 'faqsIndex'])->name('faqs.index');
        Route::post('/faqs', [AdminController::class, 'faqsStore'])->name('faqs.store');
        Route::post('/faqs/{id}', [AdminController::class, 'faqsUpdate'])->name('faqs.update');
        Route::delete('/faqs/{id}', [AdminController::class, 'faqsDestroy'])->name('faqs.destroy');

        // Blog Posts CRUD
        Route::get('/posts', [AdminController::class, 'postsIndex'])->name('posts.index');
        Route::get('/posts/create', [AdminController::class, 'postsCreate'])->name('posts.create');
        Route::post('/posts', [AdminController::class, 'postsStore'])->name('posts.store');
        Route::get('/posts/{id}/edit', [AdminController::class, 'postsEdit'])->name('posts.edit');
        Route::post('/posts/{id}', [AdminController::class, 'postsUpdate'])->name('posts.update');
        Route::delete('/posts/{id}', [AdminController::class, 'postsDestroy'])->name('posts.destroy');

        // Categories CRUD
        Route::get('/categories', [AdminController::class, 'categoriesIndex'])->name('categories.index');
        Route::post('/categories', [AdminController::class, 'categoriesStore'])->name('categories.store');
        Route::post('/categories/{id}', [AdminController::class, 'categoriesUpdate'])->name('categories.update');
        Route::delete('/categories/{id}', [AdminController::class, 'categoriesDestroy'])->name('categories.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Catch-all Sitemap Dynamic Tool Routing (Social media viewers)
|--------------------------------------------------------------------------
*/
Route::get('/{platform}/{tool?}', [HomeController::class, 'tool'])->name('viewer.tool');
