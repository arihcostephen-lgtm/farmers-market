<?php

function report_attachment_rows($db, $reportId)
{
    $reportId = (int) $reportId;
    $attachments = [];
    $query = mysqli_query($db, "SELECT attachment_name, attachment_path, attachment_type, attachment_size FROM supervisor_report_attachments WHERE report_id = $reportId ORDER BY attachment_id ASC");
    if ($query) {
        while ($attachment = mysqli_fetch_assoc($query)) {
            $attachments[] = $attachment;
        }
    }
    return $attachments;
}

function save_report_attachments($db, $reportId, $supervisorId, $files, $uploadRoot)
{
    if (empty($files['name']) || !is_array($files['name'])) {
        return '';
    }

    $allowedTypes = [
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];
    $fileCount = count($files['name']);
    if ($fileCount > 5) {
        return 'You can attach a maximum of 5 files per report.';
    }

    $reportDirectory = rtrim($uploadRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR . (int) $supervisorId;
    if (!is_dir($reportDirectory) && !mkdir($reportDirectory, 0750, true)) {
        return 'The report attachment directory could not be created.';
    }

    $fileInfo = new finfo(FILEINFO_MIME_TYPE);
    for ($index = 0; $index < $fileCount; $index++) {
        if (($files['error'][$index] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        if (($files['error'][$index] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || ($files['size'][$index] ?? 0) > 10 * 1024 * 1024) {
            return 'Each attachment must be a valid file no larger than 10 MB.';
        }
        $temporaryPath = $files['tmp_name'][$index] ?? '';
        $mimeType = is_uploaded_file($temporaryPath) ? $fileInfo->file($temporaryPath) : false;
        if (!isset($allowedTypes[$mimeType])) {
            return 'Only PDF, Word, JPG, PNG, GIF, and WEBP files can be attached.';
        }

        $extension = $allowedTypes[$mimeType];
        $storedName = bin2hex(random_bytes(16)) . '.' . $extension;
        $targetPath = $reportDirectory . DIRECTORY_SEPARATOR . $storedName;
        if (!move_uploaded_file($temporaryPath, $targetPath)) {
            return 'One or more attachments could not be saved.';
        }

        $relativePath = 'reports/' . (int) $supervisorId . '/' . $storedName;
        $originalName = mysqli_real_escape_string($db, basename($files['name'][$index]));
        $safePath = mysqli_real_escape_string($db, $relativePath);
        $safeMime = mysqli_real_escape_string($db, $mimeType);
        $fileSize = (int) $files['size'][$index];
        $saved = mysqli_query($db, "INSERT INTO supervisor_report_attachments (report_id, attachment_name, attachment_path, attachment_type, attachment_size) VALUES ($reportId, '$originalName', '$safePath', '$safeMime', $fileSize)");
        if (!$saved) {
            @unlink($targetPath);
            return 'One or more attachments could not be recorded.';
        }
    }
    return '';
}
