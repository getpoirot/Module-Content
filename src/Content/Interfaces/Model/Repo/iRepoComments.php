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
     * Find Match By Given UID
     *
     * @param string|mixed $uid
     *
     * @return iEntityComment|false
     */
    function findOneMatchUid($uid);

    /**
     * Find Entities Match With Given Identifier And Model
     *
     * @param mixed    $item_identifier
     * @param string   $model
     * @param int|null $skip
     * @param int|null $limit
     *
     * @return \Traversable
     */
    function findByItemIdentifierOfModel($item_identifier, $model, $skip = null, $limit = null);

}
