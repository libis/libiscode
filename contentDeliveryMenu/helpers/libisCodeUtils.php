<?php
/**
 * Created by PhpStorm.
 * User: NaeemM
 * Date: 19/05/14
 * Time: 13:34
 */

class libiscodeUtils {

    function removeDirectory($dir) {
        $dir_content = scandir($dir);
        if($dir_content !== FALSE){
            foreach ($dir_content as $entry)
            {
                if(!in_array($entry, array('.','..'))){
                    $entry = $dir . '/' . $entry;
                    if(!is_dir($entry)){
                        unlink($entry);
                    }
                    else{
                        rmdir_recursive($entry);
                    }
                }
            }
        }
        return rmdir($dir);
    }
} 