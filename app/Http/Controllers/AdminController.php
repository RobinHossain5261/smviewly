<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SiteSetting;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    /**
     * Show admin login form.
     */
    public function loginForm()
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->isAdmin() ? 'admin.dashboard' : 'home');
        }
        return view('admin.login');
    }

    /**
     * Process admin login.
     */
    public function login(Request $request)
    {
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $request->session()->forget('url.intended');
            return redirect()->route(Auth::user()->isAdmin() ? 'admin.dashboard' : 'home');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Process admin logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }

    /**
     * Dashboard Overview metrics.
     */
    public function index()
    {
        $postsCount = Post::count();
        $categoriesCount = Category::count();
        $publishedCount = Post::published()->count();
        $faqsCount = \App\Models\Faq::count();
        
        // Load recent posts
        $recentPosts = Post::with('category')->latest()->take(5)->get();

        $contactsCount = \App\Models\Contact::count();
        $recentContacts = \App\Models\Contact::latest()->take(5)->get();
        return view('admin.dashboard', compact('postsCount', 'categoriesCount', 'publishedCount', 'faqsCount', 'recentPosts', 'contactsCount', 'recentContacts'));
    }

    /**
     * Show Settings form.
     */
    public function settings()
    {
        $settings = SiteSetting::pluck('value', 'key')->all();
        return view('admin.settings', compact('settings'));
    }

    /**
     * Save updated Site Settings.
     */
    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'brand_name' => 'required|string|max:50',
            'primary_color' => 'required|string|max:7', // Hex validation
            'secondary_color' => 'required|string|max:7',
            'meta_title' => 'required|string|max:150',
            'meta_description' => 'required|string|max:300',
            'contact_email' => 'required|email|max:100',
            'contact_phone' => 'nullable|string|max:40',
            'linkedin_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg,gif,webp|max:2048',
            'rapidapi_key' => 'nullable|string|max:100',
            'social_api_base_url' => 'nullable|string|max:255',
            'social_api_token' => 'nullable|string|max:255',
            'social_api_use_proxy' => 'nullable|in:0,1',
            'custom_head_scripts' => 'nullable|string',
            'about_text' => 'nullable|string',
            'home_blog_title' => 'nullable|string|max:200',
            'home_blog_description' => 'nullable|string',
        ]);

        $data['social_api_use_proxy'] = $request->boolean('social_api_use_proxy') ? '1' : '0';

        foreach ($data as $key => $value) {
            if ($key === 'logo') continue;
            if ($key === 'social_api_token' && ($value === null || $value === '')) continue;
            SiteSetting::set($key, $value ?? '');
        }

        // Logo upload processing
        if ($request->hasFile('logo')) {
            // Delete old logo if it exists
            $oldLogo = SiteSetting::get('logo_path');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }

            $path = $request->file('logo')->store('uploads', 'public');
            SiteSetting::set('logo_path', $path);
        }

        SiteSetting::clearCache();

        return back()->with('success', 'Site settings updated successfully!');
    }

    /*
     |--------------------------------------------------------------------------
     | Blog Posts CRUD
     |--------------------------------------------------------------------------
     */

    public function postsIndex()
    {
        $posts = Post::with('category')->latest()->paginate(10);
        return view('admin.posts.index', compact('posts'));
    }

    public function postsCreate()
    {
        $categories = Category::all();
        return view('admin.posts.create', compact('categories'));
    }

    public function postsStore(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:200|unique:posts,title',
            'content' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:published,draft',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data['slug'] = Str::slug($data['title']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('posts', 'public');
        }

        Post::create($data);

        $message = ($data['status'] ?? '') === 'published'
            ? 'Blog post published. It is now in the sitemap.'
            : 'Draft saved. Publish it to add the post to the sitemap.';

        return redirect()->route('admin.posts.index')->with('success', $message);
    }

    public function postsEdit($id)
    {
        $post = Post::findOrFail($id);
        $categories = Category::all();
        return view('admin.posts.edit', compact('post', 'categories'));
    }

    public function postsUpdate(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:200|unique:posts,title,' . $post->id,
            'content' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:published,draft',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data['slug'] = Str::slug($data['title']);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($post->image_path && Storage::disk('public')->exists($post->image_path)) {
                Storage::disk('public')->delete($post->image_path);
            }
            $data['image_path'] = $request->file('image')->store('posts', 'public');
        }

        $post->update($data);

        $message = ($data['status'] ?? $post->status) === 'published'
            ? 'Blog post updated and listed in the sitemap.'
            : 'Draft updated. Publish it to add the post to the sitemap.';

        return redirect()->route('admin.posts.index')->with('success', $message);
    }

    public function postsDestroy($id)
    {
        $post = Post::findOrFail($id);

        if ($post->image_path && Storage::disk('public')->exists($post->image_path)) {
            Storage::disk('public')->delete($post->image_path);
        }

        $post->delete();

        return back()->with('success', 'Blog post deleted and removed from the sitemap.');
    }

    /*
     |--------------------------------------------------------------------------
     | Categories CRUD
     |--------------------------------------------------------------------------
     */

    public function categoriesIndex()
    {
        $categories = Category::withCount('posts')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function categoriesStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50|unique:categories,name',
        ]);

        $data['slug'] = Str::slug($data['name']);

        Category::create($data);

        return back()->with('success', 'Category created successfully!');
    }

    public function categoriesUpdate(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:50|unique:categories,name,' . $category->id,
        ]);

        $data['slug'] = Str::slug($data['name']);

        $category->update($data);

        return back()->with('success', 'Category updated successfully!');
    }

    public function categoriesDestroy($id)
    {
        $category = Category::findOrFail($id);
        
        // Prevent deleting category if it has posts (or cascade delete, depending on need)
        if ($category->posts()->count() > 0) {
            return back()->withErrors(['category_error' => 'Cannot delete category that contains active posts. Move posts first.']);
        }

        $category->delete();

        return back()->with('success', 'Category deleted successfully!');
    }

    /**
     * Show public sign in form.
     */
    public function signinForm()
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->isAdmin() ? 'admin.dashboard' : 'home');
        }
        return view('auth.signin');
    }

    /**
     * Process public sign in.
     */
    public function signin(Request $request)
    {
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $request->session()->forget('url.intended');
            return redirect()->route(Auth::user()->isAdmin() ? 'admin.dashboard' : 'home');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Show public sign up form.
     */
    public function signupForm()
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->isAdmin() ? 'admin.dashboard' : 'home');
        }
        return view('auth.signup');
    }

    /**
     * Process public sign up.
     */
    public function signup(Request $request)
    {
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users', \Illuminate\Validation\Rule::notIn(['super@admin.com'])],
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->route('home')->with('success', 'Welcome! Your account is ready.');
    }

    /*
     |--------------------------------------------------------------------------
     | FAQ CRUD
     |--------------------------------------------------------------------------
     */

    public function faqsIndex()
    {
        $faqs = \App\Models\Faq::orderBy('order_index')->get();
        return view('admin.faqs.index', compact('faqs'));
    }

    public function faqsStore(Request $request)
    {
        $data = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'order_index' => 'required|integer',
        ]);

        \App\Models\Faq::create($data);

        return back()->with('success', 'FAQ added successfully!');
    }

    public function faqsUpdate(Request $request, $id)
    {
        $faq = \App\Models\Faq::findOrFail($id);

        $data = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'order_index' => 'required|integer',
        ]);

        $faq->update($data);

        return back()->with('success', 'FAQ updated successfully!');
    }

    public function faqsDestroy($id)
    {
        $faq = \App\Models\Faq::findOrFail($id);
        $faq->delete();

        return back()->with('success', 'FAQ deleted successfully!');
    }

    /*
     |--------------------------------------------------------------------------
     | Contacts Manager
     |--------------------------------------------------------------------------
     */

    public function contactsIndex(Request $request)
    {
        $search = $request->validate(['q' => 'nullable|string|max:200'])['q'] ?? '';
        $contacts = \App\Models\Contact::query()
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('subject', 'like', '%'.$search.'%')
                    ->orWhere('message', 'like', '%'.$search.'%');
            }))->latest()->paginate(20)->withQueryString();
        return view('admin.contacts.index', compact('contacts'));
    }

    public function contactsBulkDestroy(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:20'],
            'ids.*' => ['required', 'integer', 'distinct', 'exists:contacts,id'],
        ]);

        $count = \App\Models\Contact::whereIn('id', $data['ids'])->delete();

        return back()->with('success', $count.' contact messages deleted successfully.');
    }

    public function contactsDestroy($id)
    {
        $contact = \App\Models\Contact::findOrFail($id);
        $contact->delete();

        return back()->with('success', 'Contact message deleted successfully!');
    }
}
