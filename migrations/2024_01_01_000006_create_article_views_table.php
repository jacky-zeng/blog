<?php

declare(strict_types=1);

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateArticleViewsTable extends Migration
{
    public function up(): void
    {
        Schema::create('article_views', function (Blueprint $table) {
            $table->id();
            $table->integer('article_id')->comment('文章ID');
            $table->string('ip_address', 50)->comment('IP地址');
            $table->string('user_agent', 500)->nullable()->comment('浏览器User-Agent');
            $table->dateTime('created_at')->nullable()->comment('访问时间');
            
            $table->index('article_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_views');
    }
}
