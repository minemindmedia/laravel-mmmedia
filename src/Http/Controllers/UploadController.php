<?php

namespace Mmmedia\Media\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mmmedia\Media\Models\MediaItem;

class UploadController
{
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:' . (config('media.upload.max_file_size') * 1024),
        ]);

        $file = $request->file('file');
        $disk = config('media.disk');
        
        // Validate MIME type
        $allowedMimes = collect(config('media.upload.allowed_mimes'))->flatten()->toArray();
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            return response()->json([
                'error' => 'File type not allowed',
                'allowed_types' => $allowedMimes,
            ], 422);
        }

        // Determine storage path based on file type
        $path = $this->getStoragePath($file);
        
        // Generate unique filename
        $filename = $this->generateFilename($file);
        $fullPath = $path . '/' . $filename;

        // Store the file
        $storedPath = $file->storeAs($path, $filename, $disk);

        // Get image dimensions if it's an image
        $width = null;
        $height = null;
        if (str_starts_with($file->getMimeType(), 'image/')) {
            $imageInfo = getimagesize($file->getPathname());
            if ($imageInfo) {
                $width = $imageInfo[0];
                $height = $imageInfo[1];
            }
        }

        // Create media item record
        $mediaItem = MediaItem::create([
            'disk' => $disk,
            'path' => $storedPath,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'width' => $width,
            'height' => $height,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'media_item' => [
                'id' => $mediaItem->id,
                'url' => $mediaItem->url,
                'thumbnail_url' => $mediaItem->thumbnail_url,
                'original_name' => $mediaItem->original_name,
                'mime_type' => $mediaItem->mime_type,
                'size' => $mediaItem->formatted_size,
                'width' => $mediaItem->width,
                'height' => $mediaItem->height,
                'is_image' => $mediaItem->isImage(),
                'is_video' => $mediaItem->isVideo(),
                'is_document' => $mediaItem->isDocument(),
            ],
        ]);
    }

    public function delete(MediaItem $mediaItem): JsonResponse
    {
        try {
            $mediaItem->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Media item deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete media item: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(MediaItem $mediaItem, Request $request): JsonResponse
    {
        $request->validate([
            'alt' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:1000',
        ]);

        $mediaItem->update($request->only(['alt', 'title', 'caption']));

        return response()->json([
            'success' => true,
            'media_item' => [
                'id' => $mediaItem->id,
                'alt' => $mediaItem->alt,
                'title' => $mediaItem->title,
                'caption' => $mediaItem->caption,
            ],
        ]);
    }

    private function getStoragePath($file): string
    {
        $mimeType = $file->getMimeType();
        
        if (str_starts_with($mimeType, 'image/')) {
            return config('media.paths.images');
        } elseif (str_starts_with($mimeType, 'video/')) {
            return config('media.paths.videos');
        } else {
            return config('media.paths.documents');
        }
    }

    private function generateFilename($file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('Y/m/d');
        $uuid = Str::uuid();
        
        return $timestamp . '/' . $uuid . '.' . $extension;
    }
}
