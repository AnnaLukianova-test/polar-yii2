<?php

namespace app\assets;

use yii\bootstrap5\BootstrapAsset;
use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
    ];
    public $depends = [
        BootstrapAsset::class,
    ];
}
