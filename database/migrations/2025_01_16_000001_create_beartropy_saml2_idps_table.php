<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('beartropy_saml2_idps', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('Unique slug identifier for the IDP');
            $table->string('name')->comment('Human-readable name');
            $table->string('entity_id')->comment('IDP Entity ID');
            $table->string('sso_url')->comment('Single Sign-On URL');
            $table->string('slo_url')->nullable()->comment('Single Logout URL');
            $table->text('x509_cert')->comment('IDP x509 certificate');
            $table->json('x509_cert_multi')->nullable()->comment('Multiple certificates for signing/encryption');
            $table->string('metadata_url')->nullable()->comment('URL to fetch/refresh IDP metadata');
            $table->json('metadata')->nullable()->comment('Additional configuration data');
            $table->json('attribute_mapping')->nullable()->comment('Custom SAML attribute mapping for this IDP');
            $table->boolean('is_active')->default(true)->comment('Whether this IDP is active');
            $table->timestamps();
            
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beartropy_saml2_idps');
    }
};
