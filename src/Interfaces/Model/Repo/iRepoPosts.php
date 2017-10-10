<?php
namespace Module\Content\Interfaces\Model\Repo;

use Module\Content\Model\Entity\EntityPost;
use Module\Content\Model\Entity\MemberObject;


/**
 * Note: When you retrieve posts consider to sort newest at first.
 *
 */
interface iRepoPosts
{
    /**
     * Generate next unique identifier to persist
     * data with
     *
     * @param null|string $id
     *
     * @return mixed
     */
    function attainNextIdentifier($id = null);

    /**
     * Persist Content Post
     *
     * - check given entity identifier not exists; must be unique
     * - if entity has no identifier used ::nextIdentifier
     *   to assign something new
     *
     * @param EntityPost $entity
     *
     * @return EntityPost Contains inserted uid
     */
    function insert(EntityPost $entity);

    /**
     * Save Entity By Insert Or Update
     *
     * @param EntityPost $entity
     *
     * @return EntityPost
     */
    function save(EntityPost $entity);

    /**
     * Find Match By Given UID
     *
     * @param string|mixed $uid
     *
     * @return EntityPost|false
     */
    function findOneMatchUid($uid);

    /**
     * Delete Entity With Given UID
     *
     * @param mixed $uid
     *
     * @return int Delete Count
     */
    function deleteOneMatchUid($uid);

    /**
     * Lock Entity With Given UID
     *
     * @param mixed $uid
     *
     * @return mixed
     */
    function lockOneMatchUid($uid);

    /**
     * Find Entities Match With Given Expression
     *
     * @param array    $expression Filter expression
     * @param int|null $offset
     * @param int|null $limit
     *
     * @return \Traversable
     */
    function findAll($expression, $offset = null, $limit = null);

    /**
     * Find All Match By Given Owner UIDs List
     *
     * @param string $ownerIdentifier Owner Identifier
     * @param array  $expression      Filter expression
     * @param string $offset          Offset is MongoID
     * @param int    $limit
     *
     * @return \Traversable
     */
    function findAllMatchWithOwnerId($ownerIdentifier, $expression = null, $offset = null, $limit = null);

    /**
     * Get Count All Match By Given Owner UID
     *
     * @param string $ownerIdentifier Owner Identifier
     *
     * @return int
     */
    function getCountMatchWithOwnerId($ownerIdentifier);

    /**
     * Find All Match By Given UIDs List
     *
     * @param []mixed      $uids
     * @param array|string $expression Filter expression
     *
     * @return \Traversable
     */
    function findAllMatchUidWithin($uids, $expression = null);

    /**
     * Set a Like On Post By Given ID
     *
     * @param string       $content_id
     * @param MemberObject $member
     *
     * @return EntityPost\LikesObject
     */
    function insertLikeEntry($content_id, MemberObject $member);

    /**
     * Remove a Like On Post By Given ID
     *
     * @param string       $content_id
     * @param MemberObject $member
     *
     * @return EntityPost\LikesObject
     */
    function removeLikeEntry($content_id, MemberObject $member);
}
