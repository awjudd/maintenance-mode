<?php

return [
    /**
     * Title of the maintenance page
     *
     * @var string
     */
    'title' => 'حالت تعمیر و نگهداری',

    /**
     * Default application down message, shown on the maintenance page
     *
     * @var string
     */
    'message' => 'ما در حال کاربر روی سایت هستیم، خواهشمند است کمی دیگر مراجعه فرمایید.',

    /**
     * Last updated string, shown on the maintenance page
     *
     * @var string
     */
    'last-updated' => 'آخرین زمان بروز رسانی :timestamp می باشد.',

    /**
     * Exception messages
     *
     * @var array
     */
    'exceptions' => [
        'invalid' => 'کلاس :class بر روی کلاس \MisterPhilip\MaintenanceMode\Exemptions\MaintenanceModeExemption توسعه داده نشده است.',
        'missing' => 'کلاس :class وجود ندارد.',
    ]
];
