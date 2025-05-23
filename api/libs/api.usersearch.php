<?php

/**
 * Returns user profile fileds search form
 * 
 * @return string
 */
function web_UserSearchFieldsForm() {
    global $ubillingConfig;
    $altCfg = $ubillingConfig->getAlter();
    $fieldinputs = wf_TextInput('searchquery', 'Search by', '', true, '40');
    $fieldinputs .= wf_RadioInput('searchtype', 'All fields', 'full', true, true);
    $fieldinputs .= wf_RadioInput('searchtype', 'Real Name', 'realname', true);
    $fieldinputs .= wf_RadioInput('searchtype', 'Login', 'login', true);
    $fieldinputs .= wf_RadioInput('searchtype', 'Phone', 'phone', true);
    $fieldinputs .= wf_RadioInput('searchtype', 'Mobile', 'mobile', true);
    $fieldinputs .= wf_RadioInput('searchtype', 'Email', 'email', true);
    $fieldinputs .= wf_RadioInput('searchtype', 'Notes', 'note', true);
    $fieldinputs .= wf_RadioInput('searchtype', 'Contract', 'contract', true);
    if ($altCfg['OPENPAYZ_SUPPORT']) {
        $fieldinputs .= wf_RadioInput('searchtype', 'Payment ID', 'payid', true);
    }
    $fieldinputs .= wf_RadioInput('searchtype', 'IP', 'ip', true);
    $fieldinputs .= wf_RadioInput('searchtype', 'MAC', 'mac', true);
    if ($altCfg['SWITCHPORT_IN_PROFILE']) {
        $fieldinputs .= wf_RadioInput('searchtype', 'Switch binding (SwIP/SwID/SwLocation)', 'switchassign', true);
    }
    if ($altCfg['PON_ENABLED']) {
        $fieldinputs .= wf_RadioInput('searchtype', 'ONU MAC', 'onumac', true);
        $fieldinputs .= wf_RadioInput('searchtype', 'ONU Serial', 'onuserial', true);
    }
    if ($altCfg['SWITCHES_EXTENDED']) {
        $fieldinputs .= wf_RadioInput('searchtype', 'Switch ID', 'swid', true);
    }
    $fieldinputs .= wf_tag('br');
    $fieldinputs .= wf_Submit('Search');
    $form = wf_Form('', 'POST', $fieldinputs);

    return ($form);
}

/**
 * Returns user profile search results
 * 
 * @global object $ubillingConfig
 * @param string $query
 * @param string $searchtype
 * @return string
 */
function zb_UserSearchFields($query, $searchtype) {
    global $ubillingConfig;
    $query = mysql_real_escape_string(trim($query));
    $searchtype = vf($searchtype);
    $altercfg = $ubillingConfig->getAlter();
    $mobileExtFlag = $ubillingConfig->getAlterParam('MOBILES_EXT');

    //check strict mode for our searchtype
    $strictsearch = array();
    if (isset($altercfg['SEARCH_STRICT'])) {
        if (!empty($altercfg['SEARCH_STRICT'])) {
            $strictsearch = explode(',', $altercfg['SEARCH_STRICT']);
            $strictsearch = array_flip($strictsearch);
        }
    }


    //construct query
    if ($searchtype == 'realname') {
        $mask = (isset($strictsearch[$searchtype]) ? '' : '%');
        $query = "SELECT `login` from `realname` WHERE `realname` LIKE '" . $mask . $query . $mask . "'";
    }
    if ($searchtype == 'login') {
        $mask = (isset($strictsearch[$searchtype]) ? '' : '%');
        $query = "SELECT `login` from `users` WHERE `login` LIKE '" . $mask . $query . $mask . "'";
    }
    if ($searchtype == 'phone') {
        $mask = (isset($strictsearch[$searchtype]) ? '' : '%');
        $query = "SELECT `login` from `phones` WHERE `phone` LIKE '" . $mask . $query . $mask . "'";
    }
    if ($searchtype == 'mobile') {
        $mask = (isset($strictsearch[$searchtype]) ? '' : '%');
        if ($mobileExtFlag) {
            $query = "SELECT `login` FROM `phones` WHERE `mobile` LIKE '" . $mask . $query . $mask . "'
                        UNION
                      SELECT `login` FROM `mobileext` WHERE `mobile` LIKE '" . $mask . $query . $mask . "'";
        } else {
            $query = "SELECT `login` from `phones` WHERE `mobile` LIKE '" . $mask . $query . $mask . "'";
        }
    }
    if ($searchtype == 'email') {
        $mask = (isset($strictsearch[$searchtype]) ? '' : '%');
        $query = "SELECT `login` from `emails` WHERE `email` LIKE '" . $mask . $query . $mask . "'";
    }
    if ($searchtype == 'note') {
        $mask = (isset($strictsearch[$searchtype]) ? '' : '%');
        $query = "SELECT `login` from `notes` WHERE `note` LIKE '" . $mask . $query . $mask . "'";
    }
    if ($searchtype == 'contract') {
        $mask = (isset($strictsearch[$searchtype]) ? '' : '%');
        $query = "SELECT `login` from `contracts` WHERE `contract` LIKE '" . $mask . $query . $mask . "'";
    }
    if ($searchtype == 'ip') {
        $mask = (isset($strictsearch[$searchtype]) ? '' : '%');
        $query = "SELECT `login` from `users` WHERE `IP` LIKE '" . $mask . $query . $mask . "'";
    }
    if ($searchtype == 'seal') {
        $mask = (isset($strictsearch[$searchtype]) ? '' : '%');
        $query = "SELECT `login` from `condet` WHERE `seal` LIKE '" . $mask . $query . $mask . "'";
    }
    if ($searchtype == 'swid') {
        $mask = (isset($strictsearch[$searchtype]) ? '' : '%');
        $query = "SELECT `login` from `users` WHERE `ip` IN (SELECT `ip` FROM `nethosts` WHERE `option` LIKE '" . $mask . $query . $mask . "')";
    }
    if ($altercfg['SWITCHPORT_IN_PROFILE']) {
        if ($searchtype == 'switchassign') {
            $mask = (isset($strictsearch[$searchtype]) ? '' : '%');
            $whereType = 'location';
            // Change type for search on switch
            $extractedIpAddr = zb_ExtractIpAddress($query);
            if ($extractedIpAddr) {
                $query = $extractedIpAddr;
                $whereType = 'ip';
            }
            $macExtracted = zb_ExtractMacAddress($query);
            if (!empty($macExtracted)) {
                $query = $macExtracted;
                $whereType = 'swid';
            }
            $query = "
            SELECT `login` from `users`
            INNER JOIN `switchportassign` USING (`login`)
            INNER JOIN `switches` ON (`switchportassign`.`switchid`=`switches`.`id`)
            WHERE `switches`.`" . $whereType . "` LIKE '" . $mask . $query . $mask . "'";
        }
    }
    if ($altercfg['PON_ENABLED'] and $searchtype == 'onumac') {
        $mask = (isset($strictsearch[$searchtype]) ? '' : '%');
        $query = "SELECT `login` from `pononu` WHERE `mac` LIKE '" . $mask . $query . $mask . "'";
    }
    if ($altercfg['PON_ENABLED'] and $searchtype == 'onuserial') {
        $mask = (isset($strictsearch[$searchtype]) ? '' : '%');
        $query = "SELECT `login` from `pononu` WHERE `serial` LIKE '" . $mask . $query . $mask . "'";
    }
    //mac-address search
    if ($searchtype == 'mac') {
        $allfoundlogins = array();
        $allMacs = zb_UserGetAllMACs();
        $searchMacPart = strtolower($query);
        $searchMacPart = RemoveMacAddressSeparator($searchMacPart);
        $searchMacPart = AddMacSeparator($searchMacPart);

        if (!empty($allMacs)) {
            $allMacs = array_flip($allMacs);
            foreach ($allMacs as $eachMac => $macLogin) {
                if (ispos($eachMac, $searchMacPart)) {
                    $allfoundlogins[] = $macLogin;
                }
            }
        }
    }

    if ($searchtype == 'apt') {
        $query = "SELECT `login` from `address` WHERE `aptid` = '" . $query . "'";
    }
    if ($searchtype == 'payid') {
        if ($altercfg['OPENPAYZ_SUPPORT']) {
            if ($altercfg['OPENPAYZ_REALID']) {
                $query = "SELECT `realid` AS `login` from `op_customers` WHERE `virtualid`='" . $query . "'";
            } else {
                $query = "SELECT `login` from `users` WHERE `IP` = '" . int2ip($query) . "'";
            }
        }
    }

    // пытаемся изобразить результат
    if ($searchtype != 'mac') {
        $allresults = simple_queryall($query);
        $allfoundlogins = array();
        if (!empty($allresults)) {
            foreach ($allresults as $io => $eachresult) {
                $allfoundlogins[] = $eachresult['login'];
            }
            //если таки по четкому адресу искали - давайте уж в профиль со старта
            if ($searchtype == 'apt') {
                rcms_redirect("?module=userprofile&username=" . $eachresult['login']);
            }
        }
    }

    $result = web_UserArrayShower($allfoundlogins);
    return ($result);
}

/**
 * Returns user profile search results by all fields
 * 
 * @param string $query, $render
 * @return string
 */
function zb_UserSearchAllFields($query, $render = true) {
    global $ubillingConfig;
    $notesSearchFlag = $ubillingConfig->getAlterParam('SEARCH_NOTES');
    $mobileExtFlag = $ubillingConfig->getAlterParam('MOBILES_EXT');

    $allfoundlogins = array();
    if (strlen($query) >= 3) {
        $search_data_array = zb_UserGetAllDataCache();

        if ($notesSearchFlag) {
            $allUserNotes = zb_UserGetAllNotes();
            if (!empty($allUserNotes)) {
                foreach ($allUserNotes as $noteLogin => $noteText) {
                    if (isset($search_data_array[$noteLogin])) {
                        $search_data_array[$noteLogin]['note'] = $noteText;
                    }
                }
            }
        }

        if ($mobileExtFlag) {
            $mobilesExt = new MobilesExt();
            $rawMobiles = $mobilesExt->getAllUsersMobileNumbers();
            if (!empty($rawMobiles)) {
                foreach ($rawMobiles as $mobileLogin => $additionalMobiles) {
                    if (isset($search_data_array[$mobileLogin])) {
                        $search_data_array[$mobileLogin]['mobile'] .= ' ' . implode(' ', $additionalMobiles);
                    }
                }
            }
        }

        $search_part = trim($query);
        $search_part = preg_quote($search_part, '/');
        foreach ($search_data_array as $login => $data) {
            if (preg_grep('/' . $search_part . '/iu', $data)) {
                $allfoundlogins[] = $login;
            }
        }
        if ($render) {
            $result = web_UserArrayShower($allfoundlogins);
        } else {
            $result = $allfoundlogins;
        }
    } else {
        $messages = new UbillingMessageHelper();
        $result = $messages->getStyledMessage(__('At least 3 characters are required for search'), 'info');
    }
    return ($result);
}

/**
 * Returns custom fields search form
 * 
 * @return string
 */
function web_UserSearchCFForm() {
    $cf = new CustomFields();
    $allCfTypes = $cf->getTypesAll();
    $cfsearchform = wf_tag('h3') . __('Additional profile fields') . wf_tag('h3', true);
    if (!empty($allCfTypes)) {
        foreach ($allCfTypes as $io => $eachtype) {
            $searchControl = $cf->getTypeSearchControl($eachtype['type'], $eachtype['id']);
            //is this type searchable?
            if (!empty($searchControl)) {
                $cfsearchform .= $cf->renderTypeName($eachtype['id']) . ' ' . $searchControl;
            }
        }
    } else {
        $cfsearchform = '';
    }
    return ($cfsearchform);
}

/**
 * Returns custom field search results
 * 
 * @param int    $typeid existing custom field database id
 * @param string $query search term
 * @return array
 */
function zb_UserSearchCF($typeid, $query) {
    $typeid = vf($typeid);
    $query = mysql_real_escape_string($query);
    $result = array();
    $dataquery = "SELECT `login` from `cfitems` WHERE `typeid`='" . $typeid . "' AND `content`LIKE '%" . $query . "%'";
    $allusers = simple_queryall($dataquery);
    if (!empty($allusers)) {
        foreach ($allusers as $io => $eachuser) {
            $result[] = $eachuser['login'];
        }
    }
    return ($result);
}

/**
 * Returns custom contract search form
 * 
 * @global object $ubillingConfig
 * @return string
 */
function web_UserSearchContractForm() {
    $result = '';
    global $ubillingConfig;
    $altercfg = $ubillingConfig->getAlter();
    if (isset($altercfg['SEARCH_CUSTOM_CONTRACT'])) {
        if ($altercfg['SEARCH_CUSTOM_CONTRACT']) {
            $result .= wf_tag('h3') . __('Contract search') . wf_tag('h3', true);
            $inputs = wf_TextInput('searchquery', '', '', false);
            $inputs .= wf_HiddenInput('searchtype', 'contract');
            $inputs .= wf_Submit(__('Search'));
            $result .= wf_Form("", 'POST', $inputs, '');
            $result .= wf_delimiter();
        }
    }
    return ($result);
}

/**
 * Returns partial address search form
 * 
 * @global object $ubillingConfig
 * @return string
 */
function web_UserSearchAddressPartialForm() {
    global $ubillingConfig;
    $altercfg = $ubillingConfig->getAlter();
    if ($altercfg['SEARCHADDR_AUTOCOMPLETE']) {
        $allAddress = array();
        if (!@$altercfg['TASKMAN_SHORT_AUTOCOMPLETE']) {
            $allAddress = zb_AddressGetFulladdresslistCached();
        } else {
            if ($altercfg['TASKMAN_SHORT_AUTOCOMPLETE'] == 1) {
                $allAddress = zb_AddressGetStreetsWithBuilds();
            }

            if ($altercfg['TASKMAN_SHORT_AUTOCOMPLETE'] == 2) {
                $allAddress = zb_AddressGetStreets();
            }
        }
        natsort($allAddress);
        $inputs = wf_AutocompleteTextInput('partialaddr', $allAddress, '', '', false, 30);
    } else {
        $inputs = wf_TextInput('partialaddr', '', '', false, 30);
    }




    $inputs .= wf_Submit('Search');
    $result = wf_Form('', 'POST', $inputs, '', '');


    return ($result);
}

/**
 * Returns user full address search form
 * 
 * @return string
 */
function web_UserSearchAddressForm() {

    $form = wf_tag('form', false, '', 'action="" method="POST"');
    $form .= wf_tag('table', false, '', 'width="100%" border="0"');
    if (!isset($_POST['citysel'])) {
        $cells = wf_TableCell(__('City'), '40%');
        $cells .= wf_TableCell(web_CitySelectorAc());
        $form .= wf_TableRow($cells, 'row3');
    } else {
        // if city selected
        $cityname = zb_AddressGetCityData($_POST['citysel']);
        $cityname = $cityname['cityname'];

        $cells = wf_TableCell(__('City'), '40%');
        $cells .= wf_TableCell(web_ok_icon() . ' ' . $cityname . wf_HiddenInput('citysel', $_POST['citysel']));
        $form .= wf_TableRow($cells, 'row3');

        if (!isset($_POST['streetsel'])) {

            $cells = wf_TableCell(__('Street'), '40%');
            $cells .= wf_TableCell(web_StreetSelectorAc($_POST['citysel']));
            $form .= wf_TableRow($cells, 'row3');
        } else {
            // if street selected
            $streetname = zb_AddressGetStreetData($_POST['streetsel']);
            $streetname = $streetname['streetname'];

            $cells = wf_TableCell(__('Street'), '40%');
            $cells .= wf_TableCell(web_ok_icon() . ' ' . $streetname . wf_HiddenInput('streetsel', $_POST['streetsel']));
            $form .= wf_TableRow($cells, 'row3');

            if (!isset($_POST['buildsel'])) {

                $cells = wf_TableCell(__('Build'), '40%');
                $cells .= wf_TableCell(web_BuildSelectorAc($_POST['streetsel']));
                $form .= wf_TableRow($cells, 'row3');
            } else {
                //if build selected
                $buildnum = zb_AddressGetBuildData($_POST['buildsel']);
                $buildnum = $buildnum['buildnum'];

                $cells = wf_TableCell(__('Build'), '40%');
                $cells .= wf_TableCell(web_ok_icon() . ' ' . $buildnum . wf_HiddenInput('buildsel', $_POST['buildsel']));
                $form .= wf_TableRow($cells, 'row3');

                if (!isset($_POST['aptsel'])) {
                    $cells = wf_TableCell(__('Apartment'), '40%');
                    $cells .= wf_TableCell(web_AptSelectorAc($_POST['buildsel']));
                    $form .= wf_TableRow($cells, 'row3');
                } else {
                    //if apt selected
                    $aptnum = zb_AddressGetAptDataById($_POST['aptsel']);
                    $aptnum = $aptnum['apt'];

                    $cells = wf_TableCell(__('Apartment'), '40%');
                    $cells .= wf_TableCell(web_ok_icon() . ' ' . $aptnum . wf_HiddenInput('aptsel', $_POST['aptsel']));
                    $form .= wf_TableRow($cells, 'row3');

                    $cells = wf_TableCell(wf_HiddenInput('aptsearch', $_POST['aptsel']));
                    $cells .= wf_TableCell(wf_Submit(__('Find')));
                    $form .= wf_TableRow($cells, 'row3');
                }
            }
        }
    }

    $form .= wf_tag('table', true);
    $form .= wf_tag('form', true);

    return ($form);
}

/**
 * Returns corporate users search form
 * 
 * @global object $ubillingConfig
 * @return string
 */
function web_CorpsSearchForm() {
    global $ubillingConfig;
    $alterCfg = $ubillingConfig->getAlter();
    $result = '';
    if ($alterCfg['CORPS_ENABLED']) {
        $result .= wf_tag('h3') . __('Corporate users') . wf_tag('h3', true);
        if ($alterCfg['SEARCHADDR_AUTOCOMPLETE']) {
            $corps = new Corps();
            $corpsDataRaw = $corps->getCorps();
            $corpsNames = array();
            if (!empty($corpsDataRaw)) {
                foreach ($corpsDataRaw as $io => $each) {
                    $corpsNames[] = $each['corpname'];
                }
            }

            $inputs = wf_AutocompleteTextInput('searchcorpname', $corpsNames, '', '', false, '30');
        } else {
            $inputs = wf_TextInput('searchcorpname', '', '', false, '30');
        }
        $inputs .= wf_Submit(__('Search'));
        $result .= wf_Form('?module=corps&show=search', 'POST', $inputs, '');
    }
    return ($result);
}

/**
 * Performs login search by partial address or extended address
 * 
 * @global object $ubillingConfig
 * @param string $query
 * @param bool $searchExtenAddr
 *
 * @return array
 */
function zb_UserSearchAddressPartial($query, $searchExtenAddr = false) {
    global $ubillingConfig;
    $altercfg = $ubillingConfig->getAlter();
    $query = mysql_real_escape_string($query);

    if (!$altercfg['SEARCHADDR_AUTOCOMPLETE']) {
        $query = strtolower_utf8($query);
    }

    if ($searchExtenAddr) {
        $alluseraddress = zb_AddressExtenGetList();
    } else {
        $alluseraddress = zb_AddressGetFulladdresslist();
    }

    $result = array();

    if (!empty($alluseraddress)) {
        if (!$altercfg['SEARCHADDR_AUTOCOMPLETE']) {
            foreach ($alluseraddress as $login => $address) {
                if (ispos(strtolower_utf8($address), $query)) {
                    $result[] = $login;
                }
            }
        } else {
            foreach ($alluseraddress as $login => $address) {
                if (ispos($address, $query)) {
                    $result[] = $login;
                }
            }
        }
    }

    return ($result);
}

/**
 * Try to apply localte to searctype
 * 
 * @param string $searchtype
 * @return string
 */
function zb_UserSearchTypeLocalize($searchtype, $query = '') {
    $result = __('Search by') . ' ';

    switch ($searchtype) {
        case 'full':
            $result .= __('All fields');
            break;
        case 'realname':
            $result .= __('Real Name');
            break;
        case 'login':
            $result .= __('Login');
            break;
        case 'phone':
            $result .= __('Phone');
            break;
        case 'mobile':
            $result .= __('Mobile');
            break;
        case 'email':
            $result .= __('Email');
            break;
        case 'Note':
            $result .= __('Note');
            break;
        case 'contract':
            $result .= __('Contract');
            break;
        case 'payid':
            $result .= __('Payment ID');
            break;
        case 'ip':
            $result .= __('IP');
            break;
        case 'mac':
            $result .= __('MAC');
            break;
        case 'partialaddr':
            $result .= __('Partial address');
            break;
        case 'extenaddr':
            $result .= __('Extended address');
            break;
        case 'seal':
            $result .= __('Cable seal');
            break;
        case 'swid':
            $result .= __('Switch ID');
            break;
        default:
            $result .= '';
            break;
    }

    if (!empty($query)) {
        $result .= ' "' . $query . '"';
    }

    return ($result);
}

/**
 * Generates a user search elements with optional title and content.
 *
 * @param string $title The title of the user search grid.
 * @param string $content The content of the user search grid.
 * 
 * @return string
 */
function web_UserSearchElement($title = '', $content = '') {
    $result = '';
    if (!empty($content)) {
        $style = 'style="flex: 500px; padding: 5px; margin: 5px; border: 1px solid #d4d4d4; background-color: #fafafa;"';
        $result .= wf_tag('div', false, '', $style);
        if (!empty($title)) {
            $result .= wf_tag('h3', false, 'row3') . $title . wf_tag('h3', true);
        }
        $result .= $content;
        $result .= wf_tag('div', true);
    }
    return ($result);
}

/**
 * Generates the User Search Grid.
 *
 * This function generates the HTML markup for the User Search Grid,
 *  which includes various search forms for different search criteria.
 *
 * @return string
 */
function web_UserSearchGrid() {
    $result = '';
    $result = wf_tag('div', false, '', 'style="display: flex; flex-direction: row; flex-wrap: wrap;"');
    $result .= web_UserSearchElement(__('Full address'), web_UserSearchAddressForm());
    $result .= web_UserSearchElement(__('Partial address'), web_UserSearchAddressPartialForm());
    $result .= web_UserSearchElement(__('Profile fields search'), web_UserSearchFieldsForm());
    $result .= web_UserSearchElement(__('Other'), web_CorpsSearchForm() . web_UserSearchContractForm() . web_UserSearchCFForm());
    $result .= wf_tag('div', true);
    $result .= wf_CleanDiv();
    return ($result);
}
