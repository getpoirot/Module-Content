<?php
namespace Module\Content\Model\Entity\EntityPost;

use Traversable;


class MediaObject
    implements \IteratorAggregate
{
    /** @var array */
    protected $mediaMeta;


    /**
     * Set Media Meta Data
     *
     * @param array|\Traversable $metaData
     *
     * @return $this
     */
    function setMediaMeta($metaData)
    {
        if ($metaData instanceof \Traversable)
            $metaData = \Poirot\Std\cast($metaData)->toArray();

        $this->mediaMeta = $metaData;
        return $this;
    }

    /**
     * Stream Wrapper Link to Bindata To Retrieve Content
     * note: usually link must provide on-the-fly using media extend
     *
     * @return string|null http://bin/54d3w345
     */
    function get_Link()
    {
        // TODO implement
        return 'http://server/media/'.$this->mediaMeta['$bindata']['hash'];
    }

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new \ArrayIterator(
            array_merge($this->mediaMeta, ['_link' => $this->get_Link()])
        );
    }
}
