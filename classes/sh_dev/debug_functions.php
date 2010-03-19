<?php
if(defined('SH_DEBUG_VERIFY_FOLDER') && is_dir(SH_DEBUG_VERIFY_FOLDER)){
    define('SH_GLOBAL_DEBUG',true);
    ini_set('xdebug.var_display_max_depth','5');
    if(defined('SH_DEBUG_COVERAGE_PAGE')){
        xdebug_start_code_coverage();
        define('LINE_USED',1);
        define('LINE_EMPTY',2);
        define('LINE_LIKE_PREVIOUS_LINE',4);
    }
    if(defined('SH_DEBUG_ERROR_REPORTING')){
        error_reporting(SH_DEBUG_ERROR_REPORTING);
    }
    $_SESSION["temp"] = array();
    define("SH_GLOBAL_DEBUG",true);
    if(defined('SH_DEBUG_TRACE_PATH')){
        ini_set("xdebug.collect_params", "3");
        xdebug_start_trace(SH_DEBUG_TRACE_PATH);
    }
}else{
    define('SH_GLOBAL_DEBUG',false);
}

function endDebug(){
    if(defined('SH_DEBUG_VERIFY_FOLDER') && is_dir(SH_DEBUG_VERIFY_FOLDER)){
        if(defined('SH_DEBUG_COVERAGE_PAGE')){
            echo 'Ending coverage...<br />';
            $linker = sh_linker::getInstance();
            if(file_exists(SH_DEBUG_COVERAGE_PAGE)){
                include(SH_DEBUG_COVERAGE_PAGE);
            }else{
                $coverage = array();
            }
            $newCoverage = debug_get_coverage();
            $all_keys = array_merge(array_keys($newCoverage),array_keys($coverage));
            //$coverage = array_merge_recursive($newCoverage,$coverage);
            foreach($all_keys as $file){
                if(!is_array($newCoverage[$file])){
                    $newCoverage[$file] = array();
                }elseif(!is_array($coverage[$file])){
                    $coverage[$file] = array();
                }
                $all_lines = array_merge(array_keys($newCoverage),array_keys($coverage));
                foreach($all_lines as $oneLine){
                    if(isset($newCoverage[$file][$oneLine])){
                       $all_elements[$file][$oneLine] = $newCoverage[$file][$oneLine];
                    }else{
                       $all_elements[$file][$oneLine] = $coverage[$file][$oneLine];
                    }
                }
                //$all_elements[$file] = array_merge($newCoverage[$file],$coverage[$file]);
            }
            echo 'Writing to '.SH_DEBUG_COVERAGE_PAGE.'<br />';
            $linker->helper->writeArrayInFile(SH_DEBUG_COVERAGE_PAGE,"coverage",$all_elements);
            xdebug_stop_code_coverage();
        }
    }
}

function debug_get_coverage(){
    echo 'Getting coverage...<br />';
    $array = xdebug_get_code_coverage();
    if(!is_array($array)){
        return array();
    }
    $ret = array();
    foreach($array as $file=>$views){
        $fileContents = file($file);

        foreach($fileContents as $num=>$lineContent){
            $lineContent = trim($lineContent);

            if(isset($file[$num]) || $lineContent == '<?php'){
                $ret[$file]['l_'.($num + 1)] = LINE_USED;// Used line
            }elseif($lineContent == ''){
                $ret[$file]['l_'.($num + 1)] = LINE_EMPTY;// Empty line
            }elseif($lineContent == '}'){
                $ret[$file]['l_'.($num + 1)] = LINE_LIKE_PREVIOUS_LINE;// as Previous line
            }
        }
    }
    return $ret;
}