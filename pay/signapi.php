<?php
/**
 * WatchPay sign helper
 * Doc वाले format के हिसाब से: md5( signSource . "&key=" . merchant_key )
 */
class signapi
{
    /**
     * sign बनाना
     */
    public function sign(string $signSource, string $key): string
    {
        if (!empty($key)) {
            $signSource .= "&key=" . $key;
        }
        // WatchPay docs में md5 ही है (lowercase)
        return md5($signSource);
    }

    /**
     * callback पर sign verify करना
     */
    public function validate(string $signSource, string $key, string $recvSign): bool
    {
        if (!empty($key)) {
            $signSource .= "&key=" . $key;
        }
        $signCalc = md5($signSource);
        return hash_equals($signCalc, $recvSign);
    }
}