<?php
/**
 * Sortable.php.
 * User: feng
 * Date: 2018/4/23
 */

namespace BaiSam\Contracts;


interface Sortable
{

    /**
     * @param float|null $weight
     *
     * @return $this
     */
    public function weight($weight = null);

}