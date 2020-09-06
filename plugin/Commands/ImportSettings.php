<?php

namespace GeminiLabs\SiteReviews\Commands;

use GeminiLabs\SiteReviews\Contracts\CommandContract as Contract;
use GeminiLabs\SiteReviews\Database\OptionManager;
use GeminiLabs\SiteReviews\Helpers\Arr;
use GeminiLabs\SiteReviews\Helpers\Str;
use GeminiLabs\SiteReviews\Modules\Notice;

class ImportSettings implements Contract
{
    private $file;

    public function __construct()
    {
        $this->file = glsr()->args(Arr::get($_FILES, 'import-file', []));
    }

    /**
     * @param int $errorCode
     * @return string
     */
    public function getUploadError($errorCode)
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => _x('The uploaded file exceeds the upload_max_filesize directive in php.ini.', 'admin-text', 'site-reviews'),
            UPLOAD_ERR_FORM_SIZE => _x('The uploaded file is too big.', 'admin-text', 'site-reviews'),
            UPLOAD_ERR_PARTIAL => _x('The uploaded file was only partially uploaded.', 'admin-text', 'site-reviews'),
            UPLOAD_ERR_NO_FILE => _x('No file was uploaded.', 'admin-text', 'site-reviews'),
            UPLOAD_ERR_NO_TMP_DIR => _x('Missing a temporary folder.', 'admin-text', 'site-reviews'),
            UPLOAD_ERR_CANT_WRITE => _x('Failed to write file to disk.', 'admin-text', 'site-reviews'),
            UPLOAD_ERR_EXTENSION => _x('A PHP extension stopped the file upload.', 'admin-text', 'site-reviews'),
        ];
        return Arr::get($errors, $errorCode, _x('Unknown upload error.', 'admin-text', 'site-reviews'));
    }

    /**
     * @return void
     */
    public function handle()
    {
        if (!$this->validateUpload()) {
            return;
        }
        if (!$this->validateFileType()) {
            return;
        }
        if ($this->import()) {
            glsr(Notice::class)->addSuccess(
                _x('Settings imported.', 'admin-text', 'site-reviews')
            );
        }
    }

    /**
     * @return bool
     */
    protected function import()
    {
        if ($settings = json_decode(file_get_contents($this->file->tmp_name), true)) {
            glsr(OptionManager::class)->set(
                glsr(OptionManager::class)->normalize($settings)
            );
            return true;
        }
        glsr(Notice::class)->addWarning(
            _x('There were no settings found to import.', 'admin-text', 'site-reviews')
        );
        return false;
    }

    /**
     * @return bool
     */
    protected function validateFileType()
    {
        if ('application/json' === $this->file->type && Str::endsWith('.json', $this->file->name)) {
            return true;
        }
        glsr(Notice::class)->addError(
            _x('Please use a valid Site Reviews settings file.', 'admin-text', 'site-reviews')
        );
        return false;
    }

    /**
     * @return bool
     */
    protected function validateUpload()
    {
        if (UPLOAD_ERR_OK === $this->file->error) {
            return true;
        }
        glsr(Notice::class)->addError($this->getUploadError($this->file->error));
        return false;
    }
}