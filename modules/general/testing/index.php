<?php

//just dummy module for testing purposes
error_reporting(E_ALL);

if (cfr('ROOT')) {

    $photostorage = new PhotoStorage('GALLERY', 'nope');

  
    deb($photostorage->renderScopesGallery());
    
}