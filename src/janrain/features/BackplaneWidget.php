<?php
namespace janrain\features;

use janrain\Feature;
use janrain\platform\Renderable;

class BackplaneWidget extends Feature implements Renderable
{
    protected static $OPTS_J = array(
        'backplane.serverBaseUrl' => array('IsUrl'),
        'backplane.busName' => array('NotEmpty'));

    public function getHeadJsSrcs()
    {
        return array('//d134l0cdryxgwa.cloudfront.net/backplane.js');
    }

    public function getStartHeadJs()
    {
        return "";
    }

    public function getSettingsHeadJs()
    {
        $out = '';
        if (in_array('CaptureWidget', $this->config->getItem('features'))) {
            $out .= sprintf(
                "//backplane settings
                janrain.settings.capture.backplane = true;
                janrain.settings.capture.backplaneBusName = '%s';
                janrain.settings.capture.backplaneServerBaseUrl = '%s';",
                $this->cleanSetting($this->config->getItem('backplane.busName')),
                $this->cleanSetting($this->config->getItem('backplane.serverBaseUrl'))
            );
        } else {
            $out .= "
            // Bind ready callback. Note, must be called after widget is present
            janrain.settings.beforeJanrainWidgetOnLoad.push(function () {
                Backplane(function () {
                    janrain.engage.signin.setBackplaneChannel(Backplane.getChannelID());
                });
            });";
        }
        return $out;
    }

    public function getEndHeadJs()
    {
        $out = '';
        if (in_array('EngageWidget', $this->config->getItem('features'))) {
            $out .= sprintf(
                "\nif (!Backplane.getChannelName()) {
                    Backplane.init({serverBaseURL: '%s',busName: '%s'});
                }\n",
                $this->config->getItem('backplane.serverBaseUrl'),
                $this->config->getItem('backplane.busName')
            );
        }
        return $out;
    }
    public function getCssHrefs()
    {
        return array();
    }
    public function getHtml()
    {
        return '';
    }
    public function getPriority()
    {
        return 100;
    }
    public function getCss()
    {
        return '';
    }
}
