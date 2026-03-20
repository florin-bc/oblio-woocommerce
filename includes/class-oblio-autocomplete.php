<?php

if (!defined('ABSPATH')) {
    exit;
}

class Oblio_Autocomplete {

    /**
     * Mapare field => key wp_option
     * ex: 'issuerName' => 'oblio_autocomplete_issuerName'
     *
     * @var array<string,string>
     */
    public static $fields = array(
        'issuerName'         => 'oblio_autocomplete_issuerName',
        'issuerId'           => 'oblio_autocomplete_issuerId',
        'deputyName'         => 'oblio_autocomplete_deputyName',
        'deputyIdentityCard' => 'oblio_autocomplete_deputyIdentityCard',
        'deputyAuto'         => 'oblio_autocomplete_deputyAuto',
    );

    /**
     * Returnează lista de valori pentru un câmp.
     *
     * @param string $field
     * @return array
     */
    public static function get_values($field) {
        if (!isset(self::$fields[$field])) {
            return array();
        }
        $option = self::$fields[$field];
        $values = get_option($option, array());
        if (!is_array($values)) {
            $values = array();
        }
        // Normalize: string keys, unice, ordonate simplu
        $clean = array();
        foreach ($values as $v) {
            $v = trim((string) $v);
            if ($v === '') {
                continue;
            }
            $clean[$v] = $v;
        }
        return array_values($clean);
    }

    /**
     * Adaugă o valoare nouă pentru un câmp (unic, curățat).
     *
     * @param string $field
     * @param string $value
     * @return void
     */
    public static function save_value($field, $value) {
        if (!isset(self::$fields[$field])) {
            return;
        }
        $value = trim((string) $value);
        if ($value === '') {
            return;
        }

        $option = self::$fields[$field];
        $values = get_option($option, array());
        if (!is_array($values)) {
            $values = array();
        }

        // Nu duplicăm valori (case-sensitive simplu)
        if (in_array($value, $values, true)) {
            return;
        }

        $values[] = $value;
        update_option($option, $values);
    }

    /**
     * Procesează toate câmpurile dintr-un submit de form.
     *
     * @param array $form_data tipic $_POST sau $_REQUEST
     * @return void
     */
    public static function save_values_from_form(array $form_data) {
        foreach (self::$fields as $field => $option) {
            if (!isset($form_data[$field])) {
                continue;
            }
            $raw = $form_data[$field];

            if (is_array($raw)) {
                foreach ($raw as $value) {
                    self::save_value($field, sanitize_text_field(wp_unslash($value)));
                }
            } else {
                self::save_value($field, sanitize_text_field(wp_unslash($raw)));
            }
        }
    }

    /**
     * Returnează toate câmpurile cu valorile lor.
     *
     * @return array<string,array>
     */
    public static function get_all_values() {
        $result = array();
        foreach (self::$fields as $field => $option) {
            $result[$field] = self::get_values($field);
        }
        return $result;
    }
}