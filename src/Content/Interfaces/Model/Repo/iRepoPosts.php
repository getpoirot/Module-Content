<?php
namespace Module\Content\Interfaces\Model\Repo;

use Module\Content\Model\Entity\EntityPost;
use Module\Content\Model\Entity\MemberObject;


interface iRepoPosts
{
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
    function findOneByUID($uid);

    /**
     * Delete Entity With Given UID
     *
     * @param mixed $uid
     *
     * @return int Delete Count
     */
    function deleteOneByUID($uid);

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
