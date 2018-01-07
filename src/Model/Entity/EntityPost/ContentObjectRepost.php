<?php
namespace Module\Content\Model\Entity\EntityPost;

use Module\Content\Interfaces\Model\Entity\iEntityPostContentObject;
use Module\Content\Lib\FactoryContentObject;
use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Module\Content\Model\Entity\EntityPost;
use Module\Content\Model\Entity\EntityPostBase;


class ContentObjectRepost
    extends ContentObjectPlain
{
    const CONTENT_TYPE = 'repost';

    /** @var iRepoPosts */
    protected $repoPosts;
    /** @var EntityPost */
    protected $entityPost = null;

    /** @var mixed */
    protected $owner;
    /** @var mixed */
    protected $uid;
    /** @var iEntityPostContentObject */
    protected $content;
    /** @var \DateTime */
    protected $datetimeCreated;


    /**
     * ContentObjectRepost constructor.
     *
     * @param array|\Traversable    $options
     * @param iRepoPosts            $repoPosts @IoC /module/content/services/repository/Posts
     */
    function __construct(iRepoPosts $repoPosts, array $options = null)
    {
        $this->repoPosts = $repoPosts;
        parent::__construct($options);
    }


    /**
     * Usage before creating the repost. Not while retrieving from data source,
     *  So there is no getOriginalContentId implemented.
     *
     * @param string $originalContentId
     *
     * @throws \RuntimeException
     * @return $this
     */
    function setOriginalContentId($originalContentId)
    {
        $this->entityPost = $this->repoPosts->findOneMatchUid($originalContentId);
        if ( ! ($this->entityPost instanceof EntityPost))
            throw new \RuntimeException(sprintf("Invalid Original Content Id = %s", (string)$originalContentId));

        ## Re-Posting a Re-Post?
        #
        if ($this->entityPost->getContent() instanceof ContentObjectRepost)
        {
            $originalContentId = $this->entityPost->getContent()->getUid();
            return $this->setOriginalContentId($originalContentId);
        }

        return $this;
    }

    /**
     * @override Show Content Type Before Any Other When Converting Into Array
     *           better json response to client
     *
     * @inheritdoc
     */
    function getContentType()
    {
        return self::CONTENT_TYPE;
    }

    /**
     * @override Show Description Before Any Other When Converting Into Array
     *           better json response to client
     *
     * @inheritdoc
     */
    function getDescription()
    {
        return parent::getDescription();
    }

    /**
     * @override
     *
     * @inheritdoc
     */
    function with(array $options, $throwException = false)
    {
        if (isset($options['content']))
        {
            $contentType        = $options['content']['content_type'];
            unset($options['content']['content_type']);
            /** @var iEntityPostContentObject $contentObject */
            $contentObject      = FactoryContentObject::of($contentType, $options['content']);
            $options['content'] = $contentObject;
        }

        parent::with($options, $throwException);
    }

    /**
     * Set Owner (Created By)
     * @see EntityPost
     *
     * @param mixed $ownerIdentifier
     *
     * @return $this
     */
    function setOwnerIdentifier($ownerIdentifier)
    {
        $this->owner = $ownerIdentifier;
        return $this;
    }

    /**
     * Get Owner Identifier
     * @see EntityPost
     *
     * @return mixed
     */
    function getOwnerIdentifier()
    {
        if ($this->entityPost instanceof EntityPost)
            $this->owner = $this->entityPost->getOwnerIdentifier();

        return $this->owner;
    }

    /**
     * Set Unique Content Identifier
     * @see EntityPostBase
     *
     * @param mixed $uid
     *
     * @return $this
     */
    function setUid($uid)
    {
        $this->uid = $uid;
        return $this;
    }

    /**
     * Get Content Unique Identifier
     * @see EntityPostBase
     *
     * @return mixed
     */
    function getUid()
    {
        if ($this->entityPost instanceof EntityPost)
            $this->uid = $this->entityPost->getUid();

        return $this->uid;
    }

    /**
     * Set Post Content
     * @see EntityPostBase
     *
     * @param iEntityPostContentObject $content
     *
     * @return $this
     */
    function setContent(iEntityPostContentObject $content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get Key/Value Content
     * @see EntityPostBase
     *
     * @return iEntityPostContentObject
     */
    function getContent()
    {
        if ($this->entityPost instanceof EntityPost)
            $this->content = $this->entityPost->getContent();

        return $this->content;
    }

    /**
     * Set Created Timestamp
     * @see EntityPostBase
     *
     * @param \DateTime|null $dateTime
     *
     * @return $this
     */
    function setDateTimeCreated($dateTime)
    {
        $this->datetimeCreated = $dateTime;
        return $this;
    }

    /**
     * Get Date Time Created
     * @see EntityPostBase
     *
     * @return \DateTime
     */
    function getDateTimeCreated()
    {
        if ($this->entityPost instanceof EntityPost)
            $this->datetimeCreated = $this->entityPost->getDateTimeCreated();

        return $this->datetimeCreated;
    }
}
