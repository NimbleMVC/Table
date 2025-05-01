<?php

namespace NimblePHP\Table\Template;

use NimblePHP\Framework\Exception\NotFoundException;

class View extends \NimblePHP\Framework\View
{

    public function __construct()
    {
        parent::__construct();

        $this->viewPath = __DIR__ . '/resources/';
    }
    /**
     * Render view
     * @param string $viewName
     * @param array $data
     * @return string
     * @throws NotFoundException
     */
    public function renderViewString(string $viewName, array $data = []): string
    {
        ob_start();
        $this->render($viewName, $data);
        return ob_get_clean();
    }

}