<?php
namespace Module\Content\Model\Driver\Mongo;

use Module\Content\Interfaces\Model\Entity\iEntityLike;
use Module\MongoDriver\Model\tPersistable;
use MongoDB\BSON\Persistable;
use MongoDB\BSON\UTCDatetime;


class EntityLike
    extends \Module\Content\Model\Entity\EntityLike
    implements iEntityLike
    , Persistable
{
    use tPersistable;


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
