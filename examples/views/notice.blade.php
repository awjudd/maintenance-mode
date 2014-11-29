{{--
    This is a simple example for creating a warning to users who are exempt from the maintenance page,
    telling them that a maintenance period is going on. You should place this within your main template(s).

    NOTE: This example uses the default configuration. However, "inject.global" must be set to true.
          Additionally, if you've changed "inject.prefix", the variable names below will need to be changed
          to reflect your configuration values.
--}}

@if($MaintenanceModeEnabled)

    {{-- NOTE: This CSS should be moved to an assets file, but since this is an example... --}}
    <style>
        .maintenance-mode-alert {
            width: 100%;
            padding: .5em;
            background-color: #FF130F;
            color: #fff;
        }
        .maintenance-mode-alert time {
            opacity: 0.7;
            font-size: .8em;
            padding-top: .1em;
            float: right;
        }
    </style>

    <div class="maintenance-mode-alert" role="alert">

        <strong>Maintenance Mode</strong>

        {{-- Show the truncated message (so it doesn't overflow) --}}
        {{ str_limit($MaintenanceModeMessage, 100, "&hellip;") }}

        {{-- And show a human-friendly timestamp --}}
        <time datetime="{{ $MaintenanceModeTimestamp }}" title="{{ $MaintenanceModeTimestamp }}">{{ $MaintenanceModeTimestamp->diffForHumans() }}</time>
    </div>

@endif