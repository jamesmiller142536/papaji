<?php
namespace CCV\Controller;

use DateTime;
use Exception;
use CCV\Entity\Script;
use CCV\FormType\ScriptType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ScriptController extends BaseController {
	public function addAction(Request $request) {
		$twig_options = array();
	    $script = new Script();
	    $form = $this->createForm($script);
	    $form->handleRequest($request);
	    if ($form->isValid()) {
	        $this->app['db.orm.em']->persist($script);
	        $this->app['db.orm.em']->flush();
	        $this->app['session']->getFlashBag()->add('success', 'Adding script succeeded!');
	        return $this->app->redirect($this->app['url_generator']->generate('index'));
	    }
	    $twig_options['form'] = $form->createView();
	    return new Response($this->app['twig']->render('add.html.twig', $twig_options));
	}

	public function changeAction(Request $request, $id) {
		$twig_options = array();
	    $script = $this->getScriptRepository()->findOneById($id);
	    if ($script == null) {
	    	$this->app['session']->getFlashBag()->add('error', $this->trans('unknown_script'));
	    	return $this->app->redirect($this->app['url_generator']->generate('index'));
	    }
	    $form = $this->app['form.factory']
	        ->createBuilder(new ScriptType(), $script, array(
	            'action' => $this->app['url_generator']->generate('scripts.change', array('id' => $id)),
	            'method' => 'POST',
	        ))
	        ->getForm();
	    $form->handleRequest($request);
	    if ($form->isValid()) {
	    	try {
	    		$this->app['db.orm.em']->persist($script);
	        	$this->app['db.orm.em']->flush();
	        	$this->app['session']->getFlashBag()->add('success', $this->trans('script_update_success'));
	        	return $this->app->redirect($this->app['url_generator']->generate('index'));
	    	} catch (Exception $e) {
	    		$this->app['session']->getFlashBag()->add('error', $this->trans('script_update_failure'));
	    	}
	    }
	    $twig_options['form'] = $form->createView();
	    return new Response($this->app['twig']->render('change.html.twig', $twig_options));
	}

	public function putAction(Request $request) {
		$script = new Script();
		$name = $request->request->get('name');
		if ($this->getScriptRepository()->findOneByName($name) != null) {
			return $this->plainResponse('NOTOK');
		}
	    $script->setContents(urldecode($request->request->get('code')));
		$script->setName($name);

	    $metadata = $this->app['validator.mapping.class_metadata_factory']->getMetadataFor('CCV\Entity\Script');
		$metadata->addPropertyConstraint('name', new Assert\NotBlank());
		$metadata->addPropertyConstraint('contents', new Assert\NotBlank());
		$metadata->addPropertyConstraint('contents', new Assert\Length(array('min' => 10)));

		$errors = $this->app['validator']->validate($script);

		if (count($errors) > 0) {
		    return $this->plainResponse('NOTOK');
		} else {
			try {
				$this->app['db.orm.em']->persist($script);
				$this->app['db.orm.em']->flush();
				return $this->plainResponse($script->getId());
			} catch (Exception $e) {
				return $this->plainResponse('NOTOK');
			}
		}
	}

	public function listAction() {
		$scripts = $this->getScriptRepository()->findAll();
	    $data = array();
	    foreach ($scripts as $script) {
	        $data[] = $script->getId() . '|' . $script->getName() . '|' . $script->getStrippedName();
	    }
	    return $this->plainResponse(implode(PHP_EOL, $data));
	}

	public function rawAction(Request $request) {
		$id = $request->request->get('id');
		$script = $this->getScriptRepository()->findOneById($id);
	    if ($script == null) {
	    	$subUrl = '';
	    	switch ($id) {
	    		case 'main':
	    			$subUrl = 'scripts.main';
	    			break;
	    		case 'bankapi':
	    			$subUrl = 'scripts.bank';
	    			break;
	    		case 'ticketapi':
	    			$subUrl = 'scripts.ticket';
	    			break;
	    		case 'install':
	    		case 'upgrade':
	    		case 'update':
	    			$subUrl = 'scripts.install';
	    			break;
	    		default:
	    			$script = $this->getScriptRepository()->findOneBySlug($id);
	    			if ($script !== null) {
	    				return $this->returnScript($script);
	    			}
	    			return $this->plainResponse('');
	    			break;
	    	}
	    	$subRequest = Request::create($this->app['url_generator']->generate($subUrl, array(), UrlGeneratorInterface::ABSOLUTE_URL));
	    	return $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);
	    }
	    return $this->returnScript($script);
	}

	private function returnScript(Script $script) {
		$contents = $this->app['script_twig']->render($script->getContents());
		return $this->plainResponse($contents);
	}

	public function showAction(Request $request, $id, $slug) {
		$script = $this->getScriptRepository()->findOneBy(array('id' => $id, 'slug' => $slug));
		if ($script != null) {
			$contents = $this->app['script_twig']->render($script->getContents());
			return new Response($this->app['twig']->render('single.html.twig', array('script' => $script, 'contents' => $contents)));
		} else {
			return $this->app->abort(404, 'Script was not found');
		}
	}

	public function createForm(Script $script) {
		return $this->app['form.factory']
	        ->createBuilder(new ScriptType(), $script, array(
	            'action' => $this->app['url_generator']->generate('scripts.add'),
	            'method' => 'POST',
	        ))->getForm();
	}
}