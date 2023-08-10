<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('myob_configurations', function (Blueprint $table) {
            $table->id();
            $table->text('access_token');
            $table->text('refresh_token');
            $table->string('scope');
            $table->text('company_file_id')->nullable();
            $table->text('company_file_token')->nullable();
            $table->string('company_file_name')->nullable();
            $table->text('company_file_uri')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

};
