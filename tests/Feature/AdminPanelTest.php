<?php

use Beartropy\Saml2\Models\Saml2Idp;
use Illuminate\Foundation\Auth\User as Authenticatable;

// Create a minimal user model for testing
class TestUser extends Authenticatable
{
    protected $table = 'users';
    protected $guarded = [];
}

beforeEach(function () {
    // Create users table for auth
    $this->app['db']->connection()->getSchemaBuilder()->create('users', function ($table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->string('password')->default('');
        $table->timestamps();
    });

    $this->user = TestUser::create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
    ]);
});

test('admin dashboard requires authentication', function () {
    // Define a login route so the auth middleware can redirect
    \Illuminate\Support\Facades\Route::get('/login', fn () => 'login')->name('login');

    $response = $this->get('/saml2/admin');

    $response->assertRedirect('/login');
});

test('admin dashboard accessible when authenticated', function () {
    $response = $this->actingAs($this->user)->get('/saml2/admin');

    $response->assertStatus(200);
});

test('create IDP via admin panel', function () {
    $response = $this->actingAs($this->user)->post('/saml2/admin/idp', [
        'idp_key' => 'new-idp',
        'idp_name' => 'New IDP',
        'entity_id' => 'https://new-idp.example.com',
        'sso_url' => 'https://new-idp.example.com/sso',
        'x509_cert' => 'MIICpDCCAYwCCQ',
    ]);

    $response->assertRedirect(route('saml2.admin.index'));

    $this->assertDatabaseHas('beartropy_saml2_idps', [
        'key' => 'new-idp',
        'name' => 'New IDP',
    ]);
});

test('update IDP via admin panel', function () {
    $idp = Saml2Idp::create([
        'key' => 'update-test',
        'name' => 'Original',
        'entity_id' => 'https://idp.example.com',
        'sso_url' => 'https://idp.example.com/sso',
        'x509_cert' => 'cert',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)->put("/saml2/admin/idp/{$idp->id}", [
        'idp_key' => 'update-test',
        'idp_name' => 'Updated Name',
        'entity_id' => 'https://idp.example.com',
        'sso_url' => 'https://idp.example.com/sso',
        'x509_cert' => 'cert',
    ]);

    $response->assertRedirect(route('saml2.admin.index'));

    $idp->refresh();
    expect($idp->name)->toBe('Updated Name');
});

test('key immutability enforced on update', function () {
    $idp = Saml2Idp::create([
        'key' => 'immutable-key',
        'name' => 'Test',
        'entity_id' => 'https://idp.example.com',
        'sso_url' => 'https://idp.example.com/sso',
        'x509_cert' => 'cert',
        'is_active' => true,
    ]);

    $this->actingAs($this->user)->put("/saml2/admin/idp/{$idp->id}", [
        'idp_key' => 'changed-key',
        'idp_name' => 'Updated',
        'entity_id' => 'https://idp.example.com',
        'sso_url' => 'https://idp.example.com/sso',
        'x509_cert' => 'cert',
    ]);

    $idp->refresh();
    // Key should remain unchanged (model-level protection)
    expect($idp->key)->toBe('immutable-key');
});

test('delete IDP via admin panel', function () {
    $idp = Saml2Idp::create([
        'key' => 'delete-me',
        'name' => 'Delete Me',
        'entity_id' => 'https://idp.example.com',
        'sso_url' => 'https://idp.example.com/sso',
        'x509_cert' => 'cert',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)->delete("/saml2/admin/idp/{$idp->id}");

    $response->assertRedirect(route('saml2.admin.index'));
    $this->assertDatabaseMissing('beartropy_saml2_idps', ['key' => 'delete-me']);
});

test('toggle IDP status', function () {
    $idp = Saml2Idp::create([
        'key' => 'toggle-test',
        'name' => 'Toggle',
        'entity_id' => 'https://idp.example.com',
        'sso_url' => 'https://idp.example.com/sso',
        'x509_cert' => 'cert',
        'is_active' => true,
    ]);

    $this->actingAs($this->user)->post("/saml2/admin/idp/{$idp->id}/toggle");

    $idp->refresh();
    expect($idp->is_active)->toBeFalse();

    $this->actingAs($this->user)->post("/saml2/admin/idp/{$idp->id}/toggle");

    $idp->refresh();
    expect($idp->is_active)->toBeTrue();
});

test('validation rejects missing required fields', function () {
    $response = $this->actingAs($this->user)->post('/saml2/admin/idp', [
        'idp_key' => '',
        'idp_name' => '',
        'entity_id' => '',
        'sso_url' => '',
        'x509_cert' => '',
    ]);

    $response->assertSessionHasErrors(['idp_key', 'idp_name', 'entity_id', 'sso_url', 'x509_cert']);
});

test('validation rejects invalid URL for sso_url', function () {
    $response = $this->actingAs($this->user)->post('/saml2/admin/idp', [
        'idp_key' => 'test',
        'idp_name' => 'Test',
        'entity_id' => 'https://idp.example.com',
        'sso_url' => 'not-a-url',
        'x509_cert' => 'cert',
    ]);

    $response->assertSessionHasErrors(['sso_url']);
});

test('validation rejects duplicate key', function () {
    Saml2Idp::create([
        'key' => 'existing',
        'name' => 'Existing',
        'entity_id' => 'https://idp.example.com',
        'sso_url' => 'https://idp.example.com/sso',
        'x509_cert' => 'cert',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)->post('/saml2/admin/idp', [
        'idp_key' => 'existing',
        'idp_name' => 'Duplicate',
        'entity_id' => 'https://idp2.example.com',
        'sso_url' => 'https://idp2.example.com/sso',
        'x509_cert' => 'cert',
    ]);

    $response->assertSessionHasErrors(['idp_key']);
});
