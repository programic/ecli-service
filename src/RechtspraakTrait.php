<?php

namespace Programic\EcliService;

use Programic\EcliService\Locations\Location;

/**
 * Trait RechtspraakTrait
 * @package App\Models\Traits
 */
trait RechtspraakTrait
{
    /**
     * @return bool
     */
    public function sendToRechtspraak()
    {
        $data = $this->getRechtspraakLocationFields();
        if (is_array($data)) {
            return Location::sync($data);
        }

        return false;
    }
}
