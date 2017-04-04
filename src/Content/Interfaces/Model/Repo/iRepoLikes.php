<?php
namespace Module\Content\Interfaces\Model\Repo;

use Module\Content\Interfaces\Model\Entity\iEntityLike;


interface iRepoLikes
{
    /**
     * Save Like Entity
     *
     * - like entity with same [model,item,owner] fields
     *   must only store once
     *
     * @param iEntityLike $entity
     *
     * @return iEntityLike|null Return Clone copy if changed otherwise given Entity
     */
    function save(iEntityLike $entity);

    /**
     * Remove Like Entity
     *
     * - like entity with same [model,item,owner] fields
     *
     * @param iEntityLike $entity
     *
     * @return int Removed Count
     */
    function remove(iEntityLike $entity);

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

    /**
     * Find Entities Liked By Owner In Model X
     *
     * @param mixed  $owner_identifier
     * @param string $model
     * @param int|null $skip
     * @param int|null $limit
     *
     * @return \Traversable
     */
    function findAllItemsOfOwnerAndModel($owner_identifier, $model, $skip = null, $limit = null);
}
