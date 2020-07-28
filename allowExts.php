<?php
    $allowExts = array('gif','jpg','jpeg','png','pdf','txt','doc','docx','xls','xlsx','zip','lzh');
    $maxMB = 20;
    $maxSize = 1000 * 1000 * $maxMB;
    
    $keyPath = './dl_key/';
    $filePath = './files/';

    function getExt($file){
        return pathinfo($file,PATHINFO_EXTENSION);
    }
?>