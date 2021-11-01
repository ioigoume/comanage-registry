<?php

App::uses('Helper', 'View');
class AppHelper extends Helper { 
       
    /**
     * truncateString
     *
     * @param  string $string
     * @param  integer $limit
     * @return string
     */
    function truncateString($string, $limit = 100) {
        $stringCut = substr($string, 0, $limit);
        $endPoint = strrpos($stringCut, ' ');

        //if the string doesn't contain any space then it will cut without word basis.
        $string = $endPoint ? substr($stringCut, 0, $endPoint) : substr($stringCut, 0);
        return $string;
    }
}
