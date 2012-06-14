<?php
if(defined('SH_DEBUG_VERIFY_FOLDER') && is_dir(SH_DEBUG_VERIFY_FOLDER)){
    define('SH_IS_DEV',true);
    define('SH_GLOBAL_DEBUG',true);
    ini_set('xdebug.var_display_max_depth','5');
    if(defined('SH_DEBUG_COVERAGE_PAGE')){
        xdebug_start_code_coverage();
        define('LINE_USED',1);
        define('LINE_UNUSED',0);
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
    define('SH_IS_DEV',false);
}

function endDebug(){
    if(defined('SH_DEBUG_VERIFY_FOLDER') && is_dir(SH_DEBUG_VERIFY_FOLDER)){
        if(defined('SH_DEBUG_COVERAGE_PAGE')){
            $linker = sh_linker::getInstance();
            if(file_exists(SH_DEBUG_COVERAGE_PAGE)){
                include(SH_DEBUG_COVERAGE_PAGE);
            }else{
                $coverage = array();
            }
            $all_elements = debug_get_coverage($coverage);
            $linker->helper->writeArrayInFile(SH_DEBUG_COVERAGE_PAGE,"coverage",$all_elements);
            xdebug_stop_code_coverage();
        }
    }
}

function debug_get_coverage($oldCoverage = array()){
    $array = xdebug_get_code_coverage();
    $ret = $oldCoverage;
    if(!is_array($array)){
        return $ret;
    }
    foreach($array as $file=>$views){
        if(substr(basename($file),0,-8) == SH_DEBUG_COVERAGE_CLASS){
            $fileContents = file($file);
            foreach($fileContents as $num=>$lineContent){
                $lineContent = trim($lineContent);

                if(isset($views[$num]) || $lineContent == '<?php'){
                    $previousLine = LINE_USED;
                    $ret[$file]['l_'.($num + 1)] += LINE_USED;// Used line
                }elseif($lineContent == '' || $lineContent == '}'){
                    if($previousLine){
                        $ret[$file]['l_'.($num + 1)] = $previousLine;
                    }
                }else{
                    $previousLine = LINE_UNUSED;
                }
            }
        }
    }
    return $ret;
}