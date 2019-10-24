<?php
defined('CB_VALID_ENTRY') or die();

$db = KenedoPlatform::getDb();

if (ConfigboxUpdateHelper::tableExists('#__configbox_xref_group_payment_method') == false) {
	$query = "
	CREATE TABLE `#__configbox_xref_group_payment_method`
	(
		payment_id MEDIUMINT UNSIGNED not null,
		group_id MEDIUMINT UNSIGNED not null,
		primary key (payment_id, group_id),
		
		constraint fk_payment_id
			foreign key (payment_id) references #__configbox_payment_methods (id)
				on update cascade on delete cascade,
				
		constraint fk_group_id
			foreign key (group_id) references #__configbox_groups (id)
				on update cascade on delete cascade
	) 
	ENGINE=InnoDB
	";
	$db->setQuery($query);
	$db->query();

	$query = "SELECT `id` FROM `#__configbox_groups`";
	$db->setQuery($query);
	$groupIds = $db->loadResultList();

	$query = "SELECT `id` FROM `#__configbox_payment_methods`";
	$db->setQuery($query);
	$paymentIds = $db->loadResultList();

	$items = array();

	foreach ($paymentIds as $paymentId) {
		foreach ($groupIds as $groupId) {
			$items[] = '('.$paymentId.', '.$groupId.')';
		}
	}

	if (!empty($items)) {
		$query = "
		INSERT INTO `#__configbox_xref_group_payment_method`
		(`payment_id`, `group_id`)
		VALUES ".implode(',', $items)."
		";
		$db->setQuery($query);
		$db->query();
	}

}