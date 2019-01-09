<?php

namespace CoreHelpers;

class ProgressBarController {
    public $max;
    public $bars;
    public $style;
    public $showValues;
    public $options;

    public function __construct($max, $style=[], $showValues=false, $options=[]) {
        $this->max = $max;
        $this->bars = [];
        $this->style = array_merge([
                'width' => '100px',
                'height' => '13px',
                'margin' => 0,
            ], $style);
        $this->showValues = $showValues;
        $this->options = $options;
    }

    public function addBar($value, $class="success", $show=false, $title="") {
        $this->bars[] = [
            'value' => $value,
            'percent' => empty($this->max) ? 0 : floor(100*$value/$this->max),
            'class' => $class,
            'show' => $show,
            'title' => $title
        ];
    }

    public function render($class="") {
        $styleString = "";
        foreach ($this->style as $key => $value)
            $styleString .= "$key:$value;";
        ob_start();
        ?>
        <div class="progress <?= $class ?>" style="<?= $styleString ?>">
            <?php foreach ($this->bars as $v) { ?>
                <div class="<?= $class ?> progress-bar progress-bar-<?= $v['class'] ?>" style="width:<?= $v['percent'] ?>%;line-height:inherit;font-size:10px;" title="<?= $v['title'].'<br>'.$v['value'].' ('.$v['percent'].'%)' ?>" <?= (empty($this->options['noTooltip'])?'data-toggle="tooltip"':'') ?>>
                    <?php if ($this->showValues || $v['show']) { ?>
                        <?= $v['value'].(!empty($this->options['isPercentage'])?'%':'') ?>
                    <?php }else{ ?>
                        <span class="sr-only"><?= $v['value'].(!empty($this->options['isPercentage'])?'%':'') ?></span>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
        <?php
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
}