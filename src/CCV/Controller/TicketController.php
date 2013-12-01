<?php
namespace CCV\Controller;

use DateTime;
use Exception;
use CCV\Entity\Ticket;
use CCV\Entity\TicketType;
use CCV\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class TicketController extends BaseController {
	public function createAction(Request $request) {
		$type = $request->request->get('type');
		if (!$type || $type == '') {
	        return $this->plainResponse($this->trans('ticket.invalid_type'));
	    }
		$ticketType = $this->getTicketTypeRepository()->findOneBySlug($type);
		if (!$ticketType || !$ticketType->isActive()) {
            return $this->plainResponse($this->trans('ticket.invalid_type'));
        }
        $username = $request->request->get('username');
        $pin = $request->request->get('pin');
        $to = $ticketType->getTo();

        if (!$this->validateUsername($username)) {
            $this->log('TRANSACTION', $username, $to, $amount, false, 'INVALID_USERNAME');
            return $this->plainResponse($this->trans('invalid_username'));
        }

        $subRequest = Request::create($this->app['url_generator']->generate('bank.pay'), 
                                            'POST', 
                                            array(
                                                    'username' => $username, 
                                                    'pin' => $pin, 
                                                    'to' => $to, 
                                                    'amount' => $ticketType->getPrice()
                                                    ), 
                                            $request->cookies->all(), 
                                            array(), 
                                            $request->server->all());

        $response = $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);

        $repository = $this->getUserRepository();
		$user = $repository->findOneByUsername($username);
		$balance = 0;
		if ($user !== null) {
			$balance = $user->getBalance();
		}

        if (strpos($response->getContent(), $this->trans('bank.new_balance', array('%balance%' => $balance))) !== false) {
            $ticket = new Ticket();
            $ticket->setId($this->generateId());
            $ticket->setType($ticketType);
            $this->app['db.orm.em']->persist($ticket);
            $this->app['db.orm.em']->flush();
            return $this->plainResponse($ticket->getId());
        } else {
            return $response;
        }
	}

	public function useAction(Request $request) {
		$type = $request->request->get('type');
		if (!$type || $type == '') {
	        return $this->plainResponse('NOTOK');
	    }
		$id = $request->request->get('id');
        $ticket = $this->getTicketRepository()->find($id);
        if ($ticket == null) {
            return $this->plainResponse('NOTOK');
        } else {
            if ($ticket->isUsed() || $ticket->getType()->getSlug() != $type) {
                return $this->plainResponse('NOTOK');
            }
            $ticket->setUsed(true);
            $ticket->setUseTime(new DateTime());
            $this->app['db.orm.em']->flush();
            return $this->plainResponse('OK');
        }
	}

	public function checkAction(Request $request) {
		$type = $request->request->get('type');
		if (!$type || $type == '') {
	        return $this->plainResponse('NOTOK');
	    }
		$id = $request->request->get('id');
        $ticket = $this->getTicketRepository()->find($id);
        if ($ticket == null) {
            return $this->plainResponse('NOTOK');
        } else {
            if ($ticket->isUsed() || $ticket->getType()->getSlug() != $type) {
                return $this->plainResponse('NOTOK');
            }
            return $this->plainResponse('OK');
        }
	}

	public function priceAction(Request $request) {
		$type = $request->request->get('type');
		if (!$type || $type == '') {
	        return $this->plainResponse('NOTOK');
	    }
		$ticketType = $this->getTicketTypeRepository()->findOneBySlug($type);
        if (!$ticketType || !$ticketType->isActive()) {
            return $this->plainResponse('NOTOK');
        }
        return $this->plainResponse($ticketType->getPrice());
	}

	public function addTypeAction(Request $request) {
		$slug = $request->request->get('slug');
        $amount = intval($request->request->get('amount'));
        if (empty($slug) || strlen($slug) < 1) {
            return $this->plainResponse($this->trans('ticket.invalid_slug'));
        }
        if (empty($amount) || $amount < 1) {
        	return $this->plainResponse($this->trans('ticket.invalid_amount'));
        }
        if ($this->getTicketTypeRepository()->findOneBySlug($slug) !== null) {
        	return $this->plainResponse($this->trans('ticket.already_exists'));
        }
        $ticketType = new TicketType();
        $ticketType->setSlug($slug);
        $ticketType->setPrice($amount);
        $ticketType->setActive(true);
        $ticketType->setTo('');
        $this->app['db.orm.em']->persist($ticketType);
        $this->app['db.orm.em']->flush();
        return $this->plainResponse('OK');
	}

	public function generateId() {
		$id = mt_rand();
        if ($this->getTicketRepository()->find($id)) {
            return $this->generateId();
        }
        return $id;
	}
}