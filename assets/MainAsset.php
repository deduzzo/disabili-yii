<?php

namespace app\assets;

use yii\web\AssetBundle;
use yii\web\JqueryAsset;
use yii\web\YiiAsset;

class MainAsset extends AssetBundle
{
	public $sourcePath = __DIR__ . '/assets/dist';
	public $baseUrl = '@web';

	public $js = [
		'assets/dist/app.min.js',
	];

	public $css = [
		'app.min.css',
		'app-dark.min.css',
        'assets/original_assets/extensions/simple-datatables/style.css',
        'assets/original_assets/compiled/css/table-datatable.css',
	];

	public $publishOptions = [];

	public $depends = [
		YiiAsset::class,
		JqueryAsset::class,
	];
}
