<?php
/** MIT License */
namespace janrain\features;

use janrain\Feature;
use janrain\platform\Renderable;
use janrain\platform\FeatureStack;

class CaptureWidget extends Feature implements Renderable
{
    public static $OPTS_J = array(
        'capture.clientId' => array('NotEmpty'),
        'capture.captureServer' => array('IsUrl'),
        'capture.redirectUri' => array('IsUrl'),
        'externalId' => array('NotEmpty'));

    /**
     * {@inheritdoc}
     */
    public function getHeadJsSrcs()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getStartHeadJs()
    {
        $out = "// Initialize Registration settings (no clobber)
            'object' != typeof window.janrain && (window.janrain = {plex:{}, settings:{capture:{}}});
            'object' != typeof janrain.plex && (janrain.plex = {});
            'object' != typeof janrain.settings && (janrain.settings = {capture:{}});
            'object' != typeof janrain.settings.capture && (janrain.settings.capture = {});
            'object' != typeof janrain.settings.capture.beforeJanrainCaptureWidgetOnLoad
                && (janrain.settings.capture.beforeJanrainCaptureWidgetOnLoad = []);\n";
        return $out;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsHeadJs()
    {
        $settings = $this->config;
        $server = $this->cleanSetting($settings->getItem('capture.captureServer'));
        $locale = $this->cleanSetting($settings->getLocale()) ?: 'en';
        $externalId = $this->cleanSetting($settings->getItem('externalId'));
        $clientId = $this->cleanSetting($settings->getItem('capture.clientId'));
        $appId = $this->cleanSetting($settings->getItem('capture.appId'));
        $redirectUri = $this->cleanSetting($settings->getItem('capture.redirectUri'));

        $current_url = $settings::getSessionItem('capture.currentUri');
        $current_url = empty($current_url)
            ? "window.location.href"
            : "'{$current_url}'";
        $out =
            "janrain.settings.capture.captureServer = '{$server}';
            janrain.settings.capture.appId = '{$appId}';
            janrain.settings.capture.clientId = '{$clientId}';
            window.httpLoadUrl  = 'http://widget-cdn.rpxnow.com/load/{$externalId}';
            window.httpsLoadUrl = 'https://rpxnow.com/load/{$externalId}';
            janrain.settings.language = '{$locale}';
            // mobile-enabled settings for mobile app web ui
            janrain.settings.tokenAction = 'url';
            janrain.settings.popup = false;
            janrain.settings.tokenUrl = window.location.href;
            janrain.settings.capture.redirectUri = {$current_url};
            janrain.settings.capture.redirectFlow = true;
            // end mobile-enabled settings
            janrain.settings.capture.responseType = 'code';
            janrain.settings.capture.beforeJanrainCaptureWidgetOnLoad.push(function () {
                janrain.plex.refreshToken();
                janrain.events.onCaptureLoginSuccess.addHandler(function (evt) {
                    if (typeof janrain.plex.login === 'function') {
                        janrain.plex.login(evt.authorizationCode);
                    }
                });
                janrain.events.onCaptureProfileSaveSuccess.addHandler(function (evt) {
                    if (typeof janrain.plex.profileUpdate === 'function') {
                        janrain.plex.profileUpdate(evt);
                    }
                });
            });\n";
        return $out;
    }

    /**
     * {@inheritdoc}
     */
    public function getEndHeadJs()
    {
        return "//End Janrain CaptureWidget output\n";
    }

    /**
     * {@inheritdoc}
     */
    public function getCssHrefs()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getCss()
    {
        #todo enable css customization by file upload or database storage.
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getHtml()
    {
        return "<a href='javascript:;' id='captureSignInLink' class='capture_modal_open'>Sign In / Sign Up</a>\n";
    }

    /**
     * @inheritsDoc
     *
     * Returns 5
     */
    public function getPriority()
    {
        return 5;
    }

    /**
     * Validate a zip file containing Registration Widget assets
     *
     * Checks to make sure the following files are present at the top-level of the zip file.
     *
     * **janrain-init.js** - The main Registration configuration.  Included in header, overrides SDK settings
     *
     * **janrain-utils.js** - Extra functions or client customizations.  Loads AFTER init.js.
     *
     * **screens.html** - All of the markup for the flow specified in init.js.
     *
     * **janrain-ie.css** - The default MSIE-only conditional stylesheet.
     *
     * **janrain-mobile.css** - The default mobile-only conditional stylesheet.
     *
     * **janrain.css** - The default stylesheet.
     *
     * @param string $pathToZip
     *   The path to the zip file to be analyzed.
     *
     * @return Array
     *   An array of error messages.
     */
    public function validateAssetsZip($pathToZip)
    {
        static $files = array('janrain-ie.css', 'janrain-init.js', 'janrain-mobile.css', 'janrain-utils.js',
            'janrain.css','screens.html');
        $errors = array();
        $zip = new \ZipArchive();
        // quick fail if file is not a zip or is currupted
        if (true !== $zip->open($pathToZip, \ZipArchive::CHECKCONS)) {
            $errors[] = 'Cannot open zip or zip is corrupted.';
            return $errors;
        }
        // zipfile itself is okay
        foreach ($files as &$f) {
            if (false === $zip->locateName($f)) {
                $errors[] = sprintf('"%s" not found', $f);
            }
        }
        return $errors;
    }
}
