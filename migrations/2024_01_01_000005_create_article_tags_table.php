<?php

declare(strict_types=1);

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateArticleTagsTable extends Migration
{
    public function up(): void
    {
        Schema::create('article_tags', function (Blueprint $table) {
            $table->integer('article_id')->comment('文章ID');
            $table->integer('tag_id')->comment('标签ID');
            
            $table->primary(['article_id', 'tag_id']);
            $table->index('article_id');
            $table->index('tag_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_tags');
    }
}
