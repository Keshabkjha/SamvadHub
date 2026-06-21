<?php

namespace App\Helpers;

use finfo;

class ImageHelper {
    /**
     * Handle and optimize uploaded image.
     */
    public static function handleUpload(array $file, string $type, int $maxDimension): array {
        $allowed_mime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size     = 5 * 1024 * 1024; // 5MB

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['status' => false, 'error' => 'Upload failed. Please try again.'];
        }
        if ($file['size'] > $max_size) {
            return ['status' => false, 'error' => 'File is too large. Maximum size is 5MB.'];
        }

        // Validate MIME type using finfo
        $finfo     = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($file['tmp_name']);
        if (!in_array($mime_type, $allowed_mime)) {
            return ['status' => false, 'error' => 'Only JPG, PNG, GIF, and WebP images are allowed.'];
        }

        $ext        = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename   = bin2hex(random_bytes(8)) . '_' . time() . '.' . strtolower($ext);
        
        // Root dir is project root. Target: public/assets/images/$type/
        $upload_dir = dirname(__DIR__, 2) . "/public/assets/images/{$type}/";
        $dest       = $upload_dir . $filename;

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Use GD to resize and optimize if extension loaded
        if (extension_loaded('gd')) {
            $resized = self::resizeImage($file['tmp_name'], $mime_type, $maxDimension);
            if ($resized) {
                // Override extension since we always save as JPEG
                $filename = pathinfo($filename, PATHINFO_FILENAME) . '.jpg';
                $dest     = $upload_dir . $filename;
                imagejpeg($resized, $dest, 85);
                imagedestroy($resized);
            } else {
                if (!move_uploaded_file($file['tmp_name'], $dest)) {
                    return ['status' => false, 'error' => 'Could not save the uploaded file.'];
                }
            }
        } else {
            if (!move_uploaded_file($file['tmp_name'], $dest)) {
                return ['status' => false, 'error' => 'Could not save the uploaded file.'];
            }
        }

        return ['status' => true, 'filename' => $filename];
    }

    /**
     * Resize image maintaining aspect ratio.
     */
    private static function resizeImage(string $path, string $mime, int $max_dim) {
        [$orig_w, $orig_h] = getimagesize($path);
        if ($orig_w <= $max_dim && $orig_h <= $max_dim) {
            switch ($mime) {
                case 'image/jpeg': $src = imagecreatefromjpeg($path); break;
                case 'image/png':  $src = imagecreatefrompng($path);  break;
                case 'image/gif':  $src = imagecreatefromgif($path);  break;
                case 'image/webp': $src = imagecreatefromwebp($path); break;
                default: return false;
            }
            return $src;
        }

        $ratio = min($max_dim / $orig_w, $max_dim / $orig_h);
        $new_w = (int)round($orig_w * $ratio);
        $new_h = (int)round($orig_h * $ratio);

        switch ($mime) {
            case 'image/jpeg': $src = imagecreatefromjpeg($path); break;
            case 'image/png':  $src = imagecreatefrompng($path);  break;
            case 'image/gif':  $src = imagecreatefromgif($path);  break;
            case 'image/webp': $src = imagecreatefromwebp($path); break;
            default: return false;
        }

        $dst = imagecreatetruecolor($new_w, $new_h);
        // Preserve transparency / fill white
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_w, $new_h, $orig_w, $orig_h);
        imagedestroy($src);
        return $dst;
    }
}
