<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Seeder;

class PicLiteBlogSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::firstOrCreate(
            ['slug' => 'creator-tools'],
            ['name' => 'Creator Tools'],
        );

        Post::firstOrCreate(
            ['slug' => 'piclite-resize-compress-images-for-social-media'],
            [
                'title' => 'PicLite: Resize and Compress Images for Social Media',
                'image_path' => 'images/piclite-cover.webp',
                'category_id' => $category->id,
                'status' => 'published',
                'content' => <<<'HTML'
<p>PicLite is an online image resizer and compressor for preparing photos for social posts, websites, and email. With its free browser tool, you can reduce image dimensions, choose an output format, and adjust JPEG or WebP quality before downloading the result.</p>
<p>If you are preparing a campaign image, a profile photo, or a visual for a blog, the <a href="https://piclite.co.uk/" target="_blank" rel="noopener noreferrer">PicLite image resizer and compressor</a> gives you a straightforward place to start. It is useful when your original file is larger than the size you actually need to publish.</p>
<h2>What the free tool offers</h2>
<p>The homepage accepts JPG, PNG, GIF, and WebP files. You can resize by pixel dimensions or a percentage, keep the aspect ratio locked, and export as JPEG, PNG, or WebP. PicLite states that this free resizer processes images on your device without a server upload or an account.</p>
<h2>Resizing and compression solve different problems</h2>
<p>Resizing changes the width and height of an image. Compression changes how its data is stored, sometimes trading fine detail for a smaller file. A large photograph may benefit from both: first reduce unnecessary dimensions, then compare a few quality settings. The best result depends on the source image and how you intend to use it.</p>
<p>For social media, choose dimensions that suit the placement you are preparing. A profile photo, landscape post, and portrait graphic have different needs. Resizing alone does not guarantee the right crop, so check the final composition and the platform preview before publishing.</p>
<h2>Prepare an image for your next post</h2>
<p>Select your image, enter the dimensions you need, and keep the aspect ratio locked to avoid stretching. Choose an output format, adjust quality where available, then download and inspect the result. Keep your original file so you can compare detail and readability.</p>
<ol><li>Start with a photo or graphic you own or have permission to edit.</li><li>Set the intended width and height, or use a percentage for a proportional reduction.</li><li>Choose an export format that your publishing destination supports.</li><li>Check small text, faces, and edges after changing quality.</li><li>Compare the output size and appearance before using the file.</li></ol>
<h2>Choose a format for the image you have</h2>
<p>JPEG is a practical choice for many photographs. PNG is useful for graphics that need transparency, while WebP can be an efficient option for compatible websites and publishing workflows. There is no single quality setting that suits every image. A detailed photo and a graphic containing small lettering may need different treatment.</p>
<h2>Understand the browser workflow</h2>
<p>PicLite describes the free tool as local browser processing. That distinction matters when comparing it with the separate cloud features available through an account. For the site's explanation of file handling and the resizing workflow, read the <a href="https://piclite.co.uk/faq" target="_blank" rel="noopener noreferrer">PicLite FAQ</a>.</p>
<h2>Browser resizing and account features</h2>
<p>The website also advertises account-based cloud optimization, a WordPress plugin, and API access. These are separate from the free browser resizer. If your workflow involves a website or ongoing optimization, review <a href="https://piclite.co.uk/pricing" target="_blank" rel="noopener noreferrer">PicLite's current plans and pricing</a> for the relevant limits and features.</p>
<p>WordPress users can also visit the <a href="https://piclite.co.uk/plugin/download" target="_blank" rel="noopener noreferrer">PicLite WordPress plugin page</a> for its available download and setup information. Choose the workflow that fits your publishing needs, and retain original images for future edits.</p>
HTML,
            ],
        );
    }
}
