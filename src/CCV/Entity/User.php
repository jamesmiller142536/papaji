<?php
namespace CCV\Entity;

/**
 * @Entity
 * @Table(name="users")
 */
class User {
	/**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	private $id;

	/**
	 * @Column(type="string")
	 */
	private $username;

	/**
	 * @Column(type="integer",length=4)
	 */
	private $pin;

	/**
	 * @Column(type="integer")
	 */
	private $balance;

	/**
	 * @Column(type="datetime")
	 */
	private $last_daily;

	/**
	 * @Column(type="datetime")
	 */
	private $last_iron;

	/**
	 * @Column(type="integer")
	 */
	private $iron_today;

	public function __construct() {
		$this->pin = 0;
		$this->balance = 0;
		$this->last_daily = new \DateTime('5 years ago');
		$this->last_iron = new \DateTime('5 years ago');
		$this->iron_today = 0;
	}


	public function getId() {
	    return $this->id;
	}
	
	public function setId($id) {
	    $this->id = $id;
	
	    return $this;
	}


	
	public function getUsername() {
	    return $this->username;
	}
	
	public function setUsername($username) {
	    $this->username = $username;
	
	    return $this;
	}


	public function getPin() {
	    return $this->pin;
	}
	
	public function setPin($pin) {
	    $this->pin = $pin;
	
	    return $this;
	}


	public function getBalance() {
	    return $this->balance;
	}
	
	public function setBalance($balance) {
	    $this->balance = $balance;
	
	    return $this;
	}

	public function substractBalance($amount) {
		$this->balance = $this->balance - $amount;
	}

	public function addBalance($amount) {
		$this->balance = $this->balance + $amount;
	}


	public function getLastDaily() {
	    return $this->last_daily;
	}
	
	public function setLastDaily($last_daily) {
	    $this->last_daily = $last_daily;
	
	    return $this;
	}


	public function getLastIron() {
	    return $this->last_iron;
	}
	
	public function setLastIron($last_iron) {
	    $this->last_iron = $last_iron;
	
	    return $this;
	}


	public function getIronToday() {
	    return $this->iron_today;
	}
	
	public function setIronToday($iron_today) {
	    $this->iron_today = $iron_today;
	
	    return $this;
	}

	public function addIronToday($amount) {
		$this->iron_today = $this->iron_today + $amount;
	}
}