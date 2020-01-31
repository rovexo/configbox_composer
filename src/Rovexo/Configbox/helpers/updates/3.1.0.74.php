<?php
defined('CB_VALID_ENTRY') or die();

$constraintName = ConfigboxUpdateHelper::getFkConstraintName('#__cbcheckout_order_records', 'user_id');
$db = KenedoPlatform::getDb();
if ($constraintName) {

    $query = "alter table #__cbcheckout_order_records drop foreign key `".$constraintName."`";
    $db->setQuery($query);
	$db->query();

}

$constraintName = ConfigboxUpdateHelper::getFkConstraintName('#__cbcheckout_order_users', 'order_id');

if ($constraintName) {

    $query = "alter table #__cbcheckout_order_users drop foreign key `$constraintName`";
    $db->setQuery($query);
	$db->query();

	$query = "
	alter table #__cbcheckout_order_users
    add constraint `".$constraintName."` foreign key (order_id) references #__cbcheckout_order_records (id);
	";
	$db->setQuery($query);
	$db->query();

}