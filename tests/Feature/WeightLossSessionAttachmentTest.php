<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use App\Models\WeightLossPlan;
use App\Models\WeightLossSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WeightLossSessionAttachmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Patient $patient;
    protected WeightLossPlan $plan;
    protected WeightLossSession $session;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->admin = User::where('username', 'admin')->first();

        $this->patient = Patient::create([
            'name' => 'نيرة محمود مصطفى',
            'phone' => '01011223344',
            'gender' => 'female',
            'age' => 31,
        ]);

        $this->plan = WeightLossPlan::create([
            'patient_id' => $this->patient->id,
            'package_name' => 'باقة التخسيس المكثف',
            'total_sessions' => 6,
            'cost' => 1800,
            'status' => 'active',
        ]);

        $this->session = WeightLossSession::create([
            'patient_id' => $this->patient->id,
            'weight_loss_plan_id' => $this->plan->id,
            'session_number' => 1,
            'session_date' => now()->format('Y-m-d'),
            'weight' => 85.5,
            'height' => 165,
        ]);

        Storage::fake('public');
    }

    public function test_guest_cannot_upload_session_attachment(): void
    {
        $file = UploadedFile::fake()->image('inbody.jpg', 800, 600);

        $response = $this->post("/weight-loss-sessions/{$this->session->id}/attachment", [
            'attachment' => $file,
        ]);

        $response->assertRedirect('/login');
    }

    public function test_rejects_file_exceeding_size_limit(): void
    {
        // 25 MB file exceeds 20480 KB (20MB)
        $oversizedFile = UploadedFile::fake()->create('huge_scan.pdf', 25000, 'application/pdf');

        $response = $this->actingAs($this->admin)
            ->post("/weight-loss-sessions/{$this->session->id}/attachment", [
                'attachment' => $oversizedFile,
            ]);

        $response->assertSessionHasErrors('attachment');
        $this->assertFalse($this->session->fresh()->has_attachment);
    }

    public function test_can_upload_and_compress_image_attachment(): void
    {
        $largeImage = UploadedFile::fake()->image('inbody_report.jpg', 2400, 1800)->size(3500); // 3.5 MB

        $response = $this->actingAs($this->admin)
            ->post("/weight-loss-sessions/{$this->session->id}/attachment", [
                'attachment' => $largeImage,
            ]);

        $response->assertRedirect();
        $this->session->refresh();

        $this->assertTrue($this->session->has_attachment);
        $this->assertTrue($this->session->is_attachment_image);
        $this->assertEquals('image', $this->session->attachment_type);
        $this->assertEquals('inbody_report.jpg', $this->session->attachment_name);
        $this->assertNotNull($this->session->attachment_path);
        $this->assertGreaterThan(0, $this->session->attachment_size);
        $this->assertGreaterThanOrEqual($this->session->attachment_size, $this->session->original_size);

        Storage::disk('public')->assertExists($this->session->attachment_path);
    }

    public function test_can_upload_pdf_attachment(): void
    {
        $pdf = UploadedFile::fake()->create('diet_program.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->admin)
            ->post("/weight-loss-sessions/{$this->session->id}/attachment", [
                'attachment' => $pdf,
            ]);

        $response->assertRedirect();
        $this->session->refresh();

        $this->assertTrue($this->session->has_attachment);
        $this->assertTrue($this->session->is_attachment_pdf);
        $this->assertEquals('pdf', $this->session->attachment_type);
        $this->assertEquals('diet_program.pdf', $this->session->attachment_name);

        Storage::disk('public')->assertExists($this->session->attachment_path);
    }

    public function test_can_view_and_download_session_attachment(): void
    {
        $file = UploadedFile::fake()->image('scan.jpg', 600, 400);

        $this->actingAs($this->admin)->post("/weight-loss-sessions/{$this->session->id}/attachment", [
            'attachment' => $file,
        ]);

        $this->session->refresh();

        // Test view (inline stream)
        $viewResponse = $this->actingAs($this->admin)->get("/weight-loss-sessions/{$this->session->id}/attachment/view");
        $viewResponse->assertStatus(200);
        $this->assertStringContainsString('inline', $viewResponse->headers->get('Content-Disposition') ?? '');

        // Test download
        $downloadResponse = $this->actingAs($this->admin)->get("/weight-loss-sessions/{$this->session->id}/attachment/download");
        $downloadResponse->assertStatus(200);
        $this->assertStringContainsString('attachment', $downloadResponse->headers->get('Content-Disposition') ?? '');
    }

    public function test_can_delete_session_attachment(): void
    {
        $file = UploadedFile::fake()->image('delete_me.png', 500, 500);

        $this->actingAs($this->admin)->post("/weight-loss-sessions/{$this->session->id}/attachment", [
            'attachment' => $file,
        ]);

        $this->session->refresh();
        $filePath = $this->session->attachment_path;
        Storage::disk('public')->assertExists($filePath);

        // Delete via AJAX
        $deleteResponse = $this->actingAs($this->admin)
            ->deleteJson("/weight-loss-sessions/{$this->session->id}/attachment");

        $deleteResponse->assertStatus(200);
        $deleteResponse->assertJson(['success' => true]);

        $this->session->refresh();
        $this->assertFalse($this->session->has_attachment);
        $this->assertNull($this->session->attachment_path);
        Storage::disk('public')->assertMissing($filePath);
    }

    public function test_session_update_form_with_attachment_compresses_and_saves(): void
    {
        $file = UploadedFile::fake()->image('update_test.jpg', 1200, 900);

        $response = $this->actingAs($this->admin)
            ->put("/weight-loss-sessions/{$this->session->id}", [
                'session_date' => now()->format('Y-m-d'),
                'weight' => 84.0,
                'height' => 165,
                'attachment' => $file,
            ]);

        $response->assertRedirect();
        $this->session->refresh();

        $this->assertEquals(84.0, (float) $this->session->weight);
        $this->assertTrue($this->session->has_attachment);
        $this->assertEquals('update_test.jpg', $this->session->attachment_name);
        Storage::disk('public')->assertExists($this->session->attachment_path);
    }
}
