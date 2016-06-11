<?php
namespace RelishMedia;

if (!defined('COMPRESSOR_URL')) {
    define('COMPRESSOR_URL', '/');
}

if (!defined('COMPRESSOR_ROOT')) {
    define('COMPRESSOR_ROOT', './');
}

class Compressor
{
    const CSS = 'css';
    const JS = 'js';
    const CSS_REGEX = '/<link.*href=([\'"])(.+)\1.*>/Ui';
    const JS_REGEX = '/<script.*src=([\'"])(.+)\1.*>/Ui';

    private static $enabled = true;

    private $types = [self::CSS, self::JS];

    private $html;
    private $js_files = [];
    private $css_files = [];

    /**
     * Enable Compressor
     */
    public static function enable()
    {
        self::$enabled = true;
    }

    /**
     * Disable compressor
     */
    public static function disable()
    {
        self::$enabled = false;
    }

    /**
     * Start buffering output for processing
     */
    public static function start()
    {
        ob_start();
    }

    /**
     * Stop buffering, process and render
     */
    public static function stop()
    {
        $html = ob_get_clean();

        $compressor = new self($html);
        $compressor->render();
    }

    /**
     * Find references to CSS and JS files in the HTML
     *
     * @param string $html The HTML to search
     */
    public function __construct($html)
    {
        $this->html = $html;
        $this->root = realpath(COMPRESSOR_ROOT);

        preg_match_all(self::CSS_REGEX, $html, $matches, PREG_PATTERN_ORDER);

        foreach ($matches[2] as $path) {
            $this->css_files[] = $this->urlPathToFileSystemPath($path);
        }

        preg_match_all(self::JS_REGEX, $html, $matches, PREG_PATTERN_ORDER);

        foreach ($matches[2] as $path) {
            $this->js_files[] = $this->urlPathToFileSystemPath($path);
        }
    }

    /**
     * Render output
     */
    public function render()
    {
        if (self::$enabled) {
            $this->renderCompressed();
        } else {
            $this->renderOriginal();
        }
    }

    /**
     * Render tags pointing to compressed CSS and/or JS
     * @return [type] [description]
     */
    private function renderCompressed()
    {
        if ($filename = $this->compressJs()) {
            echo '<script src="' . COMPRESSOR_URL . 'js/compressor/' . $filename . '"></script>';
        }

        if ($filename = $this->compressCss()) {
            echo '<link href="' . COMPRESSOR_URL . 'css/compressor/' . $filename . '" rel="stylesheet">';
        }
    }

    private function renderOriginal()
    {
        echo $this->html;
    }

    private function urlPathToFileSystemPath($url)
    {
        if (preg_match("#^https?://#", $url)) {
            return $url;
        }

        if (substr($url, 0, 1) !== '/') {
            throw new CompressorException("Compressor only works with asbsolute paths to resources. '{$url}' given");
        }

        $path = $this->root . $url;

        if (DIRECTORY_SEPARATOR == '\\') {
            $path = str_replace('/', '\\', $path);
        }

        return $path;
    }

    private function compressJs()
    {
        if (count($this->js_files) === 0) {
            return null;
        }

        if (!is_dir($this->root . '/js/compressor')) {
            mkdir($this->root . '/js/compressor', 0744, true);
        }

        $filename = $this->getFilenameHash(self::JS) . '_' . $this->getFileHash(self::JS) . '.js';
        $path = $this->root . '/js/compressor/' . $filename;

        $this->deleteFiles($this->root . '/js/compressor/' . $this->getFilenameHash(self::JS) . '_*.js');

        file_put_contents($path, $this->concatFiles($this->js_files));

        exec("uglifyjs $path -mco $path");

        return $filename;
    }

    private function compressCss()
    {
        if (count($this->css_files) === 0) {
            return null;
        }

        if (!is_dir($this->root . '/css/compressor')) {
            mkdir($this->root . '/css/compressor', 0744, true);
        }

        $filename = $this->getFilenameHash(self::CSS) . '_' . $this->getFileHash(self::CSS) . '.css';
        $path = $this->root . '/css/compressor/' . $filename;

        $this->deleteFiles($this->root . '/css/compressor/' . $this->getFilenameHash(self::JS) . '_*.css');

        file_put_contents($path, $this->concatFiles($this->css_files));

        exec("csso $path $path");

        return $filename;
    }

    private function getFilenameHash($type)
    {
        if (!in_array($type, $this->types)) {
            $error = sprintf('$type should be one of: %s but %s was given.', join(',', $this->types), $type);
            throw new InvalidArgumentException($error);
        }

        $filenames = join('', $this->{$type . '_files'});

        return base_convert(md5($filenames), 16, 36);
    }

    private function getFileHash($type)
    {
        if (!in_array($type, $this->types)) {
            $error = sprintf('$type should be one of: %s but %s was given.', join(',', $this->types), $type);
            throw new InvalidArgumentException($error);
        }

        $src = '';

        foreach ($this->{$type . '_files'} as $file) {
            $src .= file_get_contents($file);
        }

        return base_convert(md5($src), 16, 36);
    }

    private function concatFiles($files)
    {
        $src = '';

        foreach ($files as $file) {
            $src .= file_get_contents($file) . "\n";
        }

        return $src;
    }

    private function deleteFiles($wildcard)
    {
        foreach (glob($wildcard) as $file) {
            unlink($file);
        }
    }
}

function __compress($html, $regex, $type)
{
    $path = constant("COMPRESSOR_{$type}_PATH");
    $url = constant("COMPRESSOR_{$type}_URL");

    preg_match_all($regex, $html, $matches, PREG_PATTERN_ORDER);

    $files = $matches[2];
    $src = '';
    $filename_hash = '';

    foreach($files as $file) {
        $src .= file_get_contents($file) . "\n";
        $filename_hash .= $file;
    }

    $filename_hash = md5($filename_hash);

    if (!is_dir($path)) {
        mkdir($path, 0744, true);
    }

    $filename = $filename_hash . '_' . md5($src) . '.' . strtolower($type);

    if (!file_exists($path . $filename)) {
        $f = fopen($path . $filename, 'w');
        fwrite($f, $src);
        fclose($f);

        delete_files($path . $filename_hash . '_*.' . $type);
    }

    return $url . $filename;
}
