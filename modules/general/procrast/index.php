<?php

if (cfr('PROCRAST')) {

    /**
     * Returns game icon and link as standard panel
     * 
     * @return string
     */
    function zb_buildGameIcon($link, $icon, $text) {
        $icon_path = 'modules/jsc/procrastdata/icons/';

        $task_link = $link;
        $task_icon = $icon_path . $icon;
        $task_text = $text;

        $tbiconsize = '128';
        $template = wf_tag('div', false, 'dashtask', 'style="height:' . ($tbiconsize + 30) . 'px; width:' . ($tbiconsize + 30) . 'px;"');
        $template.= wf_tag('a', false, '', 'href="' . $task_link . '"');
        $template.= wf_tag('img', false, '', 'src="' . $task_icon . '" border="0" width="' . $tbiconsize . '"  height="' . $tbiconsize . '" alt="' . $task_text . '" title="' . $task_text . '"');
        $template.= wf_tag('a', true);
        $template.= wf_tag('br');
        $template.= wf_tag('br');
        $template.= $task_text;
        $template.= wf_tag('div', true);
        return ($template);
    }

    if (wf_CheckGet(array('run'))) {
        $application = vf($_GET['run']);
        switch ($application) {
            case 'tetris':
                $jsTetris = file_get_contents('modules/jsc/procrastdata/jstetris/tetris.html');
                $jsTetris = str_replace('START_LABEL', __('Press space to play'), $jsTetris);
                $jsTetris = str_replace('SCORE_LABEL', __('score'), $jsTetris);
                $jsTetris = str_replace('ROWS_LABEL', __('rows'), $jsTetris);
                show_window(__('Tetris'), $jsTetris);
                break;
            case '2048':
                $jsCode = file_get_contents('modules/jsc/procrastdata/2048/2048.html');
                show_window(__('2048'), $jsCode);
                break;
            case 'robotunicorn':
                $jsCode = file_get_contents('modules/jsc/procrastdata/robotunicorn.html');
                show_window(__('Robot Unicorn Attack'), $jsCode);
                break;
            case 'motox3m':
                $jsCode = file_get_contents('modules/jsc/procrastdata/motox3m.html');
                show_window(__('Moto X3M'), $jsCode, 'center');
                break;
            case 'happywheels':
                $jsCode = file_get_contents('modules/jsc/procrastdata/happywheels.html');
                show_window(__('Happy Wheels'), $jsCode, 'center');
                break;
        }
        show_window('', wf_BackLink('?module=procrast'));
    } else {
        $applicationsList = '';
        $applicationArr = array(
            'tetris' => __('Tetris'),
            '2048' => __('2048'),
            'robotunicorn' => __('Robot Unicorn Attack'),
            'motox3m' => __('Moto X3M'),
            'happywheels' => __('Happy Wheels')
        );


        if (!empty($applicationArr)) {
            foreach ($applicationArr as $io => $each) {
                $applicationsList.=zb_buildGameIcon('?module=procrast&run=' . $io, $io . '.png', $each);
            }
        }

        $applicationsList.=wf_CleanDiv();
        show_window(__('Procrastination helper'), $applicationsList);
    }
} else {
    show_error(__('Access denied'));
}
?>