<?php

declare(strict_types=1);

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateCommentsTable extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->integer('article_id')->comment('文章ID');
            $table->integer('parent_id')->default(0)->comment('父评论ID');
            $table->string('nickname', 50)->comment('评论者昵称');
            $table->string('email', 100)->comment('评论者邮箱');
            $table->text('content')->comment('评论内容');
            $table->tinyInteger('status')->default(0)->comment('状态（0=待审核，1=通过，2=拒绝）');
            $table->string('ip_address', 50)->nullable()->comment('IP地址');
            $table->dateTime('created_at')->nullable()->comment('评论时间');
            
            $table->index('article_id');
            $table->index('status');
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
}
