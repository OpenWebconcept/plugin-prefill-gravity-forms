<?php

namespace OWC\PrefillGravityForms\Foundation;

class View
{
    /** @var string */
    protected $templateDirectory = PG_ROOT_PATH . '/views/';

    /** @var array */
    protected $vars = [];

    /**
     * @var array Associative array of variables that will be accessible from
     * the template.
     */
    protected $bindings = [];

    public function __construct($templateDirectory = null)
    {
        if (null !== $templateDirectory) {
            // Check here whether this directory really exists
            $this->templateDirectory = $templateDirectory;
        }
    }

    /**
     * Render the view
     *
     * @param string $templateFile
     *
     * @return string
     */
    public function render(string $templateFile = '', array $vars = []): string
    {
        $this->bindAll($vars);
        ob_start();
        include($this->templateDirectory . $templateFile);
        $data = trim(ob_get_clean());
        return $this->parseTemplate($data, $this->bindings);
    }

    /**
     * Search and replace of variables.
     * Searching for {{VARIABLE}}.
     *
     * @param string $templateFile
     * @param array $bindings
     *
     * @return string
     */
    protected function parseTemplate(string $template, array $bindings = []): string
    {
        return preg_replace_callback(
            '#{{\s?(.*?)\s?}}#',
            function ($match) use ($bindings) {
                $match[1] = trim($match[1], '');
                return $bindings[$match[1]] ?? '';
            },
            $template
        );
    }

    /**
     * Bind a single variable that will be accessible when the view is rendered.
     *
     * @param string $parameter
     * @param mixed $value
     */
    public function bind($parameter, $value)
    {
        $this->bindings[$parameter] = $value;
    }

    /**
     * Bind multiple parameters at once.
     *
     * @see View:bind()
     * @param array $bindings
     */
    public function bindAll(array $bindings)
    {
        foreach ($bindings as $parameter => $value) {
            $this->bind($parameter, $value);
        }
    }

    public function __set($name, $value)
    {
        $this->vars[$name] = $value;
    }

    public function __get($name)
    {
        return $this->vars[$name];
    }
}
