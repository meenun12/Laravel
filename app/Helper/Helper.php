<?php

namespace App\Helper;

class Helper
{
    public function replace_image_key($images) {

        if(!$images){
            return;
        }

        $new_image_keys = array("100x100" => "image_100", "74x74" => "image_74", "32x32" => "image_32");

        foreach ($images as $key => $val) {
            $images[$new_image_keys[$key]] = $val;
            unset($images[$key]);
        }

        return $images;

    }

    public function replace_street_number_type($array) {

        if (!isset($array)) {
            return;
        }

        foreach ($array as $key => $val) {

            if ($val['street_number']) {
                $val['street_number'] = (int) $val;
            }

        }

        return $array;

    }


}

