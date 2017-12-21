<?php
/** MIT License */
namespace janrain\platform;

/**
* Janrain Internationalization class usage:
*     $_t = Janrain\Intl::createForLang('en_us')
*     $translatedString = $_t("string to be translated");
*
* In general strings should be in the format "[[marker]]Here is a phrase to be translated."
* [[marker]] is essentiallly an envelope created by a preprocessing callable to identify which version of the string
* needs to be called.  For example, a string with "%n% user record(s) found" needs multiple translations.  In this case
* there's a singular and plural in english, but other languages might change more grammar.
*/
class Intl
{

    protected $lang;
    protected $data;

    /**
     * @todo Implement loading translations from a file
     * @param string locale
     *   The locale name, preferably given by the platform, but could fallback to detecting PHP locale
     */
    public function __construct($lang)
    {
        $this->lang = $lang;
        #implement loading data from translation file or system cache.
    }

    /**
     * Callable Implementation
     *
     * All in one magic method used by $instance()
     *
     * @param string templateString
     *   __required__ The string to be translated.
     * @param mixed
     *   _optional_ A list of variables to be replaced into the string.  Names will be extracted from the string tag
     *   itself the first arg may be a callable function to be executed against the translation string prior to variable
     *   replacement if the first arg is not a callable, it will be considered a data value.  Args must be given in the
     *   order in which they appear in the translation string and will be mapped in the translated string by the names
     *   given in the string to be translated
     *
     * @return string
     *   the translated string
     */
    public function __invoke($templateString)
    {
        #make sure we're translating a string
        if (!is_string($templateString)) {
            throw new \InvalidArgumentException();
        }
        #we've got a string, check for preprocessor
        $args = func_get_args();
        if (count($args) > 1) {
            $langProc = $args[1];
            if (is_callable($langProc)) {
                #pre-processor found, run it this string with this special function
                $args[0] = $langProc($templateString);
                unset($args[1]);
            }
            #preprocessor run (if needed), all args[0] should be the translation phrase, and all other args should be
            #replacements
            return call_user_func_array(array($this, 'translate'), $args);
        }
        return $this->translate($templateString);
    }

    /**
     * Where the magic happens.
     */
    protected function translate()
    {
        return func_get_arg(0);
    }

    /**
     * Factory method for creating instances of translators.  Implements self-caching so we don't parse a translation
     * file more than once.
     */
    public static function createForLang($lang = 'en_us')
    {
        static $xlations = array();
        if (empty($xlations[$lang])) {
            $xlations[$lang] = new static($lang);
        }
        return $xlations[$lang];
    }
}
