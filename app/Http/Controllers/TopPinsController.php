<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class TopPinsController extends Controller
{
    public function index() {
        $result = DB::select("select p.saves, p.comments, p.image_url, if((char_length(p.original_link)>10), p.original_link, p.url) as link, sum(pdh.saves_change) as saves_change 
                    from pins as p inner join pin_data_history as pdh 
                    on p.id = pdh.pins_id 
                    group by p.id 
                    order by saves_change desc, p.id desc
                    limit 100");
        echo json_encode($result);
        return;
    }
}
