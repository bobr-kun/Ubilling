<?php

if (cfr('USERPROFILE')) {
    if ($ubillingConfig->getAlterParam('ROS_NAS_PPPOE_SESSION_INFO_IN_PROFLE')) {
        if (ubRouting::checkPost('GetPPPoEInfo') and ubRouting::checkPost('usrlogin')) {
            $infoBlock = zb_GetROSPPPoESessionInfo(ubRouting::post('usrlogin'), wf_getBoolFromVar(ubRouting::post('returnAsHTML'), true), wf_getBoolFromVar(ubRouting::post('returnInSpoiler'), true));
            die($infoBlock);
        }
    }

    if (isset($_GET['username'])) {
        $login = vf($_GET['username']);
        $login = trim($login);
        try {
            $profile = new UserProfile($login);
            show_window(__('User profile'), $profile->render());

            if (wf_CheckGet(array('justregistered'))) {
                $newUserRegisteredNotification = '';
                @$awesomeness = rcms_scandir('skins/awesomeness/');
                if (!empty($awesomeness)) {
                    $awesomenessRnd = array_rand($awesomeness);
                    $awesomeness = $awesomeness[$awesomenessRnd];
                    $newUserRegisteredNotification.= wf_tag('center') . wf_img_sized('skins/awesomeness/' . $awesomeness, '', '256') . wf_tag('center', true);
                }
                $messages=new UbillingMessageHelper();
                $newUserRegisteredNotification.=$messages->getStyledMessage(__('Its incredible, but you now have a new user') . '!', 'success');
                $newUserRegisteredNotification.= wf_CleanDiv();
                $newUserRegisteredNotification.= wf_tag('br');
                $newUserRegisteredNotification.=web_UserControls($login);
                show_window('', wf_modalOpenedAuto(__('Success') . '!', $newUserRegisteredNotification));
            }
        } catch (Exception $exception) {
            show_window(__('Error'), __('Strange exeption') . ': ' . wf_tag('pre') . $exception->getMessage() . wf_tag('pre', true));
        }
    } else {
        throw new Exception('GET_NO_USERNAME');
    }
} else {
    show_error(__('Access denied'));
}
?>
