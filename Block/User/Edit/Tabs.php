<?php
/**
 * Netstarter Pty Ltd.
 *
 * @category    CameraHouse
 *
 * @author      Netstarter Team <contact@netstarter.com>
 * @copyright   Copyright (c) 2016 Netstarter Pty Ltd. (http://www.netstarter.com.au)
 */

namespace CameraHouse\User\Block\User\Edit;

/**
 * Class Tabs.
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Class constructor.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('page_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('User Information'));
    }

    /**
     * Preferencing _beforeToHtml function.
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'main_section',
            [
                'label' => __('User Info'),
                'title' => __('User Info'),
                'content' => $this->getLayout()->createBlock('Magento\User\Block\User\Edit\Tab\Main')->toHtml(),
                'active' => true,
            ]
        );

        $this->addTab(
            'roles_section',
            [
                'label' => __('User Role'),
                'title' => __('User Role'),
                'content' => $this->getLayout()->createBlock(
                    'Magento\User\Block\User\Edit\Tab\Roles',
                    'user.roles.grid'
                )->toHtml(),
            ]
        );

        $this->addTab(
            'stores_section',
            [
                'label' => __('User Store'),
                'title' => __('User Store'),
                'content' => $this->getLayout()->createBlock('CameraHouse\User\Block\User\Edit\Tab\Stores')->toHtml(),
            ]
        );

        return parent::_beforeToHtml();
    }
}
