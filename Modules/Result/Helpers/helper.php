<?php 

// Get File Path From HELPER

if (!function_exists('getResulteData')) {
    function getFileName($data)
    {
        if ($data) {
            $name = explode('/', $data);
            return $name[4] ?? $name[0];
        } else {
            return '';
        }
    }
}
?>
