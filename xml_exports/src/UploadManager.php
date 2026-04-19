<?php

namespace Techy4m\XmlExports;

class UploadManager
{
    public static function handleUploads(): bool
    {
        $italyCertificate = self::handleCertificateUpload('xml_export_italy_certificate');
        $spainCertificate = self::handleCertificateUpload('xml_export_spain_certificate');
        $italyPrivateKey = self::handleCertificateUpload('xml_export_italy_private_key');
        $spainPrivateKey = self::handleCertificateUpload('xml_export_spain_private_key');

        return $italyCertificate || $spainCertificate || $italyPrivateKey || $spainPrivateKey;
    }

    private static function handleCertificateUpload(string $field): bool
    {
        if (isset($_FILES[$field]) && _perfex_upload_error($_FILES[$field]['error'])) {
            return false;
        }

        if (isset($_FILES[$field]['name']) && $_FILES[$field]['name'] != '') {
            $path = get_upload_path_by_type('xml_export');
            // Get the temp file path
            $tmpFilePath = $_FILES[$field]['tmp_name'];
            // Make sure we have a filepath
            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                // Getting file extension
                $path_parts = pathinfo($_FILES[$field]['name']);
                $extension = $path_parts['extension'];
                $extension = strtolower($extension);

                $allowed_extensions = [
                    'p12',
                    'pfx',
                    'key'
                ];


                if (!in_array($extension, $allowed_extensions)) {
                    set_alert('warning', 'extension not allowed.');

                    return false;
                }

                // Setup our new file path
                $filename = $field . '.' . $extension;
                $newFilePath = $path . $filename;
                _maybe_create_upload_path($path);

                // Upload the file into the company uploads dir
                if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                    update_option($field, $filename);

                    return true;
                }
            }
        }

        return false;
    }

    public static function getUploadedFilePath($type): string
    {
        return get_upload_path_by_type('xml_export') . get_option($type);
    }
}