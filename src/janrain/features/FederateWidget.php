<?php
/** MIT License */
namespace janrain\features;

use janrain\platform\Renderable;
use janrain\Feature;

class FederateWidget extends Feature implements Renderable
{
    protected static $OPTS_J = array(
        'sso.server' => array('IsUrl'),
        'sso.logoutUri' => array('IsUrl'),
        'sso.xdr' => array('IsUrl'),
        'sso.segment' => array('IsString'),
        'sso.supportedSegments' => array('IsStringArray'),
    );

    public static function renderXdcomm($returnString = false)
    {
        if ($returnString) {
            return file_get_contents(__DIR__ . '/federate/xdcomm.html');
        } else {
            readfile(__DIR__ . '/federate/xdcomm.html');
        }
    }


    /**
     * @inheritdoc
     */
    public function getPriority()
    {
        return 15;
    }

    /**
     * @inheritdoc
     */
    public function getHeadJsSrcs()
    {
        return array('//d1v9u0bgi1uimx.cloudfront.net/sso.js');
    }

    /**
     * @inheritdoc
     */
    public function getStartHeadJs()
    {
        $out = "// Initialize sso settings to support logout
            'object' != typeof window.janrain && (window.janrain = {plex:{}, settings:{capture:{}}});
            'object' != typeof janrain.plex && (janrain.plex = {});
            'object' != typeof janrain.settings && (janrain.settings = {});\n";
        if (in_array('EngageWidget', $this['features'])) {
            $out .= sprintf(
                "//
                janrain.plex.ssoLogout = function () {
                    JANRAIN.SSO.ENGAGE.logout({
                        sso_server: '%s'
                    });
                    window.location.href = '/';
                };\n",
                $this->cleanSetting($this['sso.server'])
            );
        }
        if (in_array('CaptureWidget', $this['features'])) {
            $out .= sprintf(
                "//
                janrain.plex.ssoLogout = function () {
                    localStorage && localStorage.removeItem('janrainCaptureToken');
                    localStorage && localStorage.removeItem('janrainCaptureToken_Expires');
                    localStorage && localStorage.removeItem('janrainCaptureProfileData');
                    JANRAIN.SSO.CAPTURE.logout({
                        sso_server: '%s'
                    });
                };\n",
                $this->cleanSetting($this['sso.server'])
            );
        }
        return $out;
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHeadJs()
    {
        $out = '';
        if (in_array('CaptureWidget', $this['features'])) {
            #capture
            $supportedSegments = $this['sso.supportedSegments'] ?: array();
            $out .= sprintf(
                "//
                janrain.settings.capture.federate = true;
                janrain.settings.capture.federateServer = '%s';
                janrain.settings.capture.federateXdReceiver = '%s';
                janrain.settings.capture.federateLogoutUri = '%s';
                janrain.settings.capture.federateSegment = '%s';
                janrain.settings.capture.federateSupportedSegments = '%s';
                janrain.settings.capture.federateEnableSafari = true;\n",
                $this->cleanSetting($this['sso.server']),
                $this->cleanSetting($this['sso.xdr']),
                $this->cleanSetting($this['sso.logoutUri']),
                $this->cleanSetting($this['sso.segment']),
                implode(',', $supportedSegments)
            );
        }
        return $out;
    }

    /**
     * @inheritdoc
     */
    public function getEndHeadJs()
    {
        if (!in_array('EngageWidget', $this['features'])) {
            return '';
        }
        $supportedSegments = $this['sso.supportedSegments'] ?: array();
        $out = "
            JANRAIN.SSO.ENGAGE.check_login({
                sso_server: '%s',
                token_uri: janrain.settings.tokenUrl,
                xd_receiver: '%s',
                segment: '%s',
                supported_segment: '%s',
                logout_uri: '%s'
            });\n";
        return sprintf(
            $out,
            $this['sso.server'],
            $this['sso.xdr'],
            $this['sso.segment'],
            implode(',', $supportedSegments),
            $this['sso.logoutUri']
        );
    }

    /**
     * @inheritdoc
     */
    public function getCssHrefs()
    {
        return array();
    }

    /**
     * @inheritdoc
     */
    public function getCss()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getHtml()
    {
        return '';
    }
}
