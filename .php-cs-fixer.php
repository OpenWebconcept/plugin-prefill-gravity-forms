<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
	->notPath('vendor')
	->notPath('node_modules')
	->in(__DIR__)
	->in('./resources/views')
	->in('./config')
	->name('*.php')
	->notName('*.blade.php')
	->ignoreDotFiles(true)
	->ignoreVCS(true);

return (new PhpCsFixer\Config)
	->setRules([
		'@PSR2' => true,
		'array_syntax' => [
			'syntax' => 'short',
		],
		'ordered_imports' => [
			'sort_algorithm' => 'alpha',
		],
		'no_unused_imports' => true,
		'binary_operator_spaces' => [
			'default' => 'single_space',
			'operators' => [
				'=>' => 'single_space',
				'|' => 'no_space',
			],
		],
		'full_opening_tag' => true,
		'yoda_style' => [
			'always_move_variable' => true,
			'equal' => true,
			'identical' => true,
			'less_and_greater' => true,
		],
	])
	->setFinder($finder);
