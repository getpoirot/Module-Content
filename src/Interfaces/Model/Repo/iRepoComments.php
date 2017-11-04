<?php
namespace Module\Content\Interfaces\Model\Repo;

use Module\Content\Interfaces\Model\Entity\iEntityComment;


interface iRepoComments
{
    /**
     * Insert Comment Entity
     *
     * @param iEntityComment $entity
     *
     * @return iEntityComment Include persistence insert identifier
     */
    function insert(iEntityComment $entity);

    /**
     * Save Entity By Insert Or Update
     *
     * @param iEntityComment $entity
     *
     * @return mixed
     */
    function save(iEntityComment $entity);

    /**
     * Remove a Comment Entity
     *
     * @param iEntityComment $entity
     *
     * @return int
     */
    function remove(iEntityComment $entity);

    /**
     * Soft Remove a Comment Entity
     *
     * @param iEntityComment $entity
     *
     * @return boolean
     */
    function removeSoftly(iEntityComment $entity);

    /**
     * Find Match By Given UID
     *
     * @param string|mixed $uid
     *
     * @return iEntityComment|false
     */
    function findOneMatchUid($uid);

    /**
     * Retrieve All Active Comments For An Entity Model
     *
     * @param string $model
     * @param mixed $itemIdentifier
     * @param mixed $offset
     * @param int $limit
     *
     * @return \Traversable
     */
    function findAllCommentsFor($model, $itemIdentifier, $offset = null, $limit = 30);

    /**
     * Find Entities Match With Given Expression
     *
     * @param array $expression Filter expression
     * @param int|null $offset
     * @param int|null $limit
     *
     * @return \Traversable
     */
    function findAll($expression, $offset = null, $limit = null);

}


