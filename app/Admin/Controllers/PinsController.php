<?php

namespace App\Admin\Controllers;

use App\Admin\Widgets\Line;
use App\Models\Boards;
use App\Models\Config;
use App\Models\PinDataHistory;
use App\Models\Pins;
use Encore\Admin\Grid;
use Encore\Admin\Form;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Support\Facades\DB;

class PinsController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('Pins');
            $content->description('list');

            $content->body($this->grid());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('Pins');
            $content->description('edit');

            $content->body($this->form()->edit($id));
        });
    }

    public function destroy($id)
    {
        $apiBaseUrl = 'https://api.pinterest.com/v1/pins/';
        $config = Config::find(1);
        if (!$config->username || !$config->access_token) {
            return;
        }
        $pinsIds = explode(',', $id);
        foreach ($pinsIds as $pinsId) {
            $pins = Pins::find($pinsId);
            $url = $apiBaseUrl . $pins->pin_id . '/?access_token=' . $config->access_token;
            $result = curlRequest('delete', $url);
            if (isset($result['status']) && $result['status'] == 'failure') {
                continue;
            }
            $pins->delete();
        }
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Pins::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->pin_id()->display(function ($pinId) {
                return '<a href="'.$this->url.'" target="_blank">' . $pinId . '</a>';
            });
            $grid->product_id();
            $grid->product_sku();
            $grid->title()->limit(30);
            $grid->board();
            $grid->saves()->sortable()->display(function ($saves) {
                return '<a href="/admin/chart/'.$this->id.'/saves" target="_blank">' . $saves . '</a>';
            });
            $grid->comments()->sortable()->display(function ($comments) {
                return '<a href="/admin/chart/'.$this->id.'/comments" target="_blank">' . $comments . '</a>';
            });
            $grid->way()->display(function ($way) {
                if ($way == '0') {
                    return '产品';
                } else if ($way == '1') {
                    return '自定义';
                } else {
                    return '非本平台';
                }
            });
            $grid->created_at()->sortable();

            $grid->filter(function ($filter) {
                $filter->useModal();
                // 禁用id查询框
                $filter->disableIdFilter();
                $filter->is('pin_id', 'Pin Id');
                $filter->is('product_id', 'Product Id');
                $filter->is('product_sku', 'Product Sku');
                $filter->like('title', 'Title');
                $filter->is('board', 'Pin Board')->select(Boards::all()->pluck('name', 'name'));
                $filter->between('saves', 'Pin Saves');
                $filter->between('comments', 'Pin Comments');
                $filter->is('way', 'Release Way')->select([0 => '产品', 1 => '自定义', 2 => '非本平台']);
                $filter->between('created_at', 'Pin Created Time')->datetime();
            });

            $grid->disableCreation();
            $grid->disableActions();
            $grid->perPages([20, 40, 60, 80, 100]);
        });
    }

    protected function form()
    {
        return Admin::form(Pins::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->display('created_at', 'Created At');
        });
    }

    public function chart($id, $type)
    {
        return Admin::content(function (Content $content) use ($id, $type) {

            $content->header(ucfirst($type) . ' Line');
            $content->description('Pin Id: ' . $id);

            $datas = PinDataHistory::where('pins_id', '=', $id)->orderBy('update_date', 'asc')->get();
            $chartDatas = array();
            $i = 30;
            while ($i > 0) {
                $chartDatas[date('m/d', strtotime('-'.$i.' day'))] = 0;
                $i--;
            }
            $chartDatas[date('m/d')] = 0;
            foreach ($datas as $data) {
                $key = date('m/d', strtotime($data->update_date));
                if (isset($chartDatas[$key])) {
                    $chartDatas[$key] += $data->{$type.'_change'};
                }
            }
            $labels = array_keys($chartDatas);
            $lineData = array_values($chartDatas);
            $token = csrf_token();
            $script = <<<SCRIPT
<form action="/admin/download/$id/$type" method="post" accept-charset="UTF-8">
    <input type="hidden" name="_token" value="$token"/>
    <a class="btn btn-sm btn-primary" id="download_data" style="margin-bottom: 20px" href="javascript:void(0)"> 下载</a>
</form>
<script type="text/javascript">
    $('#download_data').click(function() {
        $(this).parent().submit();
    });
</script>
SCRIPT;

            $content->row($script);
            $labels = json_encode($labels);
            $lineData = json_encode($lineData);
            $content->row(new Line($labels, $lineData));
        });
    }

    public function download($id, $type) {
        header("Content-type:text/csv;");
        header('Content-Disposition: attachment;filename="' .$type . '_' . $id . '.csv"');
        header('Cache-Control: max-age=0');
        $fp = fopen('php://output', 'a');
        $head = array('Pins Id', ucfirst($type), ucfirst($type). ' Change', 'Update Date');
        fputcsv($fp, $head);
        $datas = PinDataHistory::where('pins_id', '=', $id)->orderBy('update_date', 'asc')->get();
        foreach ($datas as $data) {
            fputcsv($fp, array($id, $data->$type, $data->{$type.'_change'}, substr($data->update_date, 0, 10)));
        }
        flush();
        fclose($fp);
        return;
    }

    public function savesTop() {
        // saves 30天内增长数量 top100
        return Admin::content(function (Content $content) {
            $content->header('Top100');
            $content->description('saves');

            $body = Admin::grid(Pins::class, function (Grid $grid) {
                $grid->model()->select(DB::raw('pins.*, sum(pin_data_history.saves_change) as allsaves'));
                $grid->model()->join('pin_data_history', 'pins.id', '=', 'pin_data_history.pins_id');
                $grid->model()->groupBy('pins.id');
                $grid->model()->orderBy('allsaves', 'desc');
                $grid->model()->orderBy('pins.id', 'desc');
                $grid->model()->take(100);

                $grid->id('ID');
                $grid->pin_id()->display(function ($pinId) {
                    return '<a href="'.$this->url.'" target="_blank">' . $pinId . '</a>';
                });
                $grid->title();
                $grid->board();
                $grid->way()->display(function ($way) {
                    if ($way == '0') {
                        return '产品';
                    } else if ($way == '1') {
                        return '自定义';
                    } else {
                        return '非本平台';
                    }
                });
                $grid->allsaves('saves')->display(function ($saves) {
                    return '<a href="/admin/chart/'.$this->id.'/saves" target="_blank">' . $saves . '</a>';
                });
                $grid->created_at();

                $grid->filter(function ($filter) {
                    $filter->disableIdFilter();
                });

                $grid->disableActions();
                $grid->disableCreation();
                $grid->disablePagination();
                $grid->disableFilter();
                $grid->disableExport();
                $grid->disableRowSelector();

                $grid->tools(function ($tools) {
                    $tools->disableRefreshButton();
                });
            });
            $content->body($body);
        });
    }
}
