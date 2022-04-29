<?php

if (ubRouting::get('action') == 'rorodog') {
    if ($ubillingConfig->getAlterParam('RORODOG_ENABLED')) {
        $rorodog = new RoRoDog();
        $rorodog->processPayments();
    }
}