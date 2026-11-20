<?php
declare(strict_types=1);

final class ImageUpload
{
    private const MAX_BYTES = 5242880;
    private const MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public static function storeEventImage(array $file): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['ok' => true, 'path' => null];
        }
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file((string) ($file['tmp_name'] ?? ''))) {
            return ['ok' => false, 'error' => 'Le téléversement de l’image a échoué.'];
        }
        if ((int) ($file['size'] ?? 0) < 1 || (int) $file['size'] > self::MAX_BYTES) {
            return ['ok' => false, 'error' => 'L’image doit faire au maximum 5 Mo.'];
        }

        $tmp = (string) $file['tmp_name'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp);
        if (!is_string($mime) || !isset(self::MIME_EXTENSIONS[$mime]) || @getimagesize($tmp) === false) {
            return ['ok' => false, 'error' => 'Le fichier doit être une image JPEG, PNG ou WebP valide.'];
        }

        $dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads';
        if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) {
            error_log('EventSecure: impossible de créer le répertoire d’images.');
            return ['ok' => false, 'error' => 'Le stockage de l’image est indisponible.'];
        }

        $filename = 'event_' . bin2hex(random_bytes(16)) . '.' . self::MIME_EXTENSIONS[$mime];
        $target = $dir . DIRECTORY_SEPARATOR . $filename;
        if (!move_uploaded_file($tmp, $target)) {
            error_log('EventSecure: impossible de déplacer une image téléversée.');
            return ['ok' => false, 'error' => 'Le stockage de l’image a échoué.'];
        }
        @chmod($target, 0640);
        return ['ok' => true, 'path' => '/gestion_evenements/assets/uploads/' . $filename];
    }
}
