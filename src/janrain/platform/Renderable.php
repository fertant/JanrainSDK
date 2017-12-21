<?php
/** MIT License */
namespace janrain\platform;

/**
 * This interface represents the pre-render contract for all renderable Plex features.
 */
interface Renderable
{
    /**
     * Get an array of external scripts.
     *
     * Likely these will be dependent libraries and should be rendered by the CMS prior to
     * the getStartHeadJs(), getSettingsHeadJs(), getEndHeadJs() chain.
     *
     * @return Array
     *   An array of external script sources.  Empty array if not needed.
     */
    public function getHeadJsSrcs();

    /**
     * Start the JS Output. The string representing the start of JUMP javascript to be placed within HTML Head tag.
     * Does not contain opening <script>.
     *
     * @return string
     *   Empty string if not needed.
     */
    public function getStartHeadJs();

    /**
     * Get the javascript settings for this Renderable JUMP object. A block of settings in the form
     * "janrain.settings.package.option = 'value'\n".
     *
     * @return string
     *   Empty string if no settings required.
     */
    public function getSettingsHeadJs();

    /**
     * Get the closing javascript content and load.js src. The closing block of head javascript.
     * Does not include a </script>.
     *
     * @return string
     *   Empty string if not needed.
     */
    public function getEndHeadJs();

    /**
     * Get the hrefs of the external CSS this renderable requires.
     *
     * @return Array
     *   A list of hrefs to be included by the platform or wrapped by the
     *   renderer in link tags.  Empty array if not needed.
     */
    public function getCssHrefs();

    /**
     * Get the inline style needed for this renderable.
     *
     * @return string
     *   Raw css to be added to the page.  Does not include opening or closing style tags.  Empty string if no css
     *   needed.
     */
    public function getCss();

    /**
     * Get the body content of this Renderable.
     *
     * @return string
     *   The markup to be added to page body (usually invisible divs for modals and widgets). Empty string if no body
     *   content
     *   is necessary.
     */
    public function getHtml();

    public function getPriority();
}
