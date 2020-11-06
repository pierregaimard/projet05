<?php

namespace App\Service\Templating;

use Climb\Exception\AppException;
use Climb\Templating\Twig\TemplatingManager as ClimbTemplatingManager;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class TemplatingManager
{
    /**
     * @var ClimbTemplatingManager
     */
    private ClimbTemplatingManager $templating;

    /**
     * @var Environment
     */
    private Environment $environment;

    /**
     * @param ClimbTemplatingManager $templating
     */
    public function __construct(ClimbTemplatingManager $templating)
    {
        $this->templating = $templating;
        $this->setEnvironment();
    }

    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @return bool|int
     */
    public function setEnvironment()
    {
        try {
            $this->environment = $this->templating->getEnvironment([]);
        } catch (AppException $exception) {
            return (int)$exception->getCode();
        }

        return true;
    }

    /**
     * @param string     $path
     * @param array|null $data
     *
     * @return int|string
     */
    public function render(string $path, array $data = null)
    {
        if ($data !== null) {
            try {
                return $this->environment->render($path, $data);
            } catch (LoaderError $exception) {
                return (int)$exception->getCode();
            } catch (RuntimeError $exception) {
                return (int)$exception->getCode();
            } catch (SyntaxError $exception) {
                return (int)$exception->getCode();
            }
        }

        return $this->render($path);
    }
}
