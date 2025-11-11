<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    /**
     * Get or set a setting value.
     *
     * @param  mixed  $default
     * @return mixed
     */
    function setting(?string $key = null, $default = null)
    {
        if (is_null($key)) {
            return new Setting;
        }

        return Setting::get($key, $default);
    }
}

if (! function_exists('set_setting')) {
    /**
     * Set a setting value.
     *
     * @param  mixed  $value
     * @return \App\Models\Setting
     */
    function set_setting(string $key, $value, string $group = 'general', string $type = 'string')
    {
        return Setting::set($key, $value, $group, $type);
    }
}
