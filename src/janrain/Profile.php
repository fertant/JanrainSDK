<?php
namespace janrain;

use Peekmo\JsonPath\JsonStore;

class Profile
{
    /**
     * A reference to the raw json_decoded profile.
     */
    private $raw;

    /**
     * The Peekmo\JsonPath\JsonStore which does the jsonpath processing.
     */
    private $jso;


    /**
     * Create new customer profile object.
     *
     * Works entirely in references, and stores references internally since
     * profiles can get large.  This also means direct profile modifications
     * will be reflected in subsequent queries.
     *
     * @param Array $data
     *   The data array representing this customer profile.  Note, the array root
     *   must be the top-level object of the profile data. For example:
     *   ```array('identifier' => 'googleurl', 'displayName' => 'bob')```
     *   not
     *   ```array('result' => array('id' => 'stuff'))```
     *   nor
     *   ```array('profile' => array('id' => 'stuff'))```
     */
    public function __construct(array $data)
    {
        $this->raw = &$data;
        $this->jso = new JsonStore();
    }


    /**
     * Extract arbitrary data points using a jsonpath expression.
     *
     * Wrapper for the underlying JsonStore, always run the query against the
     * internal array reference.  Also sanitize the input to avoid non-specific
     * JsonPath failures.
     *
     * @param string $jsonPath
     *   The jsonpath expression.
     *
     * @throws DomainException
     *   If $jsonPath is not a string, we throw to prevent underlying JsonPath
     *   shallow fail mechanism.
     *
     * @return Array
     *   The results of the path query as an array.
     */
    public function get($jsonPath)
    {
        if (!is_string($jsonPath)) {
            throw new \DomainException('jsonpath must be string, ' . gettype($jsonPath) . ' given.');
        }
        return $this->jso->get($this->raw, $jsonPath);
    }

    /**
     * Extract singular data using jsonpath expression
     *
     * Convenience method for just grabbing a single value.  Will grab the first
     * if more than one value is returned from jsonpath.
     *
     * @param string $jsonPath
     *   The jsonpath expression.
     *
     * @throws DomainException
     *   JsonPath must be a string
     *
     * @return mixed
     *   Will return a single value (no arrays or objects).
     */
    public function getFirst($jsonPath)
    {
        $out = $this->get($jsonPath);
        if ($out) {
            // non-empty array, return the first entry
            return $out[0];
        }
        // pass thru the empty array.
        return $out;
    }

    /**
     * Extract unique visitor identifiers
     *
     * Convenience method for getting customer id's from the underlying profile.
     * For Capture profiles it grabs uuid and all identifiers from the profile
     * plural.  For Engage profiles it just grabs the identifier.
     *
     * @return Array
     *   The raw profile values unescaped.  Note: uuid's contain dashes and social identifiers are URLs.
     */
    public function getIdentifiers()
    {
        // extract capture identifers.
        return array_merge(
            // capture uuid
            $this->get('$.uuid'),
            // capture social ids from profiles plural
            $this->get('$.profiles[*].identifier'),
            // engage social id
            $this->get('$.profile.identifier')
        );
    }

    /**
     * Dump profile to json.
     *
     * Convenience method for serializing.  Unserialize with new Profilejson_decode($string, true).
     *
     * @return string
     *   The JSON encoded profile.
     */
    public function __toString()
    {
        return json_encode($this->raw);
    }
}
