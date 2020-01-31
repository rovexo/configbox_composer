<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewAdminorders */
?>
<div <?php echo $this->getViewAttributes();?>>
<div id="view-<?php echo hsc($this->view);?>" class="<?php $this->renderViewCssClasses();?>">
<div class="kenedo-listing-form">
	
	<?php if (!empty($this->pageTitle)) { ?><h1 class="kenedo-page-title"><?php echo hsc($this->pageTitle);?></h1><?php } ?>
	
	<?php echo (count($this->pageTasks)) ? KenedoViewHelper::renderTaskItems($this->pageTasks) : ''; ?>
	
	<div class="tasks-and-filters">
			
		<div class="kenedo-filters">
			<div class="kenedo-filter-list form-inline">
				
				<div class="kenedo-filter input-group">
					<input class="listing-filter form-control" placeholder="<?php echo KText::_('Search for Customer');?>" type="text" name="filter_nameorder" id="filter_nameorder" value="<?php echo $this->lists['filter_nameorder'];?>" />
			 		<a class="kenedo-search btn btn-default input-group-addon"><?php echo KText::_('Search');?></a>
			 	</div>
			 	<div class="kenedo-filter input-group" style="width:auto">
				 	<?php echo KenedoHtml::getCalendar('filter_startdate', $this->lists['filter_startdate'], NULL, 'listing-filter', KText::_('Date from'));?>	 	
				</div>
				<div class="kenedo-filter input-group" style="width:auto;margin-left:5px;">
				 	<?php echo KenedoHtml::getCalendar('filter_enddate', $this->lists['filter_enddate'], NULL, 'listing-filter', KText::_('Date until'));?>
			 		<a class="kenedo-search backend-button-small"><?php echo KText::_('Search');?></a>
				</div>
				
			 	<div class="kenedo-filter input-group">
				 	<?php echo $this->statusDropdown;?>
				 </div>
				 
				 <div class="clear"></div>
		 	</div>
		 	<div class="clear"></div>
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

	
	<table class="kenedo-listing" cellspacing="0">
	<thead>
		<tr>
			<th class="field-id" style="width:60px">
				<input type="checkbox" name="checkall" class="kenedo-check-all-items" />
				<?php echo KenedoHtml::getListingOrderHeading('o.id', KText::_('ID'), $this->orderingInfo);?>
			</th>
			<th class="field-order" style="width:50px">
				<?php echo KText::_( 'Order' ); ?>
			</th>
			<th class="field-customer" style="white-space:nowrap">
				<?php echo KenedoHtml::getListingOrderHeading('a.billinglastname', KText::_('Customer'), $this->orderingInfo);?>
			</th>
			<th class="field-status" style="width:130px;white-space:nowrap">
				<?php echo KenedoHtml::getListingOrderHeading('o.status', KText::_('Status'), $this->orderingInfo);?>
			</th>
			<th class="field-created" style="width:100px;white-space:nowrap">
				<?php echo KenedoHtml::getListingOrderHeading('o.created_on', KText::_('Time Created'), $this->orderingInfo);?>
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
				<a class="listing-link" href="<?php echo KLink::getRoute( 'index.php?option=com_configbox&controller=adminorders&task=edit&id='. $order->id ); ?>"><?php echo KText::_('Open'); ?></a>
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
		<input type="hidden" id="order_field"	name="listing_order_property_name" 	value="<?php echo $this->orderingInfo[0]['propertyName'];?>"/>
		<input type="hidden" id="order_dir" 	name="listing_order_dir" 	value="<?php echo $this->orderingInfo[0]['direction'];?>" />
		<input type="hidden" id="start" 		name="limitstart"			value="<?php echo $this->paginationInfo['start'];?>" />
		<input type="hidden" id="option" 		name="option" 				value="<?php echo hsc($this->component);?>" />
		<input type="hidden" id="controller"	name="controller" 			value="<?php echo hsc($this->controllerName);?>" />
		<input type="hidden" id="task" 			name="task" 				value="display" />
		<input type="hidden" id="tmpl"			name="tmpl" 				value="<?php echo KRequest::getString('tmpl','index');?>" />
		<input type="hidden" id="lang"			name="lang" 				value="<?php echo substr(KenedoPlatform::p()->getLanguageTag(),0,2);?>" />
		<input type="hidden" id="Itemid"		name="Itemid" 				value="<?php echo KRequest::getInt('Itemid',0);?>" />
		
		<?php 
		$listingData = array(
			'base-url'				=> KLink::getRoute('index.php?option='.hsc($this->component).'&controller='.hsc($this->controllerName).'&lang='.hsc(KText::getLanguageCode())),
			'option'				=> hsc(KRequest::getKeyword('option','')),
			'task'					=> 'display',
			'ajax_sub_view'			=> ($this->isAjaxSubview()) ? '1':'0',
			'tmpl'					=> hsc(KRequest::getKeyword('tmpl','component')),
			'in_modal'				=> hsc(KRequest::getInt('in_modal','0')),
			'format'				=> 'raw',
			
			'limitstart'			=> hsc($this->paginationInfo['start']),
			'limit'					=> hsc($this->paginationInfo['limit']),
			'listing_order_property_name'	=> hsc($this->orderingInfo[0]['propertyName']),
			'listing_order_dir'		=> hsc($this->orderingInfo[0]['direction']),
			'return'				=> KLink::base64UrlEncode( KLink::getRoute('index.php?option='.hsc($this->component).'&controller='.hsc($this->controllerName).'&lang='.hsc(KText::getLanguageCode()), false) ),
			'add-link'				=> KLink::base64UrlEncode( KLink::getRoute('index.php?option='.hsc($this->component).'&controller='.hsc($this->controllerName).'&task=edit', false) ),
			'ids'					=> '',
			'ordering-items'		=> '',
			);
	
		$listingData['add-link'] = KLink::base64UrlEncode( KLink::getRoute('index.php?option='.hsc($this->component).'&controller='.hsc($this->controllerName).'&task=edit&return='.$listingData['return'], false) );
	
		
		foreach ($listingData as $key=>$data) {
			?>
			<div class="listing-data listing-data-<?php echo $key;?>" data-key="<?php echo $key;?>" data-value="<?php echo $data;?>"></div>
			<?php
		}
		?>
	
	</div>
	
</div>
</div>
</div>