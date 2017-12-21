<?php
/** MIT License */
namespace janrain\features;

use janrain\Feature;
use janrain\platform\Renderable;

class EngageWidget extends Feature implements Renderable
{
    protected static $OPTS_J = array(
        'appUrl' => array('IsUrl'),
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
        $out = "// Initialize Login settings (no clobber)
            'object' != typeof window.janrain && (window.janrain = {plex:{}, settings:{capture:{}}});
            'object' != typeof janrain.plex && (janrain.plex = {});
            'object' != typeof janrain.settings && (janrain.settings = {});
            'array' != typeof janrain.settings.beforeJanrainWidgetOnLoad
                && (janrain.settings.beforeJanrainWidgetOnLoad = []);\n";
        return $out;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsHeadJs()
    {
        $locale = $this->cleanSetting($this->config->getLocale());
        $out = "\njanrain.settings.tokenUrl = window.location.href;
            janrain.settings.tokenAction = 'event';
            janrain.settings.language = '$locale';\n";
        return $out;
    }

    /**
     * {@inheritdoc}
     */
    public function getEndHeadJs()
    {
        $externalId = $this->cleanSetting($this->config->getItem('externalId'));
        $tpl = file_get_contents(__DIR__ . '/engagewidget/engagewidget.tpl.js');
        return sprintf($tpl, $externalId);
    }

    public function getPriority()
    {
        return 5;
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
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getHtml()
    {
        $out = "<div id='janrainEngageEmbed'></div>";
        return $out;
    }
}
