<?php

declare(strict_types=1);

namespace App\Support\LegacyForm;

use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\HtmlString;

class LegacyFormBuilder
{
    private UrlGenerator $url;

    /** @var mixed */
    private $model = null;

    public function __construct(UrlGenerator $url)
    {
        $this->url = $url;
    }

    /**
     * @param array<string,mixed> $options
     */
    public function open(array $options = []): HtmlString
    {
        $method = strtoupper((string)($options['method'] ?? 'POST'));
        unset($options['method']);

        $files = (bool)($options['files'] ?? false);
        unset($options['files']);

        $action = $this->resolveAction($options);

        $attributes = $options;
        $attributes['method'] = ($method === 'GET') ? 'GET' : 'POST';
        $attributes['action'] = $action;

        if ($files) {
            $attributes['enctype'] = 'multipart/form-data';
        }

        $html = '<form'.$this->attributes($attributes).'>';

        if ($method !== 'GET') {
            $html .= (string) csrf_field();
        }

        if (!in_array($method, ['GET', 'POST'], true)) {
            $html .= (string) method_field($method);
        }

        return new HtmlString($html);
    }

    /**
     * @param mixed $model
     * @param array<string,mixed> $options
     */
    public function model($model, array $options = []): HtmlString
    {
        $this->model = $model;
        return $this->open($options);
    }

    public function close(): HtmlString
    {
        $this->model = null;
        return new HtmlString('</form>');
    }

    /**
     * @param array<string,mixed> $options
     */
    public function label(string $name, ?string $value = null, array $options = []): HtmlString
    {
        $for = $options['for'] ?? $this->defaultId($name);
        unset($options['for']);

        $options['for'] = $for;

        $text = $value ?? $name;

        return new HtmlString('<label'.$this->attributes($options).'>'.e($text).'</label>');
    }

    /**
     * @param array<string,mixed> $options
     */
    public function text(string $name, $value = null, array $options = []): HtmlString
    {
        return $this->input('text', $name, $value, $options);
    }

    /**
     * @param array<string,mixed> $options
     */
    public function email(string $name, $value = null, array $options = []): HtmlString
    {
        return $this->input('email', $name, $value, $options);
    }

    /**
     * @param array<string,mixed> $options
     */
    public function number(string $name, $value = null, array $options = []): HtmlString
    {
        return $this->input('number', $name, $value, $options);
    }

    /**
     * @param array<string,mixed> $options
     */
    public function datetimeLocal(string $name, $value = null, array $options = []): HtmlString
    {
        return $this->input('datetime-local', $name, $value, $options);
    }

    /**
     * @param array<string,mixed> $options
     */
    public function hidden(string $name, $value = null, array $options = []): HtmlString
    {
        return $this->input('hidden', $name, $value, $options);
    }

    /**
     * @param array<string,mixed> $options
     */
    public function password(string $name, array $options = []): HtmlString
    {
        return $this->input('password', $name, null, $options);
    }

    /**
     * @param array<string,mixed> $options
     */
    public function file(string $name, array $options = []): HtmlString
    {
        return $this->input('file', $name, null, $options);
    }

    /**
     * @param array<string,mixed> $options
     */
    public function textarea(string $name, $value = null, array $options = []): HtmlString
    {
        $options['name'] = $name;
        $options['id'] = $options['id'] ?? $this->defaultId($name);

        if ($value === null) {
            $value = $this->valueFromModel($name);
        }

        return new HtmlString('<textarea'.$this->attributes($options).'>'.e((string)($value ?? '')).'</textarea>');
    }

    /**
     * @param array<string,mixed> $list
     * @param array<string,mixed> $options
     */
    public function select(string $name, array $list = [], $selected = null, array $options = []): HtmlString
    {
        $options['name'] = $name;
        $options['id'] = $options['id'] ?? $this->defaultId($name);

        $isMultiple = array_key_exists('multiple', $options) && $options['multiple'] !== false;

        if ($selected === null) {
            $selected = $this->valueFromModel($name);
        }

        $selectedValues = $isMultiple
            ? array_map('strval', is_array($selected) ? $selected : (array) $selected)
            : [(string) $selected];

        $html = '<select'.$this->attributes($options).'>';

        foreach ($list as $key => $display) {
            $keyString = (string) $key;
            $isSelected = in_array($keyString, $selectedValues, true);

            $html .= '<option value="'.e($keyString).'"'.($isSelected ? ' selected' : '').'>'
                .e((string) $display)
                .'</option>';
        }

        $html .= '</select>';

        return new HtmlString($html);
    }

    /**
     * Signature compatible with common Collective usage:
     * checkbox(name, value, checked, options)
     *
     * @param array<string,mixed> $options
     */
    public function checkbox(string $name, $value = 1, $checked = null, array $options = []): HtmlString
    {
        $options['value'] = $value;

        if ($checked) {
            $options['checked'] = 'checked';
        }

        return $this->input('checkbox', $name, $value, $options);
    }

    /**
     * @param array<string,mixed> $options
     */
    public function radio(string $name, $value = null, $checked = null, array $options = []): HtmlString
    {
        $options['value'] = $value;

        if ($checked) {
            $options['checked'] = 'checked';
        }

        return $this->input('radio', $name, $value, $options);
    }

    /**
     * @param array<string,mixed> $options
     */
    public function submit(?string $value = null, array $options = []): HtmlString
    {
        $options['type'] = 'submit';
        $options['value'] = $value ?? 'Submit';

        return new HtmlString('<input'.$this->attributes($options).' />');
    }

    /**
     * @param array<string,mixed> $options
     */
    private function input(string $type, string $name, $value = null, array $options = []): HtmlString
    {
        $options['type'] = $type;
        $options['name'] = $name;
        $options['id'] = $options['id'] ?? $this->defaultId($name);

        if (!in_array($type, ['password', 'file'], true)) {
            if ($value === null) {
                $value = $this->valueFromModel($name);
            }
            if ($value !== null) {
                $options['value'] = $value;
            }
        }

        return new HtmlString('<input'.$this->attributes($options).' />');
    }

    /**
     * @param array<string,mixed> $options
     */
    private function resolveAction(array &$options): string
    {
        if (array_key_exists('route', $options)) {
            $route = $options['route'];
            unset($options['route']);

            if (is_array($route)) {
                $name = array_shift($route);
                return route((string) $name, $route);
            }

            return route((string) $route);
        }

        if (array_key_exists('url', $options)) {
            $url = (string) $options['url'];
            unset($options['url']);

            return $this->url->to($url);
        }

        if (array_key_exists('action', $options)) {
            $action = $options['action'];
            unset($options['action']);

            return action($action);
        }

        return $this->url->current();
    }

    private function defaultId(string $name): string
    {
        $name = preg_replace('/\[\]$/', '', $name) ?? $name;
        return str_replace(['[', ']'], ['_', ''], $name);
    }

    /**
     * @return mixed
     */
    private function valueFromModel(string $name)
    {
        if ($this->model === null) {
            return null;
        }

        $key = preg_replace('/\[\]$/', '', $name) ?? $name;

        // Eloquent models & arrays should both work with data_get
        return data_get($this->model, $key);
    }

    /**
     * @param array<string|int,mixed> $attributes
     */
    private function attributes(array $attributes): string
    {
        $chunks = [];

        foreach ($attributes as $key => $value) {
            if (is_int($key)) {
                $attrName = (string) $value;
                if ($attrName !== '') {
                    $chunks[] = ' '.e($attrName);
                }
                continue;
            }

            if ($value === null || $value === false) {
                continue;
            }

            $attrName = (string) $key;

            if ($value === true) {
                $chunks[] = ' '.e($attrName);
                continue;
            }

                $chunks[] = ' '.e($attrName).'="'.e((string) $value).'"';
        }

        return implode('', $chunks);
    }
}
