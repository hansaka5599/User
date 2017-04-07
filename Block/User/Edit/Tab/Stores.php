<?php
/**
 * Netstarter Pty Ltd.
 *
 * @category    CameraHouse
 *
 * @author      Netstarter Team <contact@netstarter.com>
 * @copyright   Copyright (c) 2016 Netstarter Pty Ltd. (http://www.netstarter.com.au)
 */

namespace CameraHouse\User\Block\User\Edit\Tab;

use CameraHouse\StoreLocator\Helper\Data;
use CameraHouse\StoreLocator\Model\Users;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Netstarter\StoreLocator\Model\Store;

/**
 * Class Stores.
 */
class Stores extends Generic
{
    /**
     * Store model.
     *
     * @var Store
     */
    protected $storeModel;

    /**
     * CameraHouse User model.
     *
     * @var Users
     */
    protected $cameraHouseUsersModel;

    /**
     * CameraHouse Helper.
     *
     * @var Data
     */
    protected $cameraHouseHelper;

    /**
     * Stores constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Store $storeModel
     * @param Users $cameraHouseUsersModel
     * @param Data $cameraHouseHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $storeModel,
        Users $cameraHouseUsersModel,
        Data $cameraHouseHelper,
        array $data = []
    ) {
        $this->storeModel = $storeModel;
        $this->cameraHouseUsersModel = $cameraHouseUsersModel;
        $this->cameraHouseHelper = $cameraHouseHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form fields.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        $userId = $this->_coreRegistry->registry('permissions_user')->getUserId();
        $assignedStores = $this->cameraHouseHelper->getMyAssignedStores($userId);

        $setData = ['store_scope' => 0, 'store_ids' => []];
        if (!empty($assignedStores)) {
            $setData['store_scope'] = 1;
            $setData['store_ids'] = $assignedStores;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('user_');

        $baseFieldset = $form->addFieldset('base_fieldset', ['legend' => __('User Stores')]);

        $baseFieldset->addField(
            'store_scope',
            'select',
            [
                'name' => 'store_scope',
                'label' => __('Store Scope'),
                'title' => __('Store Scope'),
                'required' => true,
                'values' => $this->getStoreScopeOptionsArray(),
            ]
        );

        $baseFieldset->addField(
            'store_ids',
            'multiselect',
            [
                'name' => 'store_ids[]',
                'label' => __('Stores'),
                'title' => __('Stores'),
                'required' => true,
                'values' => $this->cameraHouseHelper->getAllStoreOptionsArray(),
                'disabled' => false,
            ]
        );

        $form->setValues($setData);
        $this->setForm($form);

        $this->setChild('form_after', $this->getLayout()
            ->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence')
            ->addFieldMap('user_store_scope', 'user_store_scope')
            ->addFieldMap('user_store_ids', 'user_store_ids')
            ->addFieldDependence('user_store_ids', 'user_store_scope', 1));

        return parent::_prepareForm();
    }

    /**
     * Get Store scopes.
     * @return array
     */
    public function getStoreScopeOptionsArray()
    {
        $options[] = ['label' => __('Any'), 'value' => '0'];
        $options[] = ['label' => __('Custom'), 'value' => '1'];

        return $options;
    }
}
