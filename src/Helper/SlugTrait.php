<?php

namespace App\Helper;


use Cocur\Slugify\Slugify;

trait SlugTrait
{
    public function getSlug(string $field): ?string
    {
        $slugify = new Slugify();
        if(property_exists($this, $field)){
            $slug = $slugify->slugify($this->$field);
        }else{
            $method = $field;
            if(method_exists($this, $method)) {
                $slug = $slugify->slugify($this->$method());
            }else {
                $method = 'get' . strtoupper(substr($field, 0, 1)) . substr($field, 1);
                if (method_exists($this, $method)) {
                    $slug = $slugify->slugify($this->$method());
                }
            }
        }

        if(empty($slug)){
            $slug = "-";
        }

        return $slug;
    }
}