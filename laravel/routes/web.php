<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

use App\Models\Permission\Organization;
use Illuminate\Support\Facades\DB;

$router->get('/', function () use ($router) {
    return 'Ruigu TMS api';
});


$router->get('/log', function () use ($router) {
    $filePath = base_path('storage/logs/lumen.log');

    $ee = '';
    if (file_exists($filePath)) {
        $totalLine = hLineCount($filePath);

        #echo $startLine, ' ',$endLine,'--';
        #echo $lineCount, ' ',$lineBefore;

        $logData = array_reverse(gGetFileLine($filePath, 0, $totalLine, true));
        foreach ($logData as $log) {
            $ee .= $log;
        }
    }
    echo '<span style="width: 1000px;height: 50px">'.$ee.'</span>';
});
