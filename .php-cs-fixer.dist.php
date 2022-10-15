<?php
$finder = PhpCsFixer\Finder::create()
	->in([__DIR__ . '/joomla'])
	->in([__DIR__ . '/magento']);

$config = new PhpCsFixer\Config();
return $config->setRules([
	'@PSR12' => true,
	'@PHP81Migration' => true
])->setFinder($finder);
