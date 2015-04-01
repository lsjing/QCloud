<?php
require_once('./QCloud.php');

$zone = "pek1";
$action = "DescribeInstances";
$action = "StartInstances";
$action = "StopInstances";
$id = "i-2a1lutyn";

$q = new QCloud(array(
	'zone'=>$zone,
	'action'=>$action,
	'instances.n'=>$id
	));

$q->run();

