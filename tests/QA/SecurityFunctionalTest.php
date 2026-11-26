<?php

use App\Models\BibleBook;
use App\Models\BibleVerse;
use App\Models\Favorite;
use App\Models\Note;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

uses(TestCase::class)->use(RefreshDatabase::class);

beforeEach(function () {
    BibleBook::query()->create([
        'code' => 'GEN',
        'name' => 'Génesis',
        'testament' => 'OT',
        'order' => 1,
    ]);

    BibleVerse::query()->create([
        'book_code' => 'GEN',
        'translation' => 'spa_rvg',
        'chapter' => 1,
        'verse' => 1,
        'text' => 'En el principio creó Dios los cielos y la tierra.',
    ]);

    $this->attacker = User::factory()->create(['slug' => 'atacante-qa']);
    $this->victim = User::factory()->create(['slug' => 'victima-qa']);
});

function securityNotePayload(string $body = 'Notea del atacante'): array
{
    return [
        'book_code' => 'GEN',
        'chapter' => 1,
        'verse' => 1,
        'body' => $body,
    ];
}

test('extra profile fields cannot be mass assigned', function () {
    $verifiedAt = $this->attacker->email_verified_at;
    $slug = $this->attacker->slug;

    $this->actingAs($this->attacker)->patch('/settings/profile', [
        'name' => 'Nombre legítimo',
        'email' => $this->attacker->email,
        'slug' => 'slug-manipulado',
        'church' => 'Iglesia falsa',
        'email_verified_at' => now()->toDateTimeString(),
        'is_admin' => true,
        'role' => 'admin',
    ])->assertRedirect('/settings/profile');

    $this->attacker->refresh();

    expect($this->attacker->name)->toBe('Nombre legítimo')
        ->and($slug)->toBe('atacante-qa')
        ->and($this->attacker->slug)->toBe('nombre-legitimo')
        ->and($this->attacker->slug)->not->toBe('slug-manipulado')
        ->and($this->attacker->email_verified_at?->toDateTimeString())->toBe($verifiedAt?->toDateTimeString())
        ->and($this->attacker->church)->not->toBe('Iglesia falsa')
        ->and($this->attacker->getAttributes())->not->toHaveKeys(['is_admin', 'role']);
});

test('a post ignores client supplied ownership and relation fields', function () {
    $this->actingAs($this->attacker)->post('/posts', [
        'body' => 'Publicación legítima',
        'kind' => 'versiculo',
        'book_code' => 'GEN',
        'chapter' => 1,
        'verse' => 1,
        'user_id' => $this->victim->id,
        'parent_id' => 999,
        'rebible_of_id' => 999,
        'book_name' => 'Nombre manipulado',
    ])->assertRedirect();

    $post = Post::query()->firstOrFail();

    expect($post->user_id)->toBe($this->attacker->id)
        ->and($post->parent_id)->toBeNull()
        ->and($post->rebible_of_id)->toBeNull()
        ->and($post->book_name)->toBe('Génesis')
        ->and($post->body)->toBe('Publicación legítima');
});

test('a post cannot choose the automatic rebible kind', function () {
    $this->actingAs($this->attacker)->postJson('/posts', [
        'body' => 'Quiero parecer una rebibleada',
        'kind' => 'rebibleada',
    ])->assertStatus(422)->assertJsonValidationErrors('kind');

    expect(Post::query()->count())->toBe(0);
});

test('a note cannot be assigned to another user', function () {
    $this->actingAs($this->attacker)->putJson('/api/notes', array_merge(securityNotePayload(), [
        'user_id' => $this->victim->id,
        'book_name' => 'Nombre manipulado',
        'text' => '<script>alert(1)</script>',
    ]))->assertOk();

    $note = Note::query()->firstOrFail();

    expect($note->user_id)->toBe($this->attacker->id)
        ->and($note->book_name)->toBe('Génesis')
        ->and($note->body)->toBe('Notea del atacante')
        ->and($note->getAttributes())->not->toHaveKey('text');
});

test('a favorite cannot be assigned to another user', function () {
    $this->actingAs($this->attacker)->postJson('/api/favorites', [
        'book_code' => 'GEN',
        'chapter' => 1,
        'verse' => 1,
        'user_id' => $this->victim->id,
        'book_name' => 'Nombre manipulado',
        'text' => '<script>alert(1)</script>',
    ])->assertOk();

    $favorite = Favorite::query()->firstOrFail();

    expect($favorite->user_id)->toBe($this->attacker->id)
        ->and($favorite->book_name)->toBe('Génesis')
        ->and($favorite->text)->toBe('En el principio creó Dios los cielos y la tierra.');
});

test('a user cannot overwrite another users note for the same verse', function () {
    $this->actingAs($this->victim)->putJson('/api/notes', securityNotePayload('Nota privada'))->assertOk();
    $this->actingAs($this->attacker)->putJson('/api/notes', securityNotePayload('Nota inyectada'))->assertOk();

    expect(Note::query()->count())->toBe(2)
        ->and($this->victim->notes()->first()->body)->toBe('Nota privada')
        ->and($this->attacker->notes()->first()->body)->toBe('Nota inyectada');
});

test('script payloads round trip as plain strings in api responses', function () {
    $payload = '<script>alert("xss")</script>';
    $this->actingAs($this->attacker)->putJson('/api/notes', securityNotePayload($payload))->assertOk();

    $this->getJson('/api/notes')
        ->assertOk()
        ->assertJsonPath('0.body', $payload)
        ->assertJsonPath('0.book_name', 'Génesis');
});

test('the frontend never renders raw html with v-html or innerHTML', function () {
    $files = Finder::create()
        ->in(base_path('resources/js'))
        ->name('*.vue')
        ->name('*.js')
        ->name('*.ts');

    $offenders = [];
    foreach ($files as $file) {
        $contents = $file->getContents();
        if (str_contains($contents, 'v-html') || str_contains($contents, 'innerHTML')) {
            $offenders[] = str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname());
        }
    }

    expect($offenders)->toBe([]);
});
