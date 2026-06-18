<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Media\MediaAssetIndex;
use App\Models\Article;
use App\Models\MediaAsset;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MediaLibraryAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_media_library_from_chinese_sidebar(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->get(route('admin.media.index'))
            ->assertOk()
            ->assertSee('媒体库')
            ->assertSee('href="'.route('admin.media.index').'"', false);
    }

    public function test_admin_can_upload_safe_image_to_media_library(): void
    {
        Storage::fake('public');
        $admin = $this->superAdmin();
        $this->actingAs($admin);

        Livewire::test(MediaAssetIndex::class)
            ->set('file', UploadedFile::fake()->image('kyoto.jpg', 1200, 800))
            ->set('alt_text', '京都伏见稻荷鸟居')
            ->set('source_note', '内部摄影素材')
            ->call('upload')
            ->assertHasNoErrors()
            ->assertSee('图片已上传');

        $asset = MediaAsset::query()->where('alt_text', '京都伏见稻荷鸟居')->firstOrFail();

        Storage::disk('public')->assertExists($asset->path);
        $this->assertSame('内部摄影素材', $asset->source_note);
        $this->assertSame($admin->id, $asset->uploaded_by);
        $this->assertSame('public', $asset->disk);
    }

    public function test_non_admin_cannot_directly_upload_via_livewire_action(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(MediaAssetIndex::class)
            ->set('file', UploadedFile::fake()->image('blocked.jpg'))
            ->set('alt_text', '未授权图片')
            ->call('upload')
            ->assertForbidden();

        $this->assertDatabaseMissing('media_assets', [
            'alt_text' => '未授权图片',
        ]);
    }

    public function test_admin_cannot_delete_media_used_as_article_cover(): void
    {
        Storage::fake('public');
        $admin = $this->superAdmin();
        $asset = MediaAsset::factory()->create([
            'uploaded_by' => $admin->id,
            'path' => 'media/2026/06/used-cover.jpg',
        ]);
        Storage::disk('public')->put($asset->path, 'cover-bytes');
        Article::factory()->create([
            'author_id' => $admin->id,
            'cover_media_id' => $asset->id,
        ]);
        $this->actingAs($admin);

        Livewire::test(MediaAssetIndex::class)
            ->call('delete', $asset->id)
            ->assertSee('图片正在被内容使用，不能删除');

        $this->assertDatabaseHas('media_assets', [
            'id' => $asset->id,
        ]);
        Storage::disk('public')->assertExists($asset->path);
    }

    public function test_admin_can_delete_unreferenced_media(): void
    {
        Storage::fake('public');
        $admin = $this->superAdmin();
        $asset = MediaAsset::factory()->create([
            'uploaded_by' => $admin->id,
            'path' => 'media/2026/06/orphan.jpg',
        ]);
        Storage::disk('public')->put($asset->path, 'orphan-bytes');
        $this->actingAs($admin);

        Livewire::test(MediaAssetIndex::class)
            ->call('delete', $asset->id)
            ->assertSee('图片已删除');

        $this->assertDatabaseMissing('media_assets', [
            'id' => $asset->id,
        ]);
        Storage::disk('public')->assertMissing($asset->path);
    }

    public function test_admin_can_search_media_by_alt_and_path(): void
    {
        $admin = $this->superAdmin();
        MediaAsset::factory()->create([
            'uploaded_by' => $admin->id,
            'path' => 'media/2026/06/kiyomizu.jpg',
            'alt_text' => '清水寺舞台',
        ]);
        MediaAsset::factory()->create([
            'uploaded_by' => $admin->id,
            'path' => 'media/2026/06/fuji.jpg',
            'alt_text' => '富士山日出',
        ]);
        $this->actingAs($admin);

        Livewire::test(MediaAssetIndex::class)
            ->set('search', '清水寺')
            ->assertSee('清水寺舞台')
            ->assertDontSee('富士山日出')
            ->set('search', 'fuji')
            ->assertSee('media/2026/06/fuji.jpg')
            ->assertDontSee('清水寺舞台');
    }

    private function superAdmin(): User
    {
        $this->seed(RoleSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        return $admin;
    }
}
