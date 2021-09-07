<?php
namespace Academy\CustomIndex\Model\Indexer;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;


class CustomIndexer implements MviewActionInterface, IndexerActionInterface
{
    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {

    }
    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     *
     * @return void
     */
    public function executeList(array $ids)
    {

    }
    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     *
     * @return void
     */
    public function executeRow($id)
    {

    }
    /**
     * Execute materialization on ids entitiesss
     *
     * @param int[] $ids
     *
     * @return void
     * @api
     */
    public function execute($ids)
    {

    }
}
