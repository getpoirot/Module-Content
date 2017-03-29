<?php
namespace Module\Content\Model\Driver\Mongo;

use Module\Content\Interfaces\Model\Entity\iEntityPost;
use Module\MongoDriver\Model\tPersistable;
use MongoDB\BSON\Persistable;
use MongoDB\BSON\UTCDatetime;


class EntityPost
    extends \Module\Content\Model\Entity\EntityPost
    implements iEntityPost
    , Persistable
{
    use tPersistable;


    /**
     * Set Unique Content Identifier
     *
     * @param mixed $uid
     *
     * @return $this
     */
    function setUid($uid)
    {
        $this->set_Id($uid);
        return $this;
    }

    /**
     * Get Content Unique Identifier
     * @ignore UId for Mongo persist as _id field
     *
     * @return mixed
     */
    function getUid()
    {
        return $this->get_Id();
    }


    // Mongonize DateCreated

    /**
     * Set Created Date
     *
     * @param UTCDatetime $date
     *
     * @return $this
     */
    function setDateTimeCreatedMongo(UTCDatetime $date)
    {
        $this->setDateTimeCreated($date->toDateTime());
        return $this;
    }

    /**
     * Get Created Date
     * note: persist when serialize
     *
     * @return UTCDatetime
     */
    function getDateTimeCreatedMongo()
    {
        $dateTime = $this->getDateTimeCreated();
        return new UTCDatetime($dateTime->getTimestamp() * 1000);
    }

    /**
     * @override Ignore from persistence
     * @ignore
     *
     * Date Created
     *
     * @return \DateTime
     */
    function getDateTimeCreated()
    {
        return parent::getDateTimeCreated();
    }
}
