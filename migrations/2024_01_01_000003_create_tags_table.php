<?php

declare(strict_types=1);

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateTagsTable extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique()->comment('标签名称');
            $table->string('slug', 50)->unique()->comment('URL别名');
            $table->dateTime('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
}
