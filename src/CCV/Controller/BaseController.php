<?php
namespace CCV\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

class BaseController {

    /**
     * @var Application
     */
    protected $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    protected function plainResponse($string, $statusCode = 200) {
        return new Response($string, $statusCode, array('Content-Type' => 'text/plain'));
    }

    protected function getUserRepository() {
        return $this->app['db.orm.em']->getRepository('CCV\Entity\User');
    }

    protected function getScriptRepository() {
        return $this->app['db.orm.em']->getRepository('CCV\Entity\Script');
    }

    protected function getTicketRepository() {
        return $this->app['db.orm.em']->getRepository('CCV\Entity\Ticket');
    }

    protected function getTicketTypeRepository() {
        return $this->app['db.orm.em']->getRepository('CCV\Entity\TicketType');
    }

    protected function validateUsername($username) {
        return preg_match('/^[a-zA-Z0-9_]+$/', $username) && strlen($username) <= 255;
    }

    /**
     * Translates the given message.
     *
     * @param string $id         The message id
     * @param array  $parameters An array of parameters for the message
     * @param string $domain     The domain for the message
     * @param string $locale     The locale
     *
     * @return string The translated string
     */
    protected function trans($id, array $parameters = array(), $domain = 'messages', $locale = null) {
        return $this->app['translator']->trans($id, $parameters, $domain, $locale);
    }

    /**
     * Translates the given choice message by choosing a translation according to a number.
     *
     * @param string  $id         The message id
     * @param integer $number     The number to use to find the indice of the message
     * @param array   $parameters An array of parameters for the message
     * @param string  $domain     The domain for the message
     * @param string  $locale     The locale
     *
     * @return string The translated string
     */
    protected function transChoice($id, $number, array $parameters = array(), $domain = 'messages', $locale = null) {
        return $this->app['translator']->transChoice($id, $number, $parameters, $domain, $locale);
    }
}