<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Component\Editor\CkEditor\Toolbar;

use App\CoreBundle\Component\Editor\Toolbar;

class Basic extends Toolbar
{
    /**
     * Default plugins that will be use in all toolbars
     * In order to add a new plugin you have to load it in default/layout/head.tpl.
     */
    public array $defaultPlugins = [
        // 'adobeair',
        // 'ajax',
        'audio',
        'image2_chamilo',
        'bidi',
        'colorbutton',
        'colordialog',
        'dialogui',
        'dialogadvtab',
        'div',
        // if you activate this plugin the html, head tags will not be saved
        // 'divarea',
        // 'docprops',
        'find',
        'flash',
        'font',
        'iframe',
        // 'iframedialog',
        'indentblock',
        'justify',
        'language',
        'lineutils',
        'liststyle',
        'newpage',
        'oembed',
        'pagebreak',
        'preview',
        'print',
        'save',
        'selectall',
        // 'sharedspace',
        'showblocks',
        'smiley',
        // 'sourcedialog',
        // 'stylesheetparser',
        // 'tableresize',
        'templates',
        // 'uicolor',
        'video',
        'widget',
        'wikilink',
        'wordcount',
        'inserthtml',
        // 'xml',
        'qmarkersrolls',
    ];

    /**
     * Plugins this toolbar.
     */
    public array $plugins = [];
    private string $toolbarSet;

    public function __construct(
        $router,
        $toolbar = null,
        $config = [],
        $prefix = null
    ) {
        $isAllowedToEdit = api_is_allowed_to_edit();
        $isPlatformAdmin = api_is_platform_admin();
        // Adding plugins depending of platform conditions
        $plugins = [];

        if (api_get_setting('show_glossary_in_documents') === 'ismanual') {
            $plugins[] = 'glossary';
        }

        if (api_get_setting('youtube_for_students') === 'true') {
            $plugins[] = 'youtube';
        } else {
            if (api_is_allowed_to_edit() || api_is_platform_admin()) {
                $plugins[] = 'youtube';
            }
        }

        if (api_get_setting('enabled_googlemaps') === 'true') {
            $plugins[] = 'leaflet';
        }

        if (api_get_setting('math_asciimathML') === 'true') {
            $plugins[] = 'asciimath';
        }

        if (api_get_setting('enabled_mathjax') === 'true') {
            $plugins[] = 'mathjax';
            $config['mathJaxLib'] = api_get_path(WEB_PUBLIC_PATH) . 'assets/MathJax/MathJax.js?config=TeX-MML-AM_HTMLorMML';
        }

        if (api_get_setting('enabled_asciisvg') === 'true') {
            $plugins[] = 'asciisvg';
        }

        if (api_get_setting('enabled_wiris') === 'true') {
            // Commercial plugin
            $plugins[] = 'ckeditor_wiris';
        }

        if (api_get_setting('enabled_imgmap') === 'true') {
            $plugins[] = 'mapping';
        }

        /*if (api_get_setting('block_copy_paste_for_students') == 'true') {
            // Missing
        }*/

        if (api_get_setting('more_buttons_maximized_mode') === 'true') {
            $plugins[] = 'toolbarswitch';
        }

        if (api_get_setting('allow_spellcheck') === 'true') {
            $plugins[] = 'scayt';
        }

        if (api_get_configuration_sub_value('ckeditor_vimeo_embed/config') && ($isAllowedToEdit || $isPlatformAdmin)) {
            $plugins[] = 'ckeditor_vimeo_embed';
        }

        if (api_get_setting('editor.ck_editor_block_image_copy_paste') === 'true') {
            $plugins[] = 'blockimagepaste';
        }
        $this->defaultPlugins = array_unique(array_merge($this->defaultPlugins, $plugins));
        $this->toolbarSet = $toolbar;
        parent::__construct($router, $toolbar, $config, $prefix);
    }

    /**
     * Get the toolbar config.
     *
     * @return array
     */
    public function getConfig()
    {
        $config = [];
        $customPlugins = '';
        $customPluginsPath = [];
        if (api_get_setting('editor.translate_html') === 'true') {
            $customPlugins .= ' translatehtml';
            $customPluginsPath['translatehtml'] = api_get_path(WEB_PUBLIC_PATH) . 'libs/editor/tinymce_plugins/translatehtml/plugin.js';
        }

        $plugins = [
            'advlist autolink lists link image charmap print preview anchor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime media table paste wordcount ' . $customPlugins,
        ];

        if ($this->getConfigAttribute('fullPage')) {
            $plugins[] = 'fullpage';
        }

        $config['plugins'] = implode(' ', $plugins);
        $config['toolbar'] = 'undo redo directionality | bold italic underline strikethrough | insertfile image media template link | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview save print | code codesample | ltr rtl | ' . $customPlugins;

        if (!empty($customPluginsPath)) {
            $config['external_plugins'] = $customPluginsPath;
        }

        $config['skin'] = false;
        $config['content_css'] = false;
        $config['branding'] = false;
        $config['relative_urls'] = false;
        $config['toolbar_mode'] = 'sliding';
        $config['autosave_ask_before_unload'] = true;
        $config['toolbar_mode'] = 'sliding';

        // enable title field in the Image dialog
        $config['image_title'] = true;
        // enable automatic uploads of images represented by blob or data URIs
        $config['automatic_uploads'] = true;
        // custom filepicker only to Image dialog
        $config['file_picker_types'] = 'file image media';

        $config['file_picker_callback'] = '[browser]';

        $iso = api_get_language_isocode();
        $languageConfig = $this->getLanguageConfig($iso);

        // Merge the language configuration
        $config = array_merge($config, $languageConfig);

        /*if (isset($this->config)) {
            $this->config = array_merge($config, $this->config);
        } else {
            $this->config = $config;
        }*/

        $this->config = $config;

        // $config['width'] = '100';
        $this->config['height'] = '300';

        return $this->config;
    }

    /**
     * @return array
     */
    public function getNewPageBlock()
    {
        return ['NewPage', 'Templates', '-', 'PasteFromWord', 'inserthtml'];
    }

    /**
     * Get the default toolbar configuration when the setting more_buttons_maximized_mode is false.
     *
     * @return array
     */
    protected function getNormalToolbar()
    {
        return null;
    }

    /**
     * Get the toolbar configuration when CKEditor is minimized.
     *
     * @return array
     */
    protected function getMinimizedToolbar()
    {
        return [
            $this->getNewPageBlock(),
            ['Undo', 'Redo'],
            [
                'Link',
                'Image',
                'Video',
                'Oembed',
                'Flash',
                'Youtube',
                'VimeoEmbed',
                'Audio',
                'Table',
                'Asciimath',
                'Asciisvg',
            ],
            ['BulletedList', 'NumberedList', 'HorizontalRule'],
            ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'],
            ['Styles', 'Format', 'Font', 'FontSize', 'Bold', 'Italic', 'Underline', 'TextColor', 'BGColor'],
            api_get_setting('enabled_wiris') === 'true' ? ['ckeditor_wiris_formulaEditor', 'ckeditor_wiris_CAS'] : [''],
            ['Toolbarswitch', 'Source'],
        ];
    }

    /**
     * Get the toolbar configuration when CKEditor is maximized.
     *
     * @return array
     */
    protected function getMaximizedToolbar()
    {
        return [
            $this->getNewPageBlock(),
            ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', 'inserthtml'],
            ['Undo', 'Redo', '-', 'SelectAll', 'Find', '-', 'RemoveFormat'],
            ['Link', 'Unlink', 'Anchor', 'Glossary'],
            [
                'Image',
                'Mapping',
                'Video',
                'Oembed',
                'Flash',
                'Youtube',
                'VimeoEmbed',
                'Audio',
                'leaflet',
                'Smiley',
                'SpecialChar',
                'Asciimath',
                'Asciisvg',
            ],
            '/',
            ['Table', '-', 'CreateDiv'],
            ['BulletedList', 'NumberedList', 'HorizontalRule', '-', 'Outdent', 'Indent', 'Blockquote'],
            ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'],
            ['Bold', 'Italic', 'Underline', 'Strike', '-', 'Subscript', 'Superscript', '-', 'TextColor', 'BGColor'],
            [api_get_setting('allow_spellcheck') === 'true' ? 'Scayt' : ''],
            ['Styles', 'Format', 'Font', 'FontSize'],
            ['PageBreak', 'ShowBlocks'],
            api_get_setting('enabled_wiris') === 'true' ? ['ckeditor_wiris_formulaEditor', 'ckeditor_wiris_CAS'] : [''],
            ['Toolbarswitch', 'Source'],
        ];
    }

    /**
     * Determines the appropriate language configuration for the editor.
     * Tries to load a specific language file based on the ISO code. If not found, it attempts to load a general language file.
     * Falls back to English if neither specific nor general language files are available.
     */
    private function getLanguageConfig(string $iso): array
    {
        $url = api_get_path(WEB_PATH);
        $sysUrl = api_get_path(SYS_PATH);
        $defaultLang = 'en';
        $defaultLangFile = "libs/editor/langs/{$defaultLang}.js";
        $specificLangFile = "libs/editor/langs/{$iso}.js";
        $generalLangFile = null;

        // Default configuration set to English
        $config = [
            'language' => $defaultLang,
            'language_url' => $defaultLangFile,
        ];

        if ($iso !== 'en_US') {
            // Check for a specific variant of the language (e.g., de_german2)
            if (str_contains($iso, '_')) {
                // Extract the general language code (e.g., de)
                list($generalLangCode) = explode('_', $iso, 2);
                $generalLangFile = "libs/editor/langs/{$generalLangCode}.js";
            }

            // Attempt to load the specific language file
            if (file_exists($sysUrl . $specificLangFile)) {
                $config['language'] = $iso;
                $config['language_url'] = $url . $specificLangFile;
            }

            // Fallback to the general language file if specific is not available
            elseif ($generalLangFile !== null && file_exists($sysUrl . $generalLangFile)) {
                $config['language'] = $generalLangCode;
                $config['language_url'] = $url . $generalLangFile;
            }
        }

        return $config;
    }
}
