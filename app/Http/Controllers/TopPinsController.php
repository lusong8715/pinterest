<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class TopPinsController extends Controller
{
    public function index() {
        $date = date('Y-m-d', strtotime('-15 day'));
        $result = DB::select("select p.saves, p.comments, p.image_url, if((char_length(p.original_link)>10), p.original_link, p.url) as link, sum(pdh.saves_change) as saves_change 
                    from pins as p inner join pin_data_history as pdh 
                    on p.id = pdh.pins_id 
                    where pdh.update_date > ?
                    group by p.id 
                    order by saves_change desc, p.id desc
                    limit 100", [$date]);
        echo json_encode($result);
        return;
    }
}
