<?php

declare(strict_types=1);

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateArticlesTable extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200)->comment('文章标题');
            $table->string('slug', 200)->unique()->comment('URL别名');
            $table->text('summary')->nullable()->comment('文章摘要');
            $table->longText('content')->comment('文章内容');
            $table->string('cover_image', 255)->nullable()->comment('封面图路径');
            $table->integer('category_id')->comment('分类ID');
            $table->tinyInteger('status')->default(0)->comment('状态（0=草稿，1=发布）');
            $table->integer('view_count')->default(0)->comment('阅读量');
            $table->string('seo_title', 200)->nullable()->comment('SEO标题');
            $table->string('seo_description', 500)->nullable()->comment('SEO描述');
            $table->datetimes();
            
            $table->index('category_id');
            $table->index('status');
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
}
