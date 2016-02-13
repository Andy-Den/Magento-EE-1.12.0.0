<?php
class SL_Signaturelink_Block_Adminhtml_Whitelist_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('signaturelink_whitelist_grid');
		$this->setDefaultSort('entity_id');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('signaturelink/whitelist')->getCollection()->addFieldToFilter('active', array('eq' => 1));

		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('entity_id', array(
			'header'	=> Mage::helper('signaturelink')->__('Whitelist Entry #'),
			'index'		=> 'entity_id',
            'column_css_class' => 'entity_id'
		));

        $this->addColumn('whitelist_email', array(
            'header'	=> Mage::helper('signaturelink')->__('Whitelist Email'),
            'index'		=> 'whitelist_email',
            'column_css_class' => 'whitelist_email'
        ));

        $this->addColumn('admin_responsible', array(
            'header'	=> Mage::helper('signaturelink')->__('Admin User Responsible'),
            'index'		=> 'admin_responsible',
            'column_css_class' => 'admin_responsible'
        ));

		$this->addColumn('created_at', array(
			'header'	=> Mage::helper('signaturelink')->__('Added On'),
			'align'		=> 'right',
			'index'		=> 'created_at',
			'type'		=> 'datetime',
            'column_css_class' => 'created_at'
		));

		$this->addColumn('action', array(
			'header'	=> Mage::helper('signaturelink')->__('Action'),
			'type'		=> 'action',
			'getter'	=> 'getEntityId',
			'actions'	=> Array(
				Array(
					'caption'	=> Mage::helper('signaturelink')->__('Edit'),
					'url'		=> array('base' => '*/signaturelink/grid'),
					'field'		=> 'entity_id'
				)
			),
			'filter'	=> false,
			'sortable'	=> false,
			'index'		=> 'stores',
            'column_css_class' => 'delete',
			'is_system' => true
		));

		return parent::_prepareColumns();
	}

}
