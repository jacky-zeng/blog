<?php

declare(strict_types=1);

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateSettingsTable extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique()->comment('配置键名');
            $table->text('value')->nullable()->comment('配置值');
            $table->string('description', 255)->nullable()->comment('配置描述');
            $table->datetimes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
}
