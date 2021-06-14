<?php
defined('CB_VALID_ENTRY') or die();
/**
 * @var $this KenedoView
 */
?>

<?php if (!empty($this->pageTitle)) { ?>
	<h1 class="kenedo-page-title"><?php echo hsc($this->pageTitle);?></h1>
<?php } ?>

<?php if ($this->contentAfterTitle) { ?>
	<div class="kenedo-after-title"><?php echo $this->contentAfterTitle;?></div>
<?php } ?>

<div class="tasks-and-filters">

	<?php echo (count($this->pageTasks)) ? KenedoViewHelper::renderTaskItems($this->pageTasks, 'list') : ''; ?>
	
	<?php if (isset($this->filterInputs) && count($this->filterInputs)) { ?>
		<div class="kenedo-filters">
			<div class="kenedo-filter-list form-inline">
				<?php foreach ($this->filterInputs as $key=>$filterInput) { ?>
					<div class="kenedo-filter input-group <?php echo hsc(str_replace('.', '_', $key));?>"><?php echo $filterInput;?></div>
				<?php } ?>
			</div>
		</div>
	<?php } ?>

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

<table class="kenedo-listing <?php echo (KenedoViewHelper::canSortItems($this->records, $this->properties, $this->orderingInfo)) ? 'sortable-listing' : 'unsortable-listing';?>">

	<thead>
		<tr>
			<?php foreach ($this->properties as $property) { ?>
				<th class="field-<?php echo hsc($property->propertyName);?>"<?php echo ($property->getPropertyDefinition('listingwidth')) ? ' style="width:'.hsc($property->getPropertyDefinition('listingwidth')).'"' : '';?>>
					<?php echo $property->getHeaderCellContentInListingTable($this->orderingInfo);?>
				</th>
			<?php } ?>
		</tr>			
	</thead>

	<tbody>
		<?php
		// This takes care of sortable list items. Groups are records that have the same parent id, they are wrapped in <tbody> tags, sorting is limited within these.
		$groupKey = KenedoViewHelper::getGroupingKey($this->properties);
		$lastGroupId = NULL;
		?>

		<?php foreach ($this->records as $record) { ?>

			<?php if ($groupKey && $record->$groupKey != $lastGroupId && $lastGroupId !== NULL) { ?>
				</tbody>
				<tbody>
			<?php } ?>

			<tr id="item-id-<?php echo intval($record->id);?>" class="item-row" data-item-id="<?php echo intval($record->id);?>">
				<?php foreach ($this->properties as $property) { ?>
					<td class="field-<?php echo hsc($property->propertyName);?>"><?php echo $property->getCellContentInListingTable($record);?></td>
				<?php } ?>
			</tr>

			<?php
			if ($groupKey) {
				$lastGroupId = $record->$groupKey;
			}
			?>

		<?php } ?>
	</tbody>

</table>

<?php if (trim($this->pagination)) { ?>
	<div class="kenedo-pagination"><?php echo $this->pagination;?><div class="clear"></div></div>
<?php } ?>

<div class="kenedo-hidden-fields">
	
	<?php foreach ($this->listingData as $key=>$data) { ?>
		<div class="listing-data listing-data-<?php echo $key;?>" data-key="<?php echo $key;?>" data-value="<?php echo $data;?>"></div>
	<?php } ?>
	<!-- uncoded return url: "<?php echo KLink::base64UrlDecode($this->listingData['return']);?>" -->
	<!-- uncoded add-link url: "<?php echo KLink::base64UrlDecode($this->listingData['add-link']);?>" -->
	
</div>