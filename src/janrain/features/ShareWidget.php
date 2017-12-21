<?php
namespace janrain\features;

use janrain\Feature;
use janrain\platform\Renderable;

class ShareWidget extends Feature implements Renderable
{

    const FORM_BAR = 'bar';
    const FORM_DRAWER = 'drawer';
    const COUNT_MODE_NONE = 'none';
    const COUNT_MODE_COMBINED = 'combined';
    const COUNT_MODE_PROVIDER = 'provider';
    const ORIENT_VERTICAL = 'vertical';
    const ORIENT_HORIZONTAL = 'horizontal';
    const MODE_BROADCAST = 'broadcast';
    const MODE_CONTACT = 'contact';

    protected static $OPTS_J = array(
        'appUrl' => array('IsUrl'),
        'social.providers' => array('IsStringArray'));

    /**
     * {@inheritdoc}
     */
    public function getHeadJsSrcs()
    {
        return array('//cdn-social.janrain.com/social/janrain-social.min.js');
    }

    /**
     * {@inheritdoc}
     */
    public function getStartHeadJs()
    {
        $out = "// Initialize Share settings (noclobber)
            'object' != typeof window.janrain && (window.janrain = {plex:{}, settings:{capture:{}}});
            'object' != typeof janrain.plex && (janrain.plex = {});
            'object' != typeof janrain.settings && (janrain.settings = {});
            'object' != typeof janrain.settings.social && (janrain.settings.social = {});\n";
        return $out;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsHeadJs()
    {
        $appUrl = $this->cleanSetting($this['appUrl']);
        $providers = implode("','", $this['social.providers']);
        $out = sprintf("
            janrain.settings.appUrl = '%s';
            janrain.settings.social.providers = ['%s'];\n", $appUrl, $providers);
        return $out;
    }

    /**
     * {@inheritdoc}
     */
    public function getEndHeadJs()
    {
        return '';
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
     *
     * This accepts an array who's keys are the widget attributes and values.
     * When PHP 5.6 is allowed, this will support variadic tuples.
     *
     * @todo: add keys that used to be javascript-only settings
     */
    public function getHtml(array $opts = array())
    {
        // preset allowed keys to prevent key name injection
        static $validKeys = array(
            'description', 'image', 'message', 'mode', 'shorten-url', 'subject',
            'title', 'url',
            // extras
            'orientation', 'formFactor', 'shareCountMode');
        // strip bad keys
        $options = array_intersect_key($opts, array_flip($validKeys));
        //validate enums
        $this->testOption(
            'formFactor',
            $options,
            array(self::FORM_DRAWER, self::FORM_BAR)
        );
        $this->testOption(
            'orientation',
            $options,
            array(self::ORIENT_HORIZONTAL, self::ORIENT_VERTICAL)
        );
        $this->testOption(
            'mode',
            $options,
            array(self::MODE_CONTACT, self::MODE_BROADCAST)
        );
        $this->testOption(
            'shareCountMode',
            $options,
            array(self::COUNT_MODE_NONE, self::COUNT_MODE_PROVIDER, self::COUNT_MODE_COMBINED)
        );
        $out = "
        <div class='janrainSocialPlaceholder'\n";
        foreach ($options as $name => &$value) {
            switch ($name) {
                case 'formFactor':
                    $name = 'form-factor';
                    break;
                case 'shareCountMode':
                    $name = 'share-count-mode';
                    break;
                default:
                    // don't mess
                    break;
            }
            $out .= sprintf(
                " data-janrain-%s='%s'\n",
                htmlentities($name, ENT_QUOTES, 'UTF-8', true),
                htmlspecialchars($value, ENT_QUOTES, 'UTF-8', true)
            );
        }
        $out .= "></div>\n";
        return $out;
    }

    protected function testOption($name, $optionArray, $acceptedValues)
    {
        if (array_key_exists($name, $optionArray)
                && !in_array($optionArray[$name], $acceptedValues)) {
            throw new \DomainException("Invalid $name!");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return 110;
    }
}
