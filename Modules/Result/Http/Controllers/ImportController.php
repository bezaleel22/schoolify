<?php

namespace Modules\Result\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportController extends Controller
{
    protected $message;

    public function upload(Request $request)
    {
        try {
            // Validate the incoming request
            $request->validate([
                'file' => 'required|file',
                'chunkIndex' => 'required|integer',
                'totalChunks' => 'required|integer',
                'filename' => 'required|string',
                'fileHash' => 'required|string',
            ]);

            // Extract data from the request
            $chunkIndex = $request->input('chunkIndex');
            $totalChunks = $request->input('totalChunks');
            $filename = $request->input('filename');
            $fileHash = $request->input('fileHash');

            // Check if the filename has a '.zip' extension
            if (!$this->isValidMimetype($filename)) {
                return response()->json(['error' => 'The file must have a valid extension.'], 400);
            }



            // Define temporary and uploaded file directories
            $tempDir = $this->checkDirExists(storage_path('app/temp'));
            $uploadedDir = $this->checkDirExists(storage_path('app/uploaded_files'));
            $finalFilePath = "$uploadedDir/$filename";

            // Check if the assembled zip file exists
            if (File::exists($finalFilePath)) {
                if ($this->verifyHash($finalFilePath, $fileHash)) {
                    if ($this->unzip($finalFilePath)) {
                        return $this->jsonResponse(true, 'File already uploaded, verified, and unzipped successfully.', [
                            'nextIndex' => $totalChunks
                        ]);
                    }
                    return $this->jsonResponse(false, 'Failed to unzip the file. Please ensure it is a valid archive.', [], 400);
                }
                unlink($finalFilePath);
            }

            // Handle chunk upload
            $chunkFileName = "$filename.part$chunkIndex";
            $cachedChunkIndex = Cache::get("progress_$fileHash");

            // Resume upload from the previously uploaded chunk
            if (!$chunkIndex && File::exists("$tempDir/$chunkFileName") && $cachedChunkIndex !== null) {
                Cache::forget("progress_$fileHash");
                return $this->jsonResponse(false, 'Resuming upload from previously uploaded chunks.', [
                    'nextIndex' => $cachedChunkIndex + 1,
                ]);
            }

            // Save the uploaded chunk
            $file = $request->file('file');
            $file->move($tempDir, $chunkFileName);


            // If this is the last chunk, assemble, verify, and unzip the zip file
            if ($chunkIndex + 1 == $totalChunks) {
       
                $finalFilePath = $this->assembleChunks($filename, $totalChunks, $tempDir, $uploadedDir);
                if (!$this->verifyHash($finalFilePath, $fileHash)) {
                    unlink($finalFilePath);
                    return $this->jsonResponse(false, 'File hash mismatch. The file may be corrupted.', [], 400);
                }

                if (pathinfo($filename, PATHINFO_EXTENSION) == 'json') {
                    Storage::put();
                    return $this->jsonResponse(true, 'File uploaded successfully.');
                }

                if (!$this->unzip($finalFilePath)) {
                    return $this->jsonResponse(false, 'Failed to unzip the file. Please ensure it is a valid archive.', [], 400);
                }
                return $this->jsonResponse(true, 'File uploaded successfully.');
            }
            // Cache the current chunk index
            Cache::put("progress_$fileHash", $chunkIndex, now()->addMinutes(60));
            return $this->jsonResponse(false, 'Chunk uploaded successfully.', ['nextIndex' => $chunkIndex + 1]);
        } catch (\Exception $e) {
            return $this->jsonResponse(false, $e->getMessage(), [], 400);
        }
    }

    // Helper method to ensure a directory exists
    protected function checkDirExists($path)
    {
        if (!File::exists($path)) {
            File::makeDirectory($path, 0775, true);
        }
        return $path;
    }

    // Assemble file chunks into a complete file
    protected function assembleChunks($filename, $totalChunks, $tempDir, $uploadedDir)
    {
        $finalFilePath = "$uploadedDir/$filename";
        $finalFile = fopen($finalFilePath, 'wb');

        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkFilePath = "$tempDir/$filename.part$i";
            if (!File::exists($chunkFilePath)) {
                throw new \Exception("Missing chunk: $i");
            }
            $this->processChunk($chunkFilePath, $finalFile);
            unlink($chunkFilePath);
        }
        fclose($finalFile);
        return $finalFilePath;
    }

    // Process each chunk with memory-efficient reading
    protected function processChunk($chunkFilePath, $finalFile)
    {
        $handle = fopen($chunkFilePath, 'rb');
        while (!feof($handle)) {
            $chunkData = fread($handle, 8192); // Read in 8 KB chunks
            fwrite($finalFile, $chunkData);
        }
        fclose($handle);
    }

    // Verify the hash of the uploaded file matches the expected hash
    protected function verifyHash($filePath, $expectedHash)
    {
        $fileHash = hash_file('sha256', $filePath);
        return $fileHash === $expectedHash;
    }

    // Unzip the file to the specified directory
    protected function unzip($zipfile)
    {
        dispatch(function () use ($zipfile) {
            $extractDir = public_path('uploads/' . pathinfo($zipfile, PATHINFO_FILENAME));
            try {
                // Create the directory if it doesn't exist
                if (!file_exists($extractDir) && !mkdir($extractDir, 0755, true)) {
                    Log::error("Failed to create extraction directory: $extractDir");
                    return;
                }

                $zip = new \ZipArchive;
                if ($zip->open($zipfile) !== true) {
                    Log::error("Failed to open the zip file: $zipfile");
                    return;
                }

                if (!$zip->extractTo($extractDir)) {
                    Log::error("Failed to extract the zip file: $zipfile to $extractDir");
                    File::deleteDirectory($extractDir); // Clean up on failure
                }
            } catch (\Exception $e) {
                Log::error("Error during extraction of $zipfile: " . $e->getMessage());
            } finally {
                if (isset($zip) && $zip instanceof \ZipArchive) {
                    $zip->close();
                }
            }
        });
        return true;
    }

    protected function isValidMimetype($filename, $validExtensions = ['zip', 'json'])
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($extension, array_map('strtolower', $validExtensions));
    }

    protected function fileUpload($file)
    {
        $fileContent = null;
        $base_url = env('LOCAL_BASE_URL');

        $fileInfo = pathinfo($file);
        if (!isset($fileInfo['extension'])) return;

        $temp = tempnam(sys_get_temp_dir(), 'downloaded_');
        try {
            $fileContent = file_get_contents("$base_url/$file");
            file_put_contents($temp, $fileContent);
            $dirname = $fileInfo['dirname'];
            if (!file_exists($dirname)) mkdir($dirname, 0775, true);

            rename($temp, $file);
        } catch (\Exception $e) {
            if ($fileContent !== null) unlink($temp);
            if (strpos($e->getMessage(), '404') !== false) return;
            throw $e;
        }
    }

    // Generic method to generate JSON responses
    protected function jsonResponse(bool $success, string $message, array $data = [], int $status = 200)
    {
        return response()->json(array_merge([
            'success' => $success,
            'message' => $message,
        ], $data), $status);
    }
}
