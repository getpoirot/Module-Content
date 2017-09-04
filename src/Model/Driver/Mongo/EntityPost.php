<?php
namespace Module\Content\Model\Driver\Mongo;

use Module\Content\Interfaces\Model\Entity\iEntityPost;
use Module\Content\Lib\FactoryContentObject;
use Module\Content\Model\Entity\EntityPost\GeoObject;
use Module\Content\Model\Entity\EntityPost\LikesObject;
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


    // ...

    /**
     * Constructs the object from a BSON array or document
     * Called during unserialization of the object from BSON.
     * The properties of the BSON array or document will be passed to the method as an array.
     * @link http://php.net/manual/en/mongodb-bson-unserializable.bsonunserialize.php
     * @param array $data Properties within the BSON array or document.
     */
    function bsonUnserialize(array $data)
    {
        if (isset($data['location']))
            // Unserialize BsonDocument to Required GeoObject from Persistence
            $data['location'] = new GeoObject($data['location']);

        if (isset($data['likes'])) {
            // Unserialize BsonDocument to Required LikesObject from Persistence
            $objLike = new LikesObject;
            $objLike->with($objLike::parseWith($data['likes']));
            $data['likes'] = $objLike;
        }

        if (isset($data['content'])) {
            // Unserialize BsonDocument to Required ContentObject from Persistence
            $contentType     = $data['content']['content_type'];
            unset($data['content']['content_type']);
            $contentObject   = FactoryContentObject::of($contentType, $data['content']);
            $data['content'] = $contentObject;

        }

        $this->import($data);
    }
}
