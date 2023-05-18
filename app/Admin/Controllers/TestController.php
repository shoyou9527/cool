<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Layout\Content;
use Picqer\Barcode\BarcodeGeneratorPNG;

class TestController extends Controller
{
    public function index(Content $content)
    {
        $generator = new BarcodeGeneratorPNG();

        return $content
            ->title('條碼範例')
            ->body('<img src="data:image/png;base64,' . base64_encode($generator->getBarcode('FUCK YOU ASSHOLE', $generator::TYPE_CODE_39, 1, 50)) . '">');
    }

    // ... other methods
}