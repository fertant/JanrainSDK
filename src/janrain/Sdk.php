<?php
namespace janrain;

use janrain\Adapter;
use janrain\settings\Opts;
use janrain\Feature;
use janrain\platform\Renderable;

class Sdk
{
    const VERSION = '0.3.0';

    protected $features;
    protected $platform;

    public function __construct(Adapter $cms)
    {
        $this->features = new \SplObjectStorage();
        $this->platform = $cms;
    }

    public function __unset($name)
    {
        foreach ($this->features as $f) {
            if ($f->getName() == $name) {
                $this->features->detach($f);
            }
        }
        $this->updateFeatures();
    }

    public function __get($name)
    {
        foreach ($this->features as $f) {
            if ($f->getName() == $name) {
                return $f;
            }
        }
        return null;
    }

    public function getFeatures()
    {
        return $this->features;
    }

    public function getConfig()
    {
        return $this->platform;
    }

    /**
     * @todo rename to user errors
     */
    public function getValidationErrors()
    {
        $optsChecker = new \janrain\settings\Opts($this);
        $allErrors = $optsChecker->getUserErrorCodes();
        if (0 === count($allErrors)) {
            $allErrors = $optsChecker->getJanrainErrorCodes();
        }
        return $allErrors;
    }

    private function updateFeatures()
    {
        $newFeatures = array();
        foreach ($this->features as $f) {
            $rc = new \ReflectionClass($f);
            $newFeatures[] = $rc->getShortName();
        }
        $this->platform->setItem('features', array_keys(array_flip($newFeatures)));
    }

    public function addFeature(Feature $f)
    {
        $this->features->attach($f);
        $this->updateFeatures();
    }

    public function addFeatureByName($featureName)
    {
        if (!$this->$featureName) {
            $this->addFeature(Feature::forName($featureName, $this->platform));
        }
    }

    public function getJsSrcs()
    {
        $out = array();
        // pull only the renderable features
        $features = array_filter(iterator_to_array($this->features), function ($obj) {
            return $obj instanceof \janrain\platform\Renderable;
        });
        // order by render priority need to silence the sort because of
        @usort($features, function (Renderable $a, Renderable $b) {
            $pa = $a->getPriority();
            $pb = $b->getPriority();
            if ($pa == $pb) {
                return 0;
            }
            return $pa > $pb ? 1 : -1;
        });
        // render
        foreach ($features as $f) {
            $out += $f->getHeadJsSrcs();
        }
        return $out;
    }

    public function renderJs()
    {
        $startJs = '';
        $settingsJs = '';
        $endJs = '';
        // pull only the renderable features
        $features = array_filter(iterator_to_array($this->features), function ($obj) {
            return $obj instanceof \janrain\platform\Renderable;
        });
        // order by render priority need to silence the sort because of
        @usort($features, function (Renderable $a, Renderable $b) {
            $pa = $a->getPriority();
            $pb = $b->getPriority();
            if ($pa == $pb) {
                return 0;
            }
            return $pa > $pb ? 1 : -1;
        });
        // render
        foreach ($features as $f) {
            $startJs .= $f->getStartHeadJs();
            $settingsJs .= $f->getSettingsHeadJs();
            $endJs .= $f->getEndHeadJs();
        }
        return $startJs . $settingsJs . $endJs;
    }

    protected static $instance;

    // @todo throw exception if instance isn't ready
    public static function instance()
    {
        return self::$instance;
    }

    /**
     * Create and SDK for a specific Application.
     *
     * If no singleton exists, it will be set to the output of the first call to this method.
     *
     * @param janrain\Adapter $a
     *   The adapter which will speak to the specific platform
     *
     * @return JanrainSdk
     *   A JanrainSdk instance for this platform
     */
    public static function forAdapter(Adapter $a)
    {
        $featureList = $a->getItem('features');
        if (!is_array($featureList)) {
            $a->setItem('features', array());
        }
        $out = new self($a);
        foreach ($a->getItem('features') as $featureName) {
            $f = Feature::forName($featureName, $a);
            $out->features->attach($f);
        }
        if (empty(self::$instance)) {
            self::$instance = $out;
        }
        return self::instance();
    }

    public static function getUserAgent()
    {
        return sprintf(
            "JanrainPhpSdk/%s Guzzle/%s PHP/%s OS/%s",
            self::VERSION,
            \Guzzle\Common\Version::VERSION,
            PHP_VERSION,
            PHP_OS
        );
    }

    public static function getReferrer()
    {
        $host = @$_SERVER['HTTP_HOST'];
        if (empty($host) || !filter_var($host, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED)) {
            // no host or invalid host (localhost, etc) fallback to server_name
            return @$_SERVER['SERVER_NAME'] ?: gethostname();
        }
        return $host;
    }
}
