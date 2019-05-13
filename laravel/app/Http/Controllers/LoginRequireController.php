<?php
/**
 * Created by PhpStorm.
 * Index: xiangbohua
 * Date: 14/07/2017
 * Time: 14:20
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginRequireController extends SuperController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);

        $this->middleware('verify');
        $this->middleware('actionlog');
    }

    protected function auto_complete_data(\Closure $listProvider)
    {
        try {
            if (!isset($this->input['search'])) {
                $para = '';
            } else {
                $para = $this->input['search'];
            }

            if (isset($this->input['limit']) && is_numeric($this->input['limit'])) {
                $limit = $this->input['limit'];
            } else {
                $limit = $this->input['limit'];
            }

            return $listProvider(['search'=>$para, 'limit'=>$limit]);
        } catch (\Exception $e) {
            hFormatException($e, true);
            return [];
        }
    }
}
