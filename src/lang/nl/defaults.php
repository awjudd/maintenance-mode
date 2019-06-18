<?php

return [
    /**
     * Title of the maintenance page
     *
     * @var string
     */
    'title' => 'Onderhoudsmodus',

    /**
     * Default application down message, shown on the maintenance page
     *
     * @var string
     */
    'message' => 'Op dit moment wordt aan de website gewerkt, probeer het a.u.b. straks nog eens',

    /**
     * Last updated string, shown on the maintenance page
     *
     * @var string
     */
    'last-updated' => 'Laatste update was :timestamp',

    /**
     * Exception messages
     *
     * @var array
     */
    'exceptions' => [
        'invalid' => 'Klasse :class extend \MisterPhilip\MaintenanceMode\Exemptions\MaintenanceModeExemption niet',
        'missing' => 'Klasse :class bestaat niet',
    ]
];
