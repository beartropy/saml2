<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beartropy_saml2_idps', function (Blueprint $table) {
            $table->text('entity_id')->change();
            $table->text('sso_url')->change();
            $table->text('slo_url')->nullable()->change();
            $table->text('metadata_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('beartropy_saml2_idps', function (Blueprint $table) {
            $table->string('entity_id')->change();
            $table->string('sso_url')->change();
            $table->string('slo_url')->nullable()->change();
            $table->string('metadata_url')->nullable()->change();
        });
    }
};
