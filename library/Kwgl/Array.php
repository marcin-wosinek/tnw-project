<?php

/**
 * Description of Array
 *
 * @author user
 */
class Kwgl_Array {
   
    public static function cmp($a,$b){
        
        if ($a["numconnections"] == $b["numconnections"]) {
            return 0;
        }
    
        return ($a["numconnections"] > $b["numconnections"]) ? -1 : 1;
        
    }
    
}
?>
