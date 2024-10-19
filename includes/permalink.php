<?php

function getLinkService($id, $slug) {
    return 'course/'.$slug.'.html';
}

function getPrefixService($module='') {
    
    if ($module == 'pages') {
        return 'pages';
    }
    if ($module == 'portfolios') {
        return 'du-an';
    }
    
    if ($module == 'blog') {
        return 'blog';
    }

    if ($module == 'course') {
        return 'course';
    }
    return false;
}