<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'tasks';

    /**
     * Run the migrations.
     * @table tasks
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id')->unsigned();
            $table->string('author_id', 45)->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->string('confidential', 45)->nullable();
            $table->text('description')->nullable();
            $table->dateTime('due_date')->nullable();
            $table->integer('iid')->nullable();
            $table->dateTime('last_edited_at')->nullable();
            $table->integer('last_edited_by_id')->nullable();
            $table->integer('milestone_id')->nullable();
            $table->integer('moved_to_id')->nullable();
            $table->integer('project_id')->nullable();
            $table->bigInteger('relative_position')->nullable();
            $table->string('state', 45)->nullable();
            $table->integer('time_estimate')->nullable();
            $table->string('title', 245)->nullable();
            $table->string('url', 245)->nullable();
            $table->integer('total_time_spent')->nullable();
            $table->string('human_total_time_spent', 45)->nullable();
            $table->string('human_time_estimate', 45)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
     public function down()
     {
       Schema::dropIfExists($this->set_schema_table);
     }
}
