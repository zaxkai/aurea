<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('habits')) {
            Schema::create('habits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('unit')->nullable();
                $table->unsignedInteger('target_value');
                $table->enum('type', ['checklist', 'progress'])->default('progress');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (Schema::hasColumn('habits', 'daily_target')) {
            if (Schema::hasColumn('habits', 'target_value')) {
                DB::table('habits')->update([
                    'target_value' => DB::raw('daily_target'),
                ]);
            } else {
                Schema::table('habits', function (Blueprint $table) {
                    $table->renameColumn('daily_target', 'target_value');
                });
            }
        }

        Schema::table('habits', function (Blueprint $table) {
            if (! Schema::hasColumn('habits', 'name')) {
                $table->string('name');
            }
            if (! Schema::hasColumn('habits', 'unit')) {
                $table->string('unit')->nullable();
            }
            if (! Schema::hasColumn('habits', 'target_value')) {
                $table->unsignedInteger('target_value')->default(1);
            }
            if (! Schema::hasColumn('habits', 'type')) {
                $table->enum('type', ['checklist', 'progress'])->default('progress');
            }
            if (! Schema::hasColumn('habits', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });

        if (Schema::hasColumn('habits', 'daily_target') && ! Schema::hasColumn('habits', 'target_value')) {
            Schema::table('habits', function (Blueprint $table) {
                $table->renameColumn('daily_target', 'target_value');
            });
        }

        if (! Schema::hasTable('habit_logs')) {
            Schema::create('habit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('habit_id')->constrained()->cascadeOnDelete();
                $table->date('log_date');
                $table->unsignedInteger('value_logged')->default(0);
                $table->timestamps();
                $table->unique(['habit_id', 'log_date']);
            });
        }

        if (Schema::hasColumn('habit_logs', 'progress')) {
            if (Schema::hasColumn('habit_logs', 'value_logged')) {
                DB::table('habit_logs')->update([
                    'value_logged' => DB::raw('progress'),
                ]);
            } else {
                Schema::table('habit_logs', function (Blueprint $table) {
                    $table->renameColumn('progress', 'value_logged');
                });
            }
        }

        Schema::table('habit_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('habit_logs', 'value_logged')) {
                $table->unsignedInteger('value_logged')->default(0);
            }
        });

        if (! Schema::hasTable('check_ins')) {
            Schema::create('check_ins', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->enum('mood', ['energetic', 'calm', 'neutral', 'stressed', 'exhausted']);
                $table->integer('who5_score')->nullable();
                $table->decimal('sleep_duration', 4, 2)->nullable();
                $table->integer('physical_activity_duration')->nullable();
                $table->decimal('screen_time_duration', 4, 2)->nullable();
                $table->integer('wellbeing_index')->nullable();
                $table->text('ai_insight')->nullable();
                $table->text('note')->nullable();
                $table->date('check_in_date');
                $table->timestamps();
                $table->unique(['user_id', 'check_in_date']);
            });
        }

        if (! Schema::hasTable('trees')) {
            Schema::create('trees', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
                $table->unsignedTinyInteger('growth_percentage')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        throw new LogicException('This data-preserving migration cannot be rolled back safely. Apply a forward migration instead.');
    }
};
