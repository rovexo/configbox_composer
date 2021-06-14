<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewAdminorders */
?>
<div <?php echo $this->getViewAttributes();?>>
<div class="kenedo-listing-form" data-view="<?php echo hsc($this->view);?>">

	<?php if (!empty($this->pageTitle)) { ?>
		<h1 class="kenedo-page-title"><?php echo hsc($this->pageTitle);?></h1>
	<?php } ?>
	<?php echo (count($this->pageTasks)) ? KenedoViewHelper::renderTaskItems($this->pageTasks, 'list') : ''; ?>
	
	<div class="tasks-and-filters">

		<div class="kenedo-filters">
			<div class="kenedo-filter-list form-inline">
				<div class="kenedo-filter input-group"><input class="listing-filter form-control"
				                                              placeholder="<?php echo KText::_('Search for Customer');?>"
				                                              type="text"
				                                              value="<?php echo hsc($this->filters['filter_nameorder']);?>"
				                                              name="filter_nameorder"
				                                              id="filter_nameorder" />

					<a class="kenedo-search input-group-append">
						<span class="input-group-text"><?php echo KText::_('Search');?></span>
					</a>
				</div>

				<div class="kenedo-filter input-group"><input class="listing-filter form-control datepicker"
				                                              placeholder="<?php echo KText::_('Date from');?>"
				                                              type="text"
				                                              value="<?php echo hsc($this->filters['filter_startdate']);?>"
				                                              name="filter_startdate"
				                                              id="filter_startdate" />

					<a class="kenedo-search input-group-append">
						<span class="input-group-text"><?php echo KText::_('Search');?></span>
					</a>
				</div>

				<div class="kenedo-filter input-group"><input class="listing-filter form-control datepicker"
				                                              placeholder="<?php echo KText::_('Date until');?>"
				                                              type="text"
				                                              value="<?php echo hsc($this->filters['filter_enddate']);?>"
				                                              name="filter_enddate"
				                                              id="filter_enddate" />

					<a class="kenedo-search input-group-append">
						<span class="input-group-text"><?php echo KText::_('Search');?></span>
					</a>
				</div>

				<div class="kenedo-filter input-group">
					<?php echo $this->statusDropdown;?>
				</div>

			</div>
		</div>

		
		
	</div>
	
	<div class="clear"></div>

	<div class="kenedo-messages">
		<div class="kenedo-messages-error">
			<?php if (KenedoViewHelper::getMessages('error')) { ?>
				<ul>
					<?php foreach ( KenedoViewHelper::getMessages('error') as $message ) { ?>
						<li><?php echo $message; ?></li>
					<?php } ?>
				</ul>
			<?php } ?>
		</div>
		
		<div class="kenedo-messages-notice">
			<?php if (KenedoViewHelper::getMessages('notice')) { ?>
				<ul>
					<?php foreach ( KenedoViewHelper::getMessages('notice') as $message ) { ?>
						<li><?php echo $message; ?></li>
					<?php } ?>
				</ul>
			<?php } ?>
		</div>
	</div>

	
	<table class="kenedo-listing">
	<thead>
		<tr>
			<th class="field-id" style="width:60px">
				<input type="checkbox" name="checkall" class="kenedo-check-all-items" />

				<a data-property-name="o.id"
				   data-current-direction="<?php echo ($this->listingData['listing_order_dir']);?>"
				   class="trigger-order-list <?php echo ($this->listingData['listing_order_property_name'] == 'o.id') ? 'active':'inactive';?> <?php echo ($this->listingData['listing_order_dir'] == 'desc') ? 'direction-desc' : 'direction-asc';?>">
					<?php echo hsc(KText::_('ID'));?>
				</a>

			</th>
			<th class="field-order">
				<?php echo KText::_( 'Order' ); ?>
			</th>
			<th class="field-customer">

				<a data-property-name="a.billinglastname"
				   data-current-direction="<?php echo ($this->listingData['listing_order_dir']);?>"
				   class="trigger-order-list <?php echo ($this->listingData['listing_order_property_name'] == 'a.billinglastname') ? 'active':'inactive';?> <?php echo ($this->listingData['listing_order_dir'] == 'desc') ? 'direction-desc' : 'direction-asc';?>">
					<?php echo hsc(KText::_('Customer'));?>
				</a>

			</th>
			<th class="field-status">

				<a data-property-name="o.status"
				   data-current-direction="<?php echo ($this->listingData['listing_order_dir']);?>"
				   class="trigger-order-list <?php echo ($this->listingData['listing_order_property_name'] == 'o.status') ? 'active':'inactive';?> <?php echo ($this->listingData['listing_order_dir'] == 'desc') ? 'direction-desc' : 'direction-asc';?>">
					<?php echo hsc(KText::_('Status'));?>
				</a>

			</th>
			<th class="field-created">

				<a data-property-name="o.created_on"
				   data-current-direction="<?php echo ($this->listingData['listing_order_dir']);?>"
				   class="trigger-order-list <?php echo ($this->listingData['listing_order_property_name'] == 'o.created_on') ? 'active':'inactive';?> <?php echo ($this->listingData['listing_order_dir'] == 'desc') ? 'direction-desc' : 'direction-asc';?>">
					<?php echo hsc(KText::_('Time Created'));?>
				</a>

			</th>
		</tr>			
	</thead>
	<?php foreach ($this->orders as $order) { ?>
		<tr>
			
			<td class="field-id">
				<input type="checkbox" name="cid[]" class="kenedo-item-checkbox" value="<?php echo (int)$order->id;?>">
				<span><?php echo (int)$order->id;?></span>
			</td>
			
			<td>
				<a class="listing-link" href="<?php echo KLink::getRoute( 'index.php?option=com_configbox&controller=adminorders&task=edit&id='. $order->id ); ?>"><?php echo KText::_('ADMIN_ORDERS_LINK_DISPLAY_ORDER'); ?></a>
			</td>
			
			<td class="field-customer">
				<?php if ($order->user_id) {
					if (!$order->billinglastname) {
						$text = KText::_('No customer account');
						?>
						<span><?php echo KText::_('No customer account');?></span>
						<?php
					}
					else {
						$text = $order->billinglastname . ' '. $order->billingfirstname . (($order->billingcompanyname) ? ' - '.$order->billingcompanyname : '');
						$returnUrl = KLink::base64UrlEncode( KLink::getRoute('index.php?option='.hsc($this->component).'&controller='.hsc($this->controllerName), false) );
						?>
						<a class="listing-link" href="<?php echo KLink::getRoute( 'index.php?option=com_configbox&controller=admincustomers&task=edit&id='. intval($order->user_id) .'&return='.$returnUrl); ?>"><?php echo hsc($text); ?></a>
						<?php
					}
					?>
				<?php } else { ?>
					<span><?php echo KText::_('No customer account');?></span>
				<?php } ?>	
			</td>
			
			<td class="field-status" style="white-space:nowrap">
				<?php echo hsc($order->statusCodeString); ?>
			</td>
			
			<td class="field-created" style="white-space:nowrap">
				<?php echo hsc(KenedoTimeHelper::getFormatted( $order->created_on, 'datetime' )); ?>
			</td>
		</tr>
	<?php } ?>
	</table>
	
	<div class="kenedo-pagination">
		<?php echo $this->pagination;?>
	</div>

	<div class="clear"></div>

	<div class="kenedo-hidden-fields">

		<?php foreach ($this->listingData as $key=>$data) { ?>
			<div class="listing-data listing-data-<?php echo $key;?>" data-key="<?php echo $key;?>" data-value="<?php echo $data;?>"></div>
		<?php } ?>
		<!-- unencoded return url: "<?php echo KLink::base64UrlDecode($this->listingData['return']);?>" -->

	</div>
	
</div>
</div>