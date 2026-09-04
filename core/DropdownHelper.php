<?php
/**
 * Portal BIP - Dropdown & Form Helper
 * Standardized Searchable Select Component Generator
 */

class DropdownHelper
{
    /**
     * Render a standardized searchable <select> dropdown
     *
     * @param string $name Form field name (e.g. 'pegawai_id', 'kelas')
     * @param array $options List of options. Supported formats:
     *   1. Array of strings/scalars: ['1A', '2B', '3C']
     *   2. Array of assoc arrays:
     *      [
     *         ['value' => '1', 'label' => 'John Doe', 'badge' => 'NIY. 123', 'subtext' => 'Gelar: M.Pd', 'image' => '/url.jpg', 'unit' => 'SD'],
     *         ...
     *      ]
     * @param mixed $selected Selected value
     * @param array $config Configuration attributes:
     *   - placeholder: string
     *   - search_placeholder: string
     *   - id: string
     *   - required: bool
     *   - allow_clear: bool
     *   - class: string (extra CSS classes)
     *   - onchange: string (JS callback name or inline call)
     *   - data_attrs: array (custom data-* attributes)
     * @return string HTML markup
     */
    public static function searchableSelect(string $name, array $options, $selected = null, array $config = []): string
    {
        $id = $config['id'] ?? ('select_' . str_replace(['[', ']', '.'], '_', $name) . '_' . substr(md5(uniqid()), 0, 6));
        $placeholder = $config['placeholder'] ?? '-- Pilih Opsi --';
        $searchPlaceholder = $config['search_placeholder'] ?? 'Ketik untuk mencari...';
        $required = !empty($config['required']) ? 'required' : '';
        $allowClear = !empty($config['allow_clear']) ? 'data-allow-clear="true"' : '';
        $extraClass = $config['class'] ?? '';
        $onChange = !empty($config['onchange']) ? 'onchange="' . htmlspecialchars($config['onchange'], ENT_QUOTES) . '"' : '';

        $customData = '';
        if (!empty($config['data_attrs']) && is_array($config['data_attrs'])) {
            foreach ($config['data_attrs'] as $k => $v) {
                $customData .= ' data-' . htmlspecialchars($k, ENT_QUOTES) . '="' . htmlspecialchars((string)$v, ENT_QUOTES) . '"';
            }
        }

        $html = '<select name="' . htmlspecialchars($name, ENT_QUOTES) . '" ';
        $html .= 'id="' . htmlspecialchars($id, ENT_QUOTES) . '" ';
        $html .= 'class="searchable-select w-full ' . htmlspecialchars($extraClass, ENT_QUOTES) . '" ';
        $html .= 'data-placeholder="' . htmlspecialchars($placeholder, ENT_QUOTES) . '" ';
        $html .= 'data-search-placeholder="' . htmlspecialchars($searchPlaceholder, ENT_QUOTES) . '" ';
        $html .= $allowClear . ' ' . $required . ' ' . $onChange . $customData . '>';

        // Default empty placeholder option
        if (!empty($placeholder)) {
            $html .= '<option value="">' . htmlspecialchars($placeholder, ENT_QUOTES) . '</option>';
        }

        foreach ($options as $key => $opt) {
            if (is_array($opt)) {
                $val = $opt['value'] ?? ($opt['id'] ?? $key);
                $label = $opt['label'] ?? ($opt['nama'] ?? ($opt['nama_kelas'] ?? ($opt['nama_mapel'] ?? $val)));
                $badge = $opt['badge'] ?? ($opt['niy'] ?? ($opt['kode_mapel'] ?? ''));
                $subtext = $opt['subtext'] ?? ($opt['gelar'] ? 'Gelar: ' . $opt['gelar'] : ($opt['kelompok'] ?? ''));
                $image = !empty($opt['image']) ? $opt['image'] : (!empty($opt['foto']) ? url(ltrim($opt['foto'], '/')) : '');
                $unit = $opt['unit'] ?? ($opt['jenjang'] ?? '');
            } else {
                $val = is_numeric($key) ? $opt : $key;
                $label = $opt;
                $badge = '';
                $subtext = '';
                $image = '';
                $unit = '';
            }

            $isSelected = ((string)$selected === (string)$val) ? 'selected' : '';

            $attrString = '';
            if (!empty($badge)) $attrString .= ' data-badge="' . htmlspecialchars($badge, ENT_QUOTES) . '"';
            if (!empty($subtext)) $attrString .= ' data-subtext="' . htmlspecialchars($subtext, ENT_QUOTES) . '"';
            if (!empty($image)) $attrString .= ' data-image="' . htmlspecialchars($image, ENT_QUOTES) . '"';
            if (!empty($unit)) $attrString .= ' data-unit="' . htmlspecialchars($unit, ENT_QUOTES) . '"';

            $html .= '<option value="' . htmlspecialchars((string)$val, ENT_QUOTES) . '" ' . $isSelected . $attrString . '>';
            $html .= htmlspecialchars((string)$label, ENT_QUOTES);
            $html .= '</option>';
        }

        $html .= '</select>';

        return $html;
    }
}
