<?php

declare(strict_types=1);

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateCategoriesTable extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique()->comment('分类名称');
            $table->string('slug', 50)->unique()->comment('URL别名');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->datetimes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
}
