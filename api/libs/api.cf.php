<?php

/**
 * Sets some CF content to user with override of old value
 * 
 * @param int     $typeid  Existing CF type ID
 * @param string  $login   Existing Ubilling user login
 * @param string  $content Content that will be set for user into CF
 * 
 * @return void
 */
function cf_FieldSet($typeid, $login, $content) {
    $typeid = vf($typeid);
    $login = mysql_real_escape_string($login);
    $content = mysql_real_escape_string($content);
    cf_FieldDelete($login, $typeid);
    $query = "INSERT INTO `cfitems` (`id` ,`typeid` ,`login` ,`content`) VALUES (NULL , '" . $typeid . "', '" . $login . "', '" . $content . "');";
    nr_query($query);
    if (strlen($content) < 20) {
        $logcontent = $content;
    } else {
        $logcontent = substr($content, 0, 20) . '..';
    }

    log_register("CF SET (" . $login . ") TYPE [" . $typeid . "]" . " ON `" . $logcontent . "`");
}

/**
 * Gets CF content assigned for user in database
 * 
 * @param string  $login   Existing Ubilling user login
 * @param int     $typeid  Existing CF type ID
 * 
 * @return string
 */
function cf_FieldGet($login, $typeid) {
    $typeid = vf($typeid);
    $login = mysql_real_escape_string($login);
    $result = '';
    $query = "SELECT `content` from `cfitems` WHERE `login`='" . $login . "' AND `typeid`='" . $typeid . "'";
    $content = simple_query($query);
    if (!empty($content)) {
        $result = $content['content'];
    }
    return ($result);
}

/**
 * Gets all available CF fields content assigned with users from database
 * 
 * @return array
 */
function cf_FieldsGetAll() {
    $result = array();
    $query = "SELECT * from `cfitems`";
    $content = simple_queryall($query);
    if (!empty($content)) {
        $result = $content;
    }
    return ($result);
}

/**
 * Deletes all of CF intems in database associated with some login
 * 
 * @param string $login Existing user login
 * 
 * @return void
 */
function cf_FlushAllUserCF($login) {
    $login = mysql_real_escape_string($login);
    $query = "DELETE from `cfitems` WHERE `login`='" . $login . "'";
    nr_query($query);
    log_register("CF FLUSH (" . $login . ")");
}
