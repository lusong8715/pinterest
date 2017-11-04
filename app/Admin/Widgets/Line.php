<?php

namespace App\Admin\Widgets;
use Encore\Admin\Widgets\Chart\Chart;

class Line extends Chart
{
    protected $_labels;
    protected $_lineData;

    public function __construct($labels, $data)
    {
        $this->_labels = $labels;
        $this->_lineData = $data;
        parent::__construct([]);
    }

    public function script()
    {
        $options = json_encode($this->options);

        return <<<EOF

(function(){
    var data = {
        labels: $this->_labels,
        datasets: [
            {
                fillColor: "rgba(0,255,0,0.2)",
                data: $this->_lineData
            }
        ]
    };

    var canvas = $("#{$this->elementId}").get(0).getContext("2d");
    var chart = new Chart(canvas).Line(data, $options);
})();
EOF;
    }
}
