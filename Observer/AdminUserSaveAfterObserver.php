<?php
/**
 * Netstarter Pty Ltd.
 *
 * @category    CameraHouse
 *
 * @author      Netstarter Team <contact@netstarter.com>
 * @copyright   Copyright (c) 2016 Netstarter Pty Ltd. (http://www.netstarter.com.au)
 */

namespace CameraHouse\User\Observer;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use CameraHouse\StoreLocator\Helper\Data;

/**
 * Class Admin_User_Save_After_Observer.
 */
class AdminUserSaveAfterObserver implements ObserverInterface
{
    /**
     * Resource.
     *
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * CameraHouse store locator helper.
     *
     * @var \CameraHouse\StoreLocator\Helper\Data
     */
    protected $cameraHouseHelper;

    /**
     * AdminUserSaveAfterObserver constructor.
     * @param Context $context
     * @param ResourceConnection $resource
     * @param Data $cameraHouseHelper
     */
    public function __construct(
        Context $context,
        ResourceConnection $resource,
        Data $cameraHouseHelper
    ) {
        $this->cameraHouseHelper = $cameraHouseHelper;
        $this->resource = $resource;
    }

    /**
     * Execute function.
     * @param Observer $observer
     * @return null
     */
    public function execute(Observer $observer)
    {
        $connection = $this->resource->getConnection();

        //Store manager
        $newStores = (array)$observer->getDataObject()->getStoreIds();
        $userId = (int)$observer->getDataObject()->getUserId();
        $storeScope = $observer->getDataObject()->getStoreScope();

        if ($storeScope != null) {
            //Prevent deleting date from save admin users in user roles action
            $storeScope = (int)$storeScope;

            $genericUserRole = $this->cameraHouseHelper->getConfigSuperUserRoleId();
            $roleId = (int)$observer->getDataObject()->getRoleId();
            $tableUsers = $this->resource->getConnection()->getTableName('ns_store_locator_users');

            if ($genericUserRole != $roleId) {
                $oldStores = $this->lookupStoreIds($userId);

                if ($storeScope == '0') {
                    //Set all stores accessible
                    //Delete all assigned stores
                    $where = ['user_id = ?' => $userId, 'store_locator_id IN (?)' => $oldStores];
                    $connection->delete($tableUsers, $where);
                } else {
                    //Set only selected stores accessible
                    if (!empty($newStores)) {
                        $insert = array_diff($newStores, $oldStores);
                        $delete = array_diff($oldStores, $newStores);

                        if ($delete) {
                            $where = ['user_id = ?' => $userId, 'store_locator_id IN (?)' => $delete];
                            $connection->delete($tableUsers, $where);
                        }

                        if ($insert) {
                            $data = [];
                            foreach ($insert as $storeLocatorId) {
                                $data[] = ['user_id' => $userId, 'store_locator_id' => (int)$storeLocatorId];
                            }

                            $connection->insertMultiple($tableUsers, $data);
                        }
                    }
                }
            } else {
                $oldStores = $this->lookupStoreIds($userId);
                //Delete all assigned stores
                $where = ['user_id = ?' => $userId, 'store_locator_id IN (?)' => $oldStores];
                $connection->delete($tableUsers, $where);
            }
        }
    }

    /**
     * Get store ids to which specified item is assigned.
     * @param $userId
     * @return array
     */
    public function lookupStoreIds($userId)
    {
        $connection = $this->resource->getConnection();

        $select = $connection->select()->from(
            $this->resource->getConnection()->getTableName('ns_store_locator_users'),
            'store_locator_id'
        )->where(
            'user_id = ?',
            (int)$userId
        );

        return $connection->fetchCol($select);
    }
}
