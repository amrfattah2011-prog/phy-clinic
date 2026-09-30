<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\PatientAttachment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PatientAttachmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->admin = User::where('username', 'admin')->first();

        $this->patient = Patient::create([
            'name' => 'ياسين أحمد فؤاد',
            'phone' => '01012345678',
            'gender' => 'male',
            'age' => 28,
        ]);

        Storage::fake('public');
    }

    public function test_guest_cannot_upload_or_manage_attachments(): void
    {
        $response = $this->post("/patients/{$this->patient->id}/attachments", []);
        $response->assertRedirect('/login');
    }

    public function test_can_upload_image_attachment_with_date_and_notes(): void
    {
        $fakeImage = UploadedFile::fake()->image('spine_xray.jpg', 600, 600);
        $docDate = '2026-09-10';

        $response = $this->actingAs($this->admin)->post("/patients/{$this->patient->id}/attachments", [
            'title'         => 'أشعة عادية على الفقرات القطنية',
            'category'      => 'xray',
            'document_date' => $docDate,
            'notes'         => 'توضح استقامة خفيفة بالفقرات',
            'file'          => $fakeImage,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('patient_attachments', [
            'patient_id'    => $this->patient->id,
            'title'         => 'أشعة عادية على الفقرات القطنية',
            'category'      => 'xray',
            'file_type'     => 'image',
        ]);

        $attachment = PatientAttachment::where('patient_id', $this->patient->id)->first();
        $this->assertNotNull($attachment);
        $this->assertEquals($docDate, $attachment->document_date->format('Y-m-d'));
        Storage::disk('public')->assertExists($attachment->file_path);

        // Verify patient profile displays the attachment
        $showResponse = $this->actingAs($this->admin)->get("/patients/{$this->patient->id}?tab=attachments");
        $showResponse->assertStatus(200);
        $showResponse->assertSee('أشعة عادية على الفقرات القطنية');
        $showResponse->assertSee($docDate);
    }

    public function test_image_is_compressed_and_resized_to_save_storage(): void
    {
        // 1. Create a large 2500x1500 image (exceeds Full HD 1920)
        $largeImage = UploadedFile::fake()->image('large_mri_scan.jpg', 2500, 1500);

        $response = $this->actingAs($this->admin)->post("/patients/{$this->patient->id}/attachments", [
            'title'         => 'رنين مغناطيسي عالي الدقة',
            'category'      => 'xray',
            'document_date' => '2026-09-11',
            'file'          => $largeImage,
        ]);

        $response->assertRedirect();

        $attachment = PatientAttachment::where('title', 'رنين مغناطيسي عالي الدقة')->first();
        $this->assertNotNull($attachment);
        $this->assertNotNull($attachment->original_size);
        $this->assertGreaterThan(0, $attachment->file_size);

        // Verify image on disk was resized to max 1920
        $storedFullPath = Storage::disk('public')->path($attachment->file_path);
        $sizeInfo = getimagesize($storedFullPath);
        $this->assertNotEmpty($sizeInfo);
        $this->assertLessThanOrEqual(1920, $sizeInfo[0]); // Width <= 1920
        $this->assertLessThanOrEqual(1920, $sizeInfo[1]); // Height <= 1920
    }

    public function test_can_upload_pdf_attachment_with_date(): void
    {
        $fakePdf = UploadedFile::fake()->create('medical_report.pdf', 300, 'application/pdf');
        $docDate = '2026-09-12';

        $response = $this->actingAs($this->admin)->post("/patients/{$this->patient->id}/attachments", [
            'title'         => 'تقرير استشاري جراحة العظام',
            'category'      => 'report',
            'document_date' => $docDate,
            'notes'         => 'توصية بالعلاج الطبيعي التحفظي لمدة شهر',
            'file'          => $fakePdf,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('patient_attachments', [
            'patient_id'    => $this->patient->id,
            'title'         => 'تقرير استشاري جراحة العظام',
            'category'      => 'report',
            'file_type'     => 'pdf',
        ]);

        $attachment = PatientAttachment::where('category', 'report')->first();
        $this->assertTrue($attachment->is_pdf);
        $this->assertFalse($attachment->is_image);
        $this->assertEquals($docDate, $attachment->document_date->format('Y-m-d'));
    }

    public function test_can_preview_and_download_attachment(): void
    {
        $file = UploadedFile::fake()->image('test_doc.png', 200, 200);
        $path = $file->store('patient_attachments/' . $this->patient->id, 'public');

        $attachment = $this->patient->attachments()->create([
            'title'              => 'تحليل سرعة الترسيب ESR',
            'category'           => 'lab',
            'document_date'      => '2026-09-01',
            'file_path'          => $path,
            'original_name'      => 'test_doc.png',
            'file_type'          => 'image',
            'mime_type'          => 'image/png',
            'file_size'          => 5000,
            'created_by_user_id' => $this->admin->id,
        ]);

        // Preview / stream route
        $viewResponse = $this->actingAs($this->admin)->get("/patients/{$this->patient->id}/attachments/{$attachment->id}/view");
        $viewResponse->assertStatus(200);

        // Download route
        $downloadResponse = $this->actingAs($this->admin)->get("/patients/{$this->patient->id}/attachments/{$attachment->id}/download");
        $downloadResponse->assertStatus(200);
    }

    public function test_can_delete_attachment(): void
    {
        $file = UploadedFile::fake()->create('delete_me.pdf', 100, 'application/pdf');
        $path = $file->store('patient_attachments/' . $this->patient->id, 'public');

        $attachment = $this->patient->attachments()->create([
            'title'              => 'مرفق مؤقت للحذف',
            'category'           => 'general',
            'document_date'      => '2026-09-15',
            'file_path'          => $path,
            'original_name'      => 'delete_me.pdf',
            'file_type'          => 'pdf',
            'mime_type'          => 'application/pdf',
            'file_size'          => 2000,
            'created_by_user_id' => $this->admin->id,
        ]);

        Storage::disk('public')->assertExists($path);

        $response = $this->actingAs($this->admin)->delete("/patients/{$this->patient->id}/attachments/{$attachment->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('patient_attachments', ['id' => $attachment->id]);
        Storage::disk('public')->assertMissing($path);
    }
}