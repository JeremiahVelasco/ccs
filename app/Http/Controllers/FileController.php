<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Task;

class FileController extends Controller
{
    public function serveTaskFile($taskId)
    {
        try {
            $task = Task::findOrFail($taskId);

            if (!$task->file_path || !Storage::disk('public')->exists($task->file_path)) {
                abort(404, 'File not found');
            }

            $filePath = Storage::disk('public')->path($task->file_path);
            $fileName = basename($task->file_path);
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            // Get MIME type
            $mimeType = 'application/octet-stream';
            if (function_exists('mime_content_type')) {
                $detectedMime = mime_content_type($filePath);
                if ($detectedMime) {
                    $mimeType = $detectedMime;
                }
            }

            $inlineExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'txt'];
            $disposition = in_array($extension, $inlineExtensions) ? 'inline' : 'attachment';

            return response()->file($filePath, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => $disposition . '; filename="' . $fileName . '"'
            ]);
        } catch (\Exception $e) {
            abort(404, 'File not found');
        }
    }

    public function viewTaskFile($taskId)
    {
        try {
            $task = Task::findOrFail($taskId);

            Log::info('Task found', ['task_id' => $taskId, 'file_path' => $task->file_path]);

            if (!$task->file_path) {
                abort(404, 'No file associated with this task');
            }

            Log::info('Checking if file exists in storage', [
                'file_path' => $task->file_path,
                'storage_exists' => Storage::disk('public')->exists($task->file_path)
            ]);

            if (!Storage::disk('public')->exists($task->file_path)) {
                abort(404, 'File not found on server');
            }

            $filePath = Storage::disk('public')->path($task->file_path);

            Log::info('Full file path', ['full_path' => $filePath, 'file_exists' => file_exists($filePath)]);

            if (!file_exists($filePath)) {
                abort(404, 'File does not exist');
            }

            $fileName = basename($task->file_path);

            // Get MIME type safely
            $mimeType = 'application/octet-stream'; // default
            if (function_exists('mime_content_type')) {
                $detectedMime = mime_content_type($filePath);
                if ($detectedMime) {
                    $mimeType = $detectedMime;
                }
            } else {
                // Fallback MIME type detection
                $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $mimeTypes = [
                    'pdf' => 'application/pdf',
                    'doc' => 'application/msword',
                    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'txt' => 'text/plain',
                    'zip' => 'application/zip',
                    'rar' => 'application/x-rar-compressed',
                ];
                $mimeType = $mimeTypes[$extension] ?? $mimeType;
            }

            // Force inline display for common document types
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $inlineExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'txt', 'doc', 'docx'];

            // Create an HTML page that embeds the file
            $fileUrl = Storage::url($task->file_path);

            $html = '<!DOCTYPE html>
<html>
<head>
    <title>' . htmlspecialchars($fileName) . '</title>
    <meta charset="utf-8">
    <style>
        body { margin: 0; padding: 0; height: 100vh; }
        iframe { width: 100%; height: 100%; border: none; }
        .fallback { padding: 20px; text-align: center; }
        .fallback a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>';

            // Handle different file types
            if (in_array($extension, ['pdf'])) {
                $html .= '<iframe src="' . $fileUrl . '" type="application/pdf"></iframe>';
            } elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                $html .= '<img src="' . $fileUrl . '" style="max-width: 100%; max-height: 100%; object-fit: contain;">';
            } elseif (in_array($extension, ['txt'])) {
                $html .= '<iframe src="' . $fileUrl . '" style="width: 100%; height: 100%;"></iframe>';
            } else {
                $html .= '<div class="fallback">
                    <h3>File Preview Not Available</h3>
                    <p>This file type cannot be previewed in the browser.</p>
                    <a href="' . $fileUrl . '" target="_blank">Open File</a> | 
                    <a href="' . $fileUrl . '" download>Download File</a>
                </div>';
            }

            $html .= '</body></html>';

            return response($html, 200, [
                'Content-Type' => 'text/html; charset=utf-8',
                'Cache-Control' => 'no-cache, must-revalidate'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Task not found');
        } catch (\Exception $e) {
            Log::error('File viewing error: ' . $e->getMessage());
            abort(500, 'Error viewing file: ' . $e->getMessage());
        }
    }

    public function debugTaskFile($taskId)
    {
        try {
            $task = Task::findOrFail($taskId);

            $debugInfo = [
                'task_id' => $taskId,
                'task_title' => $task->title,
                'file_path' => $task->file_path,
                'storage_exists' => $task->file_path ? Storage::disk('public')->exists($task->file_path) : false,
                'full_path' => $task->file_path ? Storage::disk('public')->path($task->file_path) : null,
                'file_exists' => $task->file_path ? file_exists(Storage::disk('public')->path($task->file_path)) : false,
                'storage_disk_path' => Storage::disk('public')->path(''),
                'public_path' => public_path('storage'),
            ];

            return response()->json($debugInfo);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }
}
