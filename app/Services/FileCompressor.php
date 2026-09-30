<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileCompressor
{
    /**
     * ضغط وحفظ الملف (صورة أو PDF) في القرص المحدد مع تقليص الحجم وتوفير المساحة
     *
     * @param UploadedFile $file الملف المرفوع
     * @param string $folder المجلد المستهدف (مثال: patient_attachments/1)
     * @param string $disk القرص (افتراضياً: public)
     * @param int $maxDimension أقصى بُعد للصورة بالبكسل (عرض أو ارتفاع)
     * @param int $quality جودة الضغط (من 1 إلى 100)
     * @return array [path, file_type, mime_type, file_size, original_size, compression_ratio]
     */
    public static function compressAndStore(
        UploadedFile $file,
        string $folder,
        string $disk = 'public',
        int $maxDimension = 1920,
        int $quality = 80
    ): array {
        $originalSize = $file->getSize();
        $extension = strtolower($file->getClientOriginalExtension());
        $mime = $file->getClientMimeType() ?: $file->getMimeType();
        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'webp']) || str_starts_with($mime, 'image/');

        // 1. إذا كان الملف صورة ومكتبة GD متوفرة
        if ($isImage && extension_loaded('gd')) {
            $compressedResult = static::compressImage($file, $folder, $disk, $maxDimension, $quality);
            if ($compressedResult) {
                return $compressedResult;
            }
        }

        // 2. إذا كان ملف PDF أو تعذر ضغط الصورة، يتم الحفظ القياسي
        $path = $file->store($folder, $disk);
        $finalSize = Storage::disk($disk)->size($path);

        return [
            'path'              => $path,
            'file_type'         => in_array($extension, ['pdf']) ? 'pdf' : ($isImage ? 'image' : 'file'),
            'mime_type'         => $mime,
            'file_size'         => $finalSize,
            'original_size'     => $originalSize,
            'compression_ratio' => 0.0,
        ];
    }

    /**
     * معالجة وضغط الصورة باستخدام مكتبة GD وتصغير أبعادها وحجمها
     */
    protected static function compressImage(
        UploadedFile $file,
        string $folder,
        string $disk,
        int $maxDimension,
        int $quality
    ): ?array {
        try {
            $filePath = $file->getRealPath();
            $imageInfo = @getimagesize($filePath);
            if (!$imageInfo) {
                return null;
            }

            [$width, $height, $imageType] = $imageInfo;

            // قراءة الصورة حسب نوعها
            $srcImage = match ($imageType) {
                IMAGETYPE_JPEG => @imagecreatefromjpeg($filePath),
                IMAGETYPE_PNG  => @imagecreatefrompng($filePath),
                IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($filePath) : null,
                default        => null,
            };

            if (!$srcImage) {
                return null;
            }

            // حساب الأبعاد الجديدة مع الحفاظ التام على النسبة (Aspect Ratio)
            $newWidth = $width;
            $newHeight = $height;

            if ($width > $maxDimension || $height > $maxDimension) {
                if ($width >= $height) {
                    $newWidth = $maxDimension;
                    $newHeight = (int) round(($height / $width) * $maxDimension);
                } else {
                    $newHeight = $maxDimension;
                    $newWidth = (int) round(($width / $height) * $maxDimension);
                }
            }

            // إنشاء الكانفاس الجديد بألوان حقيقية
            $canvas = imagecreatetruecolor($newWidth, $newHeight);

            // الحفاظ على الشفافية لـ PNG و WEBP
            if ($imageType === IMAGETYPE_PNG || $imageType === IMAGETYPE_WEBP) {
                imagealphablending($canvas, false);
                imagesavealpha($canvas, true);
                $transparent = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
                imagefilledrectangle($canvas, 0, 0, $newWidth, $newHeight, $transparent);
            } else {
                // خلفية بيضاء للـ JPEG
                $white = imagecolorallocate($canvas, 255, 255, 255);
                imagefilledrectangle($canvas, 0, 0, $newWidth, $newHeight, $white);
            }

            // إعادة رسم وتنعيم الصورة بجودة عالية
            imagecopyresampled($canvas, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // إنشاء اسم فريد للملف
            $ext = ($imageType === IMAGETYPE_PNG) ? 'png' : (($imageType === IMAGETYPE_WEBP) ? 'webp' : 'jpg');
            $filename = Str::random(40) . '.' . $ext;
            $tempFile = tempnam(sys_get_temp_dir(), 'clinic_comp_');

            // حفظ الصورة المضغوطة في ملف مؤقت
            if ($imageType === IMAGETYPE_PNG) {
                imagepng($canvas, $tempFile, 8); // PNG compression: 8
                $mimeType = 'image/png';
            } elseif ($imageType === IMAGETYPE_WEBP && function_exists('imagewebp')) {
                imagewebp($canvas, $tempFile, $quality);
                $mimeType = 'image/webp';
            } else {
                imagejpeg($canvas, $tempFile, $quality);
                $mimeType = 'image/jpeg';
            }

            imagedestroy($canvas);
            imagedestroy($srcImage);

            $originalSize = $file->getSize();
            $relativeDir = trim($folder, '/');
            $targetPath = $relativeDir . '/' . $filename;

            // حفظ الملف النهائي في التخزين المخصص
            $stream = fopen($tempFile, 'r');
            Storage::disk($disk)->put($targetPath, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
            @unlink($tempFile);

            $finalSize = Storage::disk($disk)->size($targetPath);
            $ratio = $originalSize > 0 ? round((($originalSize - $finalSize) / $originalSize) * 100, 1) : 0.0;

            return [
                'path'              => $targetPath,
                'file_type'         => 'image',
                'mime_type'         => $mimeType,
                'file_size'         => $finalSize,
                'original_size'     => $originalSize,
                'compression_ratio' => max(0.0, $ratio),
            ];
        } catch (\Throwable $e) {
            // في حالة حدوث أي طارئ يتم الرجوع للحفظ المباشر
            return null;
        }
    }
}