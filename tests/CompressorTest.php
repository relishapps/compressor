<?php
use RelishMedia\Compressor;

define('COMPRESSOR_ROOT', './examples/');

class CompressorTest extends PHPUnit\Framework\TestCase
{
    public function validHtmlProvider()
    {
        return [
            ['<link rel="stylesheet" href="/css/file1.css">
            <link rel="stylesheet" href="/css/file2.css">
            <script src="/js/file1.js">
            <script src="/js/file2.js">']
        ];
    }

    public function htmlWithRelativePathsProvider()
    {
        return [
            ['<link rel="stylesheet" href="css/file1.css">
            <link rel="stylesheet" href="css/file2.css">
            <script src="js/file1.js">
            <script src="js/file2.js">']
        ];
    }

    /**
     * Test Compressor fails with relative paths
     *
     * As relative paths in URLs can’t be reliably mapped
     * to filesystem paths, we’re unable to read those files
     *
     * @param  string $html The HTML string to test
     *
     * @dataProvider htmlWithRelativePathsProvider
     */
    public function testFailsWithRelativePaths($html)
    {
        $this->setExpectedException('RelishMedia\CompressorException');
        $compressor = new Compressor($html);
    }

    /**
     * Tests the compressor can output original HTML
     *
     * @param  string $html The HTML string to test
     *
     * @dataProvider validHtmlProvider
     */
    public function testCompressorCanRenderOriginalHtml($html)
    {
        Compressor::disable();

        $compressor = new Compressor($html);

        ob_start();
        $compressor->render();
        $output = ob_get_clean();

        $this->assertEquals($html, $output);
    }

    /**
     * Tests the compressor can output compressed HTML
     *
     * @param  string $html The HTML string to test
     *
     * @dataProvider validHtmlProvider
     */
    public function testCompressorCanRenderCompressedHtml($html)
    {
        Compressor::enable();

        $compressor = new Compressor($html);

        ob_start();
        $compressor->render();
        $output = ob_get_clean();

        $regexp = '#^<script src="/js/compressor/[0-9a-z]{24,25}_[0-9a-z]{24,25}.js"></script><link href="/css/compressor/[0-9a-z]{24,25}_[0-9a-z]{24,25}.css" rel="stylesheet">$#';

        $this->assertRegExp($regexp, $output);
    }
}
