<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewAdminmainmenu */
?>

<div id="view-<?php echo hsc($this->view);?>" class="<?php $this->renderViewCssClasses();?>">
	<ul class="menu-list">
		<li class="menu-list-item item-admindashboard">
			<a class="menu-link" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=admindashboard');?>"><?php echo KText::_('Dashboard');?></a>
		</li>

		<li class="menu-list-item item-adminproducttree">
			<div class="trigger-toggle-sub-items closed"><?php echo KText::_('Products');?></div>
			<div class="sub-items closed">
				<div class="product-tree-wrapper"><?php KenedoView::getView('ConfigboxViewAdminproducttree')->display();?></div>
			</div>
		</li>

		<?php if (KenedoPlatform::getName() != 'magento' && KenedoPlatform::getName() != 'magento2') { ?>
			<li class="menu-list-item item-adminlistings"><a class="menu-link ajax-target-link" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminlistings');?>"><?php echo KText::_('Product Listings');?></a></li>
		<?php } ?>

		<li class="menu-list-item item-adminoptions"><a class="menu-link ajax-target-link" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminoptions');?>"><?php echo KText::_('Options');?></a></li>
		<li class="menu-list-item item-admincalculations"><a class="menu-link ajax-target-link" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=admincalculations');?>"><?php echo KText::_('Calculations');?></a></li>
		<li class="menu-list-item item-admintemplates"><a class="menu-link ajax-target-link" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=admintemplates');?>"><?php echo KText::_('Templates');?></a></li>
	</ul>

	<?php if (KenedoPlatform::getName() != 'magento' && KenedoPlatform::getName() != 'magento2') { ?>

		<ul class="menu-list">
			<li class="menu-list-item item-adminorders"><a class="menu-link ajax-target-link" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminorders');?>"><?php echo KText::_('Orders');?></a></li>
			<li class="menu-list-item item-admincustomers"><a class="menu-link ajax-target-link" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=admincustomers');?>"><?php echo KText::_('Customers');?></a></li>
			<li class="menu-list-item item-adminreviews"><a class="menu-link ajax-target-link" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminreviews');?>"><?php echo KText::_('Reviews');?></a></li>
		</ul>

		<ul class="menu-list">

			<li class="menu-list-item item-admincountries">
				<div class="trigger-toggle-sub-items closed"><?php echo KText::_('Countries');?></div>
				<div class="sub-items closed">
					<ul class="menu-list">
						<li class="menu-list-item item-admincountries"><a class="menu-link ajax-target-link" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=admincountries');?>"><?php echo KText::_('Countries');?></a></li>
						<li class="menu-list-item item-adminstates"><a class="menu-link ajax-target-link" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminstates');?>"><?php echo KText::_('States');?></a></li>
						<li class="menu-list-item item-admincounties"><a class="menu-link ajax-target-link" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=admincounties');?>"><?php echo KText::_('Counties');?></a></li>
						<li class="menu-list-item item-admincities"><a class="menu-link ajax-target-link" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=admincities');?>"><?php echo KText::_('Cities');?></a></li>
					</ul>
				</div>
			</li>

			<li class="menu-list-item item-adminshipping">
				<div class="trigger-toggle-sub-items closed"><?php echo KText::_('Shipping');?></div>
				<div class="sub-items closed">
					<ul class="menu-list">
						<li class="menu-list-item item-adminshippers"><a class="menu-link ajax-target-link" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminshippers');?>"><?php echo KText::_('Shippers');?></a></li>
						<li class="menu-list-item item-adminzones"><a class="menu-link ajax-target-link" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminzones');?>"><?php echo KText::_('Shipping Zones');?></a></li>
						<li class="menu-list-item item-adminshippingmethods"><a class="menu-link ajax-target-link" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminshippingmethods');?>"><?php echo KText::_('Shipping Methods');?></a></li>
					</ul>
				</div>
			</li>

			<li class="menu-list-item item-adminpaymentmethods"><a class="menu-link ajax-target-link" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminpaymentmethods');?>"><?php echo KText::_('Payment Methods');?></a></li>
			<li class="menu-list-item item-adminshopdata"><a class="menu-link ajax-target-link" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminshopdata');?>"><?php echo KText::_('Store Information');?></a></li>
			<li class="menu-list-item item-adminuserfields"><a class="menu-link ajax-target-link" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminuserfields');?>"><?php echo KText::_('Customer Fields');?></a></li>
			<li class="menu-list-item item-adminnotifications"><a class="menu-link ajax-target-link" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminnotifications');?>"><?php echo KText::_('Notifications');?></a></li>

		</ul>

	<?php } ?>

    <ul class="menu-list">
        <li class="menu-list-item item-adminconfig"><a class="menu-link ajax-target-link" href="<?php echo KLink::getRoute('index.php?option=com_configbox&controller=adminconfig');?>"><?php echo KText::_('Settings');?></a></li>
    </ul>

	<?php $this->renderView('extra_menu_items');?>

</div>