<?php

namespace CodeDistortion\RelCss\Tests\Unit;

use CodeDistortion\RelCss\Exceptions\FilesystemException;
use CodeDistortion\RelCss\Filesystem\DirectFilesystem;
use CodeDistortion\RelCss\RelevantCss;
use CodeDistortion\RelCss\Tests\TestCase;

/**
 * Test the RelevantCss class
 *
 * @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
class RelevantCssUnitTest extends TestCase
{
    /**
     * Provide data for test_that_css_definitions_are_loaded_from_files()
     *
     * @return array[]
     */
    public function cssDefinitionDataProvider()
    {
        return [

            'no css files' => [
                'cssSources' => [],
                'htmlSources' => [],
                'alwaysAddSet' => [],
                'minify' => false,
                'leadingWhitespace' => '',
                'removeUnused' => false,
                'expectedOutput' => '',
                'expectedException' => null,
            ],

            '1 css file' => [
                'cssSources' => [
                    [
                        'type' => 'file',
                        'value' => __DIR__.'/css/1.css',
                    ],
                ],
                'htmlSources' => [],
                'alwaysAddSet' => [],
                'minify' => false,
                'leadingWhitespace' => '',
                'removeUnused' => false,
                'expectedOutput' => 'body{padding:0}'.PHP_EOL,
                'expectedException' => null,
            ],

            '2 css files' => [
                'cssSources' => [
                    [
                        'type' => 'file',
                        'value' => __DIR__.'/css/1.css',
                    ],
                    [
                        'type' => 'file',
                        'value' => __DIR__.'/css/2.css',
                    ],
                ],
                'htmlSources' => [],
                'alwaysAddSet' => [],
                'minify' => false,
                'leadingWhitespace' => '',
                'removeUnused' => false,
                'expectedOutput' =>
                    'body{padding:0}'.PHP_EOL
                    .'body{padding:10px}'.PHP_EOL,
                'expectedException' => null,
            ],

            '1 css string' => [
                'cssSources' => [
                    [
                        'type' => 'string',
                        'value' => 'table, tr, td { border: 1px solid red; }',
                    ],
                ],
                'htmlSources' => [],
                'alwaysAddSet' => [],
                'minify' => false,
                'leadingWhitespace' => '',
                'removeUnused' => false,
                'expectedOutput' => 'table,tr,td{border:1px solid red}'.PHP_EOL,
                'expectedException' => null,
            ],

            '2 css strings' => [
                'cssSources' => [
                    [
                        'type' => 'string',
                        'value' => 'table, tr, td { border: 1px solid red; }',
                    ],
                    [
                        'type' => 'string',
                        'value' => 'table, tr, td { border: 1px solid blue; }',
                    ],
                ],
                'htmlSources' => [],
                'alwaysAddSet' => [],
                'minify' => false,
                'leadingWhitespace' => '',
                'removeUnused' => false,
                'expectedOutput' =>
                    'table,tr,td{border:1px solid red}'.PHP_EOL
                    .'table,tr,td{border:1px solid blue}'.PHP_EOL,
                'expectedException' => null,
            ],

            'interlaced css files and strings' => [
                'cssSources' => [
                    [
                        'type' => 'file',
                        'value' => __DIR__.'/css/1.css',
                    ],
                    [
                        'type' => 'string',
                        'value' => 'table, tr, td { border: 1px solid red; }',
                    ],
                    [
                        'type' => 'file',
                        'value' => __DIR__.'/css/2.css',
                    ],
                    [
                        'type' => 'string',
                        'value' => 'table, tr, td { border: 1px solid blue; }',
                    ],
                ],
                'htmlSources' => [],
                'alwaysAddSet' => [],
                'minify' => false,
                'leadingWhitespace' => '',
                'removeUnused' => false,
                'expectedOutput' =>
                    'body{padding:0}'.PHP_EOL
                    .'table,tr,td{border:1px solid red}'.PHP_EOL
                    .'body{padding:10px}'.PHP_EOL
                    .'table,tr,td{border:1px solid blue}'.PHP_EOL,
                'expectedException' => null,
            ],

            'no html' => [
                'cssSources' => [
                    [
                        'type' => 'file',
                        'value' => __DIR__.'/css/3.css',
                    ],
                ],
                'htmlSources' => [],
                'alwaysAddSet' => [],
                'minify' => false,
                'leadingWhitespace' => '',
                'removeUnused' => true,
                'expectedOutput' => '',
                'expectedException' => null,
            ],

            '1 html file' => [
                'cssSources' => [
                    [
                        'type' => 'file',
                        'value' => __DIR__.'/css/3.css',
                    ],
                ],
                'htmlSources' => [
                    [
                        'type' => 'file',
                        'value' => __DIR__.'/html/1.html',
                    ],
                ],
                'alwaysAddSet' => [],
                'minify' => false,
                'leadingWhitespace' => '',
                'removeUnused' => true,
                'expectedOutput' =>
                    'html{border:1px solid red}'.PHP_EOL
                    .'body{padding:0}'.PHP_EOL
                    .'input{background-color:white;border:1px solid black}'.PHP_EOL,
                'expectedException' => null,
            ],

            '2 html files' => [
                'cssSources' => [
                    [
                        'type' => 'file',
                        'value' => __DIR__.'/css/3.css',
                    ],
                ],
                'htmlSources' => [
                    [
                        'type' => 'file',
                        'value' => __DIR__.'/html/1.html',
                    ],
                    [
                        'type' => 'file',
                        'value' => __DIR__.'/html/2.html',
                    ],
                ],
                'alwaysAddSet' => [],
                'minify' => false,
                'leadingWhitespace' => '',
                'removeUnused' => true,
                'expectedOutput' =>
                    'html{border:1px solid red}'.PHP_EOL
                    .'body{padding:0}'.PHP_EOL
                    .'input{background-color:white;border:1px solid black}'.PHP_EOL
                    .'.success{color:green}'.PHP_EOL,
                'expectedException' => null,
            ],

            '1 html string' => [
                'cssSources' => [
                    [
                        'type' => 'file',
                        'value' => __DIR__.'/css/3.css',
                    ],
                ],
                'htmlSources' => [
                    [
                        'type' => 'string',
                        'value' => '<img src="" alt="" />',
                    ],
                ],
                'alwaysAddSet' => [],
                'minify' => false,
                'leadingWhitespace' => '',
                'removeUnused' => true,
                'expectedOutput' =>
                    'img{border:1px solid orange}'.PHP_EOL,
                'expectedException' => null,
            ],

            '2 html strings' => [
                'cssSources' => [
                    [
                        'type' => 'file',
                        'value' => __DIR__.'/css/3.css',
                    ],
                ],
                'htmlSources' => [
                    [
                        'type' => 'string',
                        'value' => '<img src="" alt="" />',
                    ],
                    [
                        'type' => 'string',
                        'value' => '<img src="" class="thumbnail" alt="" />',
                    ],
                ],
                'alwaysAddSet' => [],
                'minify' => false,
                'leadingWhitespace' => '',
                'removeUnused' => true,
                'expectedOutput' =>
                    'img{border:1px solid orange}'.PHP_EOL
                    .'img.thumbnail{width:100px;height:100px}'.PHP_EOL,
                'expectedException' => null,
            ],

            'interlaced html files and strings' => [
                'cssSources' => [
                    [
                        'type' => 'file',
                        'value' => __DIR__.'/css/3.css',
                    ],
                ],
                'htmlSources' => [
                    [
                        'type' => 'file',
                        'value' => __DIR__.'/html/1.html',
                    ],
                    [
                        'type' => 'string',
                        'value' => '<img src="" alt="" />',
                    ],
                    [
                        'type' => 'file',
                        'value' => __DIR__.'/html/2.html',
                    ],
                    [
                        'type' => 'string',
                        'value' => '<img src="" alt="" class="thumbnail" />',
                    ],
                ],
                'alwaysAddSet' => [],
                'minify' => false,
                'leadingWhitespace' => '',
                'removeUnused' => true,
                'expectedOutput' =>
                    'html{border:1px solid red}'.PHP_EOL
                    .'body{padding:0}'.PHP_EOL
                    .'input{background-color:white;border:1px solid black}'.PHP_EOL
                    .'img{border:1px solid orange}'.PHP_EOL
                    .'img.thumbnail{width:100px;height:100px}'.PHP_EOL
                    .'.success{color:green}'.PHP_EOL,
                'expectedException' => null,
            ],

            'some "always include" selectors' => [
                'cssSources' => [
                    [
                        'type' => 'file',
                        'value' => __DIR__.'/css/3.css',
                    ],
                ],
                'htmlSources' => [
                    [
                        'type' => 'file',
                        'value' => __DIR__.'/html/1.html',
                    ],
                ],
                'alwaysAddSet' => [
                    '.success, .warning, .error',
                ],
                'minify' => false,
                'leadingWhitespace' => '',
                'removeUnused' => true,
                'expectedOutput' =>
                    'html{border:1px solid red}'.PHP_EOL
                    .'body{padding:0}'.PHP_EOL
                    .'input{background-color:white;border:1px solid black}'.PHP_EOL
                    .'.success{color:green}'.PHP_EOL
                    .'.warning{color:orange}'.PHP_EOL
                    .'.error{color:red}'.PHP_EOL,
                'expectedException' => null,
            ],

            'missing css file (exception)' => [
                'cssSources' => [
                    [
                        'type' => 'file',
                        'value' => __DIR__.'/css/missing.css',
                    ],
                ],
                'htmlSources' => [],
                'alwaysAddSet' => [],
                'minify' => false,
                'leadingWhitespace' => '',
                'removeUnused' => true,
                'expectedOutput' => '',
                'expectedException' => FilesystemException::class,
            ],

            'missing html file (exception)' => [
                'cssSources' => [],
                'htmlSources' => [
                    [
                        'type' => 'file',
                        'value' => __DIR__.'/html/missing.html',
                    ],
                ],
                'alwaysAddSet' => [],
                'minify' => false,
                'leadingWhitespace' => '',
                'removeUnused' => true,
                'expectedOutput' => '',
                'expectedException' => FilesystemException::class,
            ],

            'exotic selectors' => [
                'cssSources' => [
                    [
                        'type' => 'string',
                        'value' =>
                            'abbr[title] { abc: def; }'.PHP_EOL
                            .'[type="button"] { abc: def; }'.PHP_EOL
                            .'button:focus'.PHP_EOL
                            .'::-webkit-file-upload-button { abc: def; }'.PHP_EOL
                            .'input::-moz-placeholder { abc: def; }'.PHP_EOL
                            .'.form-input::-webkit-input-placeholder { abc: def; }'.PHP_EOL
                            .'.form-multiselect:focus { abc: def; }',
                    ],
                ],
                'htmlSources' => [
                    [
                        'type' => 'string',
                        'value' =>
                            '<abbr>abc</abbr>'.PHP_EOL
                            .'<button type="button">click</button>'.PHP_EOL
                            .'<input type="text" name="name" class="form-input form-multiselect">',
                    ],
                ],
                'alwaysAddSet' => [],
                'minify' => false,
                'leadingWhitespace' => '',
                'removeUnused' => true,
                'expectedOutput' =>
                    'abbr[title]{abc:def}'.PHP_EOL
                    .'[type="button"]{abc:def}'.PHP_EOL
                    .'button:focus'.PHP_EOL
                    .'::-webkit-file-upload-button{abc:def}'.PHP_EOL
                    .'input::-moz-placeholder{abc:def}'.PHP_EOL
                    .'.form-input::-webkit-input-placeholder{abc:def}'.PHP_EOL
                    .'.form-multiselect:focus{abc:def}'.PHP_EOL,
                'expectedException' => null,
            ],

            'media queries' => [
                'cssSources' => [
                    [
                        'type' => 'string',
                        'value' =>
                            'input { background-color: black; }'.PHP_EOL
                            .'@media print and (-ms-high-contrast: active), '
                            .'print and (-ms-high-contrast: none) {'.PHP_EOL
                            .'  input {'.PHP_EOL
                            .'    padding-right: 0.75rem;'.PHP_EOL
                            .'  }'.PHP_EOL
                            .'}'.PHP_EOL
                            .'input { background-color: white; }'.PHP_EOL,
                    ],
                ],
                'htmlSources' => [
                    [
                        'type' => 'string',
                        'value' => '<input type="text" name="name">',
                    ],
                ],
                'alwaysAddSet' => [],
                'minify' => false,
                'leadingWhitespace' => '',
                'removeUnused' => true,
                'expectedOutput' =>
                    'input{background-color:black}'.PHP_EOL
                    .'input{background-color:white}'.PHP_EOL
                    .'@media print and (-ms-high-contrast: active), print and (-ms-high-contrast: none){'.PHP_EOL
                    .'    input{padding-right:.75rem}'.PHP_EOL
                    .'}'.PHP_EOL,
                'expectedException' => null,
            ],

            'media queries - no whitespace' => [
                'cssSources' => [
                    [
                        'type' => 'string',
                        'value' =>
                            'input { background-color: black; }'.PHP_EOL
                            .'@media print and (-ms-high-contrast: active), '
                            .'print and (-ms-high-contrast: none) {'.PHP_EOL
                            .'  input {'.PHP_EOL
                            .'    padding-right: 0.75rem;'.PHP_EOL
                            .'  }'.PHP_EOL
                            .'}'.PHP_EOL
                            .'input { background-color: white; }'.PHP_EOL,
                    ],
                ],
                'htmlSources' => [
                    [
                        'type' => 'string',
                        'value' => '<input type="text" name="name">',
                    ],
                ],
                'alwaysAddSet' => [],
                'minify' => true,
                'leadingWhitespace' => '',
                'removeUnused' => true,
                'expectedOutput' =>
                    'input{background-color:black}'
                    .'input{background-color:white}'
                    .'@media print and (-ms-high-contrast: active), print and (-ms-high-contrast: none){'
                    .'input{padding-right:.75rem}'
                    .'}'.PHP_EOL,
                'expectedException' => null,
            ],

            'media queries - not picked up' => [
                'cssSources' => [
                    [
                        'type' => 'string',
                        'value' =>
                            '@media print and (-ms-high-contrast: active), '
                            .' print and (-ms-high-contrast: none) {'.PHP_EOL
                            .'  input {'.PHP_EOL
                            .'    padding-right: 0.75rem;'.PHP_EOL
                            .'  }'.PHP_EOL
                            .'}',
                    ],
                ],
                'htmlSources' => [
                    [
                        'type' => 'string',
                        'value' => '<html></html>',
                    ],
                ],
                'alwaysAddSet' => [],
                'minify' => false,
                'leadingWhitespace' => '',
                'removeUnused' => true,
                'expectedOutput' => '',
                'expectedException' => null,
            ],

            'media queries - with leading whitespace' => [
                'cssSources' => [
                    [
                        'type' => 'string',
                        'value' =>
                            '@media print and (-ms-high-contrast: active), '
                            .' print and (-ms-high-contrast: none) {'.PHP_EOL
                            .'  input {'.PHP_EOL
                            .'    padding-right: 0.75rem;'.PHP_EOL
                            .'  }'.PHP_EOL
                            .'}',
                    ],
                ],
                'htmlSources' => [
                    [
                        'type' => 'string',
                        'value' => '<html><input /></html>',
                    ],
                ],
                'alwaysAddSet' => [],
                'minify' => false,
                'leadingWhitespace' => '    ',
                'removeUnused' => true,
                'expectedOutput' =>
                    '    @media print and (-ms-high-contrast: active),  print and (-ms-high-contrast: none){'.PHP_EOL
                    .'        input{padding-right:.75rem}'.PHP_EOL
                    .'    }'.PHP_EOL,
                'expectedException' => null,
            ],

            'media queries - with leading whitespace - add-whitespace off' => [
                'cssSources' => [
                    [
                        'type' => 'string',
                        'value' =>
                            '@media print and (-ms-high-contrast: active), '
                            .' print and (-ms-high-contrast: none) {'.PHP_EOL
                            .'  input {'.PHP_EOL
                            .'    padding-right: 0.75rem;'.PHP_EOL
                            .'  }'.PHP_EOL
                            .'}',
                    ],
                ],
                'htmlSources' => [
                    [
                        'type' => 'string',
                        'value' => '<html><input /></html>',
                    ],
                ],
                'alwaysAddSet' => [],
                'minify' => true,
                'leadingWhitespace' => '    ',
                'removeUnused' => true,
                'expectedOutput' =>
                    '    @media print and (-ms-high-contrast: active),  print and (-ms-high-contrast: none){'
                    .'input{padding-right:.75rem}'
                    .'}'.PHP_EOL,
                'expectedException' => null,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider cssDefinitionDataProvider
     *
     * @param array[]     $cssSources        The sources to get css from.
     * @param array[]     $htmlSources       The sources to get html from.
     * @param string[]    $alwaysAddSet      Selectors to always add.
     * @param boolean     $minify            Should the output be minified?.
     * @param string      $leadingWhitespace The whitespace to add to the beginning of each line.
     * @param boolean     $removeUnused      Should unused css-definitions should be removed?.
     * @param string      $expectedOutput    The expected output.
     * @param string|null $expectedException The expected exception (if any).
     *
     * @return void
     */
    public function test_that_css_and_html_are_processed_properly(
        array $cssSources,
        array $htmlSources,
        array $alwaysAddSet,
        bool $minify,
        string $leadingWhitespace,
        bool $removeUnused,
        string $expectedOutput,
        ?string $expectedException
    ): void {

        $relCss = RelevantCss::new();

        // add css sources
        foreach ($cssSources as $cssSource) {
            if ($cssSource['type'] == 'file') {
                $relCss->cssFile($cssSource['value']);
            } elseif ($cssSource['type'] == 'string') {
                $relCss->cssDefinitions($cssSource['value']);
            }
        }
        // add html sources
        foreach ($htmlSources as $htmlSource) {

            if ($htmlSource['type'] == 'file') {
                $relCss->fileNeedsCss($htmlSource['value']);
            } elseif ($htmlSource['type'] == 'string') {
                $relCss->contentNeedsCss($htmlSource['value']);
            }
        }
        // add selectors to always include
        foreach ($alwaysAddSet as $alwaysAddSeletors) {
            $relCss->alwaysAddTheseSelectors($alwaysAddSeletors);
        }
        // remove unused css-definitions?
        $relCss->removeUnused($removeUnused);

        // minify the output?
        $relCss->minify($minify);


        // test for the exception
        if ($expectedException) {
            $this->assertThrows(
                $expectedException,
                function () use ($relCss, $leadingWhitespace) {
                    $relCss->render($leadingWhitespace);
                }
            );
            // or compare the output
        } else {
            $output = $relCss->render($leadingWhitespace);
            $this->assertSame($expectedOutput, $output);
        }
    }

    /**
     * @test
     * @return void
     */
    public function test_constructor(): void
    {
        $filesystem = $this->createMock(DirectFilesystem::class);

        // instantiate with the normal constructor
        $relCss = new RelevantCss('.', $filesystem, true);
        $relCss->cssDefinitions('html { abc: def; }');
        $relCss->contentNeedsCss('<html></html>');
        $output = $relCss->render();
        $this->assertSame('html{abc:def}'.PHP_EOL, $output);

        // instantiate with the ::new(..) constructor
        $relCss = RelevantCss::new('.', $filesystem, true);
        $relCss->cssDefinitions('html { abc: def; }');
        $relCss->contentNeedsCss('<html></html>');
        $output = $relCss->render();
        $this->assertSame('html{abc:def}'.PHP_EOL, $output);
    }

    /**
     * @test
     * @return void
     */
    public function test_chaining(): void
    {
        $output = RelevantCss::new()
                             ->cssFile(__DIR__.'/css/3.css')
                             ->cssDefinitions('html { abc: def; }')
                             ->fileNeedsCss(__DIR__.'/html/1.html')
                             ->contentNeedsCss('<html></html>')
                             ->alwaysAddTheseSelectors('.success, warning, error')
                             ->render();

        $this->assertSame(
            'html{border:1px solid red}'.PHP_EOL
            .'body{padding:0}'.PHP_EOL
            .'input{background-color:white;border:1px solid black}'.PHP_EOL
            .'.success{color:green}'.PHP_EOL
            .'.warning{color:orange}'.PHP_EOL
            .'.error{color:red}'.PHP_EOL
            .'html{abc:def}'.PHP_EOL,
            $output
        );
    }

    /**
     * @test
     * @return void
     */
    public function test_can_write_to_css_cache(): void
    {
        $filesystem = $this->createMock(DirectFilesystem::class);

        $filesystem
            ->expects($this->once())
            ->method('exists')
            ->with($this->equalTo('./RelevantCss.c420dfb3ea1bee2fcfaa0d1f31a225e1.cache.php'))
            ->will($this->returnValue(false));

        $filesystem
            ->expects($this->once())
            ->method('put')
            ->with(
                $this->equalTo('./RelevantCss.c420dfb3ea1bee2fcfaa0d1f31a225e1.cache.php'),
                $this->equalTo(
                    "<?php\n"
                    ."// RelevantCss cache file\n"
                    ."return array (\n"
                    ."  'selectors' => \n"
                    ."  array (\n"
                    ."    '' => \n"
                    ."    array (\n"
                    ."      0 => \n"
                    ."      array (\n"
                    ."        'html' => \n"
                    ."        array (\n"
                    ."          0 => 'html',\n"
                    ."        ),\n"
                    ."      ),\n"
                    ."    ),\n"
                    ."  ),\n"
                    ."  'styles' => \n"
                    ."  array (\n"
                    ."    '' => \n"
                    ."    array (\n"
                    ."      0 => '{abc:def}',\n"
                    ."    ),\n"
                    ."  ),\n"
                    ."  'wordIndexes' => \n"
                    ."  array (\n"
                    ."    '' => \n"
                    ."    array (\n"
                    ."      'html' => \n"
                    ."      array (\n"
                    ."        0 => 0,\n"
                    ."      ),\n"
                    ."    ),\n"
                    ."  ),\n"
                    ."  'optionalWords' => \n"
                    ."  array (\n"
                    ."    'html' => true,\n"
                    ."  ),\n"
                    ."  'compulsoryWords' => \n"
                    ."  array (\n"
                    ."  ),\n"
                    .");\n"
                )
            );

        $relCss = RelevantCss::new('.', $filesystem, true);
        $relCss->cssDefinitions('html { abc: def; }');
        $relCss->contentNeedsCss('<html></html>');
        $relCss->render();
    }

    /**
     * @test
     * @return void
     */
    public function test_can_read_from_css_cache(): void
    {
        $filesystem = $this->createMock(DirectFilesystem::class);

        $filesystem
            ->expects($this->once())
            ->method('exists')
            ->with($this->equalTo('./RelevantCss.c420dfb3ea1bee2fcfaa0d1f31a225e1.cache.php'))
            ->will($this->returnValue(true));

        $filesystem
            ->expects($this->once())
            ->method('getRequire')
            ->with($this->equalTo('./RelevantCss.c420dfb3ea1bee2fcfaa0d1f31a225e1.cache.php'));

        $relCss = RelevantCss::new('.', $filesystem, true);
        $relCss->cssDefinitions('html { abc: def; }');
        $relCss->contentNeedsCss('<html></html>');
        $output = $relCss->render();

//        $this->assertSame('html{abc:def}', $output);
    }
}
