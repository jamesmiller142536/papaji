<?php
namespace CCV\Controller;

use CCV\Entity\Script;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MainController extends BaseController {
	public function indexAction(Request $request) {
		$data = array('scripts' => $this->getScriptRepository()->findBy(array(), array('id' => 'DESC')));
	    if ($this->app['user_config']['pastebin_id'] === null || strlen($this->app['user_config']['pastebin_id']) === 0) {
	        $data['download_script'] = $this->app['twig']->render('script.lua.twig');
	    }

	    $form = $this->app['script.controller']->createForm(new Script());
	    $data['add_form'] = $form->createView();

	    return new Response($this->app['twig']->render('index.html.twig', $data));
	}

	public function mainScriptAction() {
		return $this->plainResponse($this->app['twig']->render('script.lua.twig'));
	}

	public function installScriptAction() {
		return $this->plainResponse($this->app['twig']->render('install.lua.twig'));
	}

	public function bankScriptAction() {
		return $this->plainResponse($this->app['twig']->render('bank.lua.twig'));
	}

	public function ticketScriptAction() {
		return $this->plainResponse($this->app['twig']->render('ticket.lua.twig'));
	}
}