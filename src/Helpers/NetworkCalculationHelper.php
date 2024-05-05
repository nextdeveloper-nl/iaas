<?php

namespace NextDeveloper\IAAS\Helpers;

class NetworkCalculationHelper
{
    public static function mask2cidr($mask)
    {
        /**
         * Taken from: https://stackoverflow.com/questions/35075811/convert-ipv4-netmask-to-cidr-format
         * @thanks to: https://stackoverflow.com/users/1673312/hugo-ferreira
         */

        $long = ip2long($mask);
        $base = ip2long('255.255.255.255');
        return 32 - log(($long ^ $base) + 1, 2);

        /* xor-ing will give you the inverse mask,
    log base 2 of that +1 will return the number
    of bits that are off in the mask and subtracting
    from 32 gets you the cidr notation */
    }
}
