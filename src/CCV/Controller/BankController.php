<?php
namespace CCV\Controller;

use DateTime;
use Exception;
use CCV\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BankController extends BaseController {
	private static $allowedTypes = array('TRANSACTION','DAILY','IRON');	
	private static $allowedErrors = array('INVALID_AMOUNT','NO_ACCOUNT','INVALID_PIN','INVALID_TO','INVALID_USERNAME','TOO_LOW_BALANCE','ALREADY_DAILY');

	private function log($type, $from, $to, $amount, $success = true, $error = null) {
		if (!$this->app['user_config']['log_transactions']) return;
        if (!in_array($type, self::$allowedTypes)) {
            throw new Exception('Invalid type');
        }
        if (!($from == null || is_string($from))) {
            throw new Exception('Invalid from');
        }
        if (!($to == null || is_string($to))) {
            throw new Exception('Invalid from');
        }
        if (!($error == null || in_array($error, self::$allowedErrors))) {
            throw new Exception('Invalid error');
        }
        $this->app['db']->insert('transaction_logs', array(
                'transaction_type' => $type,
                'transaction_from' => $from ?: null,
                'transaction_to'   => $to?: null,
                'transaction_amount' => (int) $amount,
                'transaction_success' => $success ? 1 : 0,
                'transaction_error' => $error ?: null,
            )
        );
	}

	public function payAction(Request $request) {
		$username = $request->request->get('username');
        $pin = $request->request->get('pin');
		$amount = intval($request->request->get('amount'));
        $to = $request->request->get('to');
        if (!$this->validateUsername($username)) {
            $this->log('TRANSACTION', $username, $to, $amount, false, 'INVALID_USERNAME');
            return $this->plainResponse($this->trans('invalid_username'));
        }
		if ($amount < 1) {
            $this->log('TRANSACTION', $username, $to, $amount, false, 'INVALID_AMOUNT');
			return $this->plainResponse($this->trans('bank.invalid_amount'));
		}
		$repository = $this->getUserRepository();
		$user = $repository->findOneByUsername($username);
		if ($user == null) {
			$user = new User();
			$user->setUsername($username);
			$user->setPin(rand(1001, 9999));
			$user->setBalance($this->app['user_config']['bank']['initial_balance']);
			$this->app['db.orm.em']->persist($user);
			$this->app['db.orm.em']->flush();
            $this->log('TRANSACTION', $username, $to, $amount, false, 'NO_ACCOUNT');
			return $this->plainResponse($this->trans('bank.new_pin', array('%pin%' => $user->getPin())));
		}
		if ($user->getPin() != $pin) {
            $this->log('TRANSACTION', $username, $to, $amount, false, 'INVALID_PIN');
			return $this->plainResponse($this->trans('bank.invalid_pin'));
		}
		if (($user->getBalance() - $amount) >= 0) {
			$user->substractBalance($amount);
			if ($to !== null && strlen($to) > 0) {
				$toUser = $repository->findOneByUsername($to);
				if ($toUser) {
					$toUser->addBalance($amount);
				} else {
                    $this->log('TRANSACTION', $username, $to, $amount, false, 'INVALID_TO');
                    return $this->plainResponse($this->trans('bank.invalid_receiver'));
                }
			}
			$this->app['db.orm.em']->flush();
            $this->log('TRANSACTION', $username, $to, $amount, true, null);
			return $this->plainResponse($this->trans('bank.new_balance', array('%balance%' => $user->getBalance())));
		} else {
            $this->log('TRANSACTION', $username, $to, $amount, false, 'TOO_LOW_BALANCE');
			return $this->plainResponse($this->trans('bank.balance_too_low'));
		}
	}

	public function balanceAction(Request $request) {
		$username = $request->request->get('username');
		$user = $this->getUserRepository()->findOneByUsername($username);
		if ($user == null) {
			return 0;
		}
		return $this->plainResponse($user->getBalance());
	}

	public function allBalanceAction(Request $request) {
		$users = $this->getUserRepository()->findBy(array(), array('balance' => 'DESC'));
        $output = '';
        $balances = array();
        foreach ($users as $user) {
            $balances[] = array($user->getUsername(), $user->getBalance());
        }
        $balances = array_map(function($item) {
        	return implode(' ', $item);
        }, $balances);
        return $this->plainResponse(implode(PHP_EOL, $balances));
	}

	public function dailyAction(Request $request) {
		$amount = $this->app['user_config']['bank']['daily_amount'];
        $now = new DateTime();
        $username = $request->request->get('username');
        if (!$this->validateUsername($username)) {
            $this->log('TRANSACTION', null, $username, $amount, false, 'INVALID_USERNAME');
            return $this->plainResponse($this->trans('invalid_username'));
        }
        $repository = $this->getUserRepository();
        $user = $repository->findOneByUsername($username);
        if ($user == null) {
            $user = new User();
            $user->setUsername($username);
            $user->setPin(rand(1001, 9999));
            $user->setBalance(100 + $amount);
            $user->setLastDaily(new \DateTime());
            $this->app['db.orm.em']->persist($user);
            $this->app['db.orm.em']->flush();
            $this->log('DAILY', null, $username, $amount, true, 'NO_ACCOUNT');
            return $this->plainResponse($this->trans('bank.new_pin', array('%pin%' => $user->getPin())));
        }
        if ($user->getPin() != $request->request->get('pin')) {
            $this->log('DAILY', null, $username, $amount, false, 'INVALID_PIN');
            return $this->plainResponse($this->trans('bank.invalid_pin'));
        }
        if ($user->getLastDaily()->format('Y-m-d') === $now->format('Y-m-d')) {
            $this->log('DAILY', null, $username, $amount, false, 'ALREADY_DAILY');
            return $this->plainResponse($this->trans('bank.double_daily'));
        } else {
            $user->addBalance($amount);
            $user->setLastDaily($now);
            $this->app['db.orm.em']->flush();
            $this->log('DAILY', null, $username, $amount, true, null);
            return $this->plainResponse($this->trans('bank.new_balance', array('%balance%' => $user->getBalance())));
        }
	}

	public function checkAction(Request $request) {
		$username = $request->request->get('username');
        $pin = $request->request->get('pin');
        $user = $this->getUserRepository()->findOneByUsername($username);
        if ($user == null) {
            return $this->plainResponse('NOTOK');
        }
        if ($user->getPin() != $pin) {
            return $this->plainResponse('NOTOK');
        }
        return $this->plainResponse('OK');
	}

    public function contentAction(Request $request) {
        return new Response($this->app['twig']->render('bank_content.html.twig', array('users' => $this->getUserRepository()->findAll())));
    }

	public function indexAction(Request $request) {
	    return new Response($this->app['twig']->render('bank.html.twig'));
	}
}